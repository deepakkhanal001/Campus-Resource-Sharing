<?php

namespace App\Http\Controllers;

use App\Support\PublicCatalog;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class PublicResourceController extends PublicPortalController
{
    public function index(Request $request): View
    {
        $search = mb_substr(trim((string) $request->query('q', '')), 0, 100);
        $type = (string) $request->query('type', '');
        $availability = (string) $request->query('availability', '');
        $categoryId = max(0, (int) $request->query('category_id', 0));
        $sort = (string) $request->query('sort', 'newest');

        $type = in_array($type, ['physical', 'digital'], true) ? $type : '';
        $availability = in_array($availability, ['available', 'borrowed', 'unavailable'], true) ? $availability : '';
        $sort = in_array($sort, ['newest', 'oldest', 'title'], true) ? $sort : 'newest';

        $where = ['r.deleted_at IS NULL'];
        $bindings = [];

        if ($search !== '') {
            $where[] = '(r.title LIKE ? OR r.description LIKE ?)';
            $bindings[] = '%'.$search.'%';
            $bindings[] = '%'.$search.'%';
        }

        if ($type !== '') {
            $where[] = 'r.type = ?';
            $bindings[] = $type;
        }

        if ($availability !== '') {
            $where[] = 'r.availability_status = ?';
            $bindings[] = $availability;
        }

        if ($categoryId > 0) {
            $where[] = 'r.category_id = ?';
            $bindings[] = $categoryId;
        }

        $whereSql = implode(' AND ', $where);
        $total = (int) (DB::selectOne(
            'SELECT COUNT(*) AS total '.PublicCatalog::RESOURCE_FROM.' WHERE '.$whereSql,
            $bindings
        )->total ?? 0);

        $perPage = 12;
        $lastPage = max(1, (int) ceil($total / $perPage));
        $page = min(max(1, (int) $request->query('page', 1)), $lastPage);
        $offset = ($page - 1) * $perPage;

        $orderBy = match ($sort) {
            'oldest' => 'r.created_at ASC, r.id ASC',
            'title' => 'r.title ASC, r.id ASC',
            default => 'r.created_at DESC, r.id DESC',
        };

        $resources = DB::select(
            PublicCatalog::RESOURCE_SELECT.' '.PublicCatalog::RESOURCE_FROM.
            ' WHERE '.$whereSql.' ORDER BY '.$orderBy.' LIMIT '.$perPage.' OFFSET '.$offset,
            $bindings
        );

        $paginator = new LengthAwarePaginator(
            $resources,
            $total,
            $perPage,
            $page,
            [
                'path' => route('public.resources.index'),
                'query' => $request->query(),
            ]
        );

        return view('public.resources.index', [
            'resources' => $paginator,
            'categories' => PublicCatalog::categoriesWithCounts(),
            'filters' => [
                'q' => $search,
                'type' => $type,
                'availability' => $availability,
                'category_id' => $categoryId,
                'sort' => $sort,
            ],
            'hasFilters' => $search !== ''
                || $type !== ''
                || $availability !== ''
                || $categoryId > 0
                || $sort !== 'newest',
        ]);
    }

    public function show(int $resource): View
    {
        $item = PublicCatalog::findVisibleResource($resource);

        abort_unless($item, 404);

        $viewer = auth()->check()
            ? PublicCatalog::findUser((int) auth()->id())
            : null;

        $viewerIsActive = $viewer && strtolower((string) $viewer->status) === 'active';
        $isOwner = $viewer && (int) $viewer->id === (int) $item->owner_id;
        $isAdmin = $viewer && strtolower((string) $viewer->role) === 'admin';
        $isStudent = $viewer && strtolower((string) $viewer->role) === 'student';
        $activeRequest = null;
        $incomingPending = 0;

        if ($viewer && $isStudent) {
            $activeRequest = DB::selectOne(
                "SELECT id, status
                 FROM borrowing_records
                 WHERE resource_id = ?
                   AND requester_id = ?
                   AND status IN ('pending', 'approved', 'picked_up')
                   AND deleted_at IS NULL
                 ORDER BY id DESC
                 LIMIT 1",
                [(int) $item->id, (int) $viewer->id]
            );
        }

        if ($viewer && $isOwner) {
            $incomingPending = (int) (DB::selectOne(
                "SELECT COUNT(*) AS total
                 FROM borrowing_records
                 WHERE resource_id = ?
                   AND owner_id = ?
                   AND status = 'pending'
                   AND deleted_at IS NULL",
                [(int) $item->id, (int) $viewer->id]
            )->total ?? 0);
        }

        $files = $item->type === 'digital'
            ? PublicCatalog::availableFiles((int) $item->id)
            : [];

        $canDownload = $viewerIsActive
            && $item->type === 'digital'
            && ($isOwner || $isAdmin || $item->availability_status === 'available');

        $canRequest = $viewerIsActive
            && $isStudent
            && ! $isOwner
            && ! $activeRequest
            && $item->type === 'physical'
            && $item->availability_status === 'available';

        return view('public.resources.show', [
            'resource' => $item,
            'files' => $files,
            'viewer' => $viewer,
            'viewerIsActive' => $viewerIsActive,
            'isOwner' => $isOwner,
            'isAdmin' => $isAdmin,
            'isStudent' => $isStudent,
            'activeRequest' => $activeRequest,
            'incomingPending' => $incomingPending,
            'canDownload' => $canDownload,
            'canRequest' => $canRequest,
        ]);
    }

    public function download(int $resource, int $digitalFile): StreamedResponse
    {
        $viewer = $this->activeUserOrFail();
        $item = PublicCatalog::findVisibleResource($resource);

        abort_unless($item && $item->type === 'digital', 404);

        $file = DB::selectOne(
            'SELECT id, resource_id, file_path, file_name
             FROM digital_files
             WHERE id = ? AND resource_id = ? AND deleted_at IS NULL
             LIMIT 1',
            [$digitalFile, $resource]
        );

        abort_unless($file, 404);

        $isOwner = (int) $viewer->id === (int) $item->owner_id;
        $isAdmin = strtolower((string) $viewer->role) === 'admin';

        abort_unless(
            $isOwner || $isAdmin || $item->availability_status === 'available',
            403,
            'This digital resource is not currently available.'
        );

        abort_unless(
            Storage::disk('local')->exists($file->file_path),
            404,
            'File not found.'
        );

        DB::update(
            'UPDATE digital_files SET download_count = download_count + 1, updated_at = ? WHERE id = ?',
            [now()->toDateTimeString(), (int) $file->id]
        );

        return Storage::disk('local')->download($file->file_path, $file->file_name);
    }
}
