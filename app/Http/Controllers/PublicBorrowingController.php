<?php

namespace App\Http\Controllers;

use App\Support\PublicCatalog;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class PublicBorrowingController extends PublicPortalController
{
    private const STATUSES = ['pending', 'approved', 'picked_up', 'returned', 'rejected', 'cancelled'];

    public function index(Request $request): View
    {
        $student = $this->activeStudentOrFail();
        $search = mb_substr(trim((string) $request->query('q', '')), 0, 100);
        $status = (string) $request->query('status', '');
        $status = in_array($status, self::STATUSES, true) ? $status : '';

        $where = ['br.requester_id = ?', 'br.deleted_at IS NULL'];
        $bindings = [(int) $student->id];

        if ($status !== '') {
            $where[] = 'br.status = ?';
            $bindings[] = $status;
        }

        if ($search !== '') {
            $where[] = '(r.title LIKE ? OR owner.name LIKE ?)';
            $bindings[] = '%'.$search.'%';
            $bindings[] = '%'.$search.'%';
        }

        $from = ' FROM borrowing_records br
                  INNER JOIN resources r ON r.id = br.resource_id
                  INNER JOIN users owner ON owner.id = br.owner_id';
        $whereSql = implode(' AND ', $where);

        $total = (int) (DB::selectOne(
            'SELECT COUNT(*) AS total'.$from.' WHERE '.$whereSql,
            $bindings
        )->total ?? 0);

        $perPage = 10;
        $lastPage = max(1, (int) ceil($total / $perPage));
        $page = min(max(1, (int) $request->query('page', 1)), $lastPage);
        $offset = ($page - 1) * $perPage;

        $requests = DB::select(
            'SELECT
                br.id,
                br.status,
                br.request_message,
                br.rejection_reason,
                br.pickup_date,
                br.return_date,
                br.created_at,
                br.updated_at,
                r.id AS resource_id,
                r.title AS resource_title,
                r.type AS resource_type,
                r.availability_status,
                r.deleted_at AS resource_deleted_at,
                owner.name AS owner_name
             '.$from.' WHERE '.$whereSql.'
             ORDER BY br.created_at DESC, br.id DESC
             LIMIT '.$perPage.' OFFSET '.$offset,
            $bindings
        );

        $countRows = DB::select(
            'SELECT status, COUNT(*) AS total
             FROM borrowing_records
             WHERE requester_id = ? AND deleted_at IS NULL
             GROUP BY status',
            [(int) $student->id]
        );

        $counts = array_fill_keys(self::STATUSES, 0);

        foreach ($countRows as $row) {
            $counts[$row->status] = (int) $row->total;
        }

        return view('public.borrowings.index', [
            'requests' => new LengthAwarePaginator(
                $requests,
                $total,
                $perPage,
                $page,
                [
                    'path' => route('public.borrowings.index'),
                    'query' => $request->query(),
                ]
            ),
            'counts' => $counts,
            'filters' => [
                'q' => $search,
                'status' => $status,
            ],
            'hasFilters' => $search !== '' || $status !== '',
        ]);
    }

    public function requestForm(int $resource): View|RedirectResponse
    {
        $student = $this->activeStudentOrFail();
        $item = PublicCatalog::findVisibleResource($resource);

        abort_unless($item, 404);

        $existing = $this->findActiveRequest((int) $item->id, (int) $student->id);

        if ($existing) {
            return redirect()
                ->route('public.borrowings.index')
                ->with('error', __('You already have an open request for this resource.'));
        }

        $reason = $this->requestEligibility($item, $student);

        if ($reason) {
            return redirect()
                ->route('public.resources.show', $item->id)
                ->with('error', $reason);
        }

        return view('public.resources.request', [
            'resource' => $item,
            'minimumDate' => now()->toDateString(),
        ]);
    }

    public function store(Request $request, int $resource): RedirectResponse
    {
        $student = $this->activeStudentOrFail();
        $item = PublicCatalog::findVisibleResource($resource);

        abort_unless($item, 404);

        $validated = $request->validate([
            'message' => ['nullable', 'string', 'max:1000'],
            'pickup_date' => ['nullable', 'date', 'after_or_equal:today'],
            'return_date' => ['nullable', 'date', 'after_or_equal:pickup_date'],
        ]);

        $reason = $this->requestEligibility($item, $student);

        if ($reason) {
            throw ValidationException::withMessages(['request' => $reason]);
        }

        DB::transaction(function () use ($resource, $student, $validated): void {
            $lockedResource = DB::selectOne(
                PublicCatalog::visibleResourceSql().' AND r.id = ? LIMIT 1'.$this->lockClause(),
                [$resource]
            );

            if (! $lockedResource) {
                abort(404);
            }

            $reason = $this->requestEligibility($lockedResource, $student);

            if ($reason) {
                throw ValidationException::withMessages(['request' => $reason]);
            }

            $existing = DB::selectOne(
                "SELECT id
                 FROM borrowing_records
                 WHERE resource_id = ?
                   AND requester_id = ?
                   AND status IN ('pending', 'approved', 'picked_up')
                   AND deleted_at IS NULL
                 LIMIT 1",
                [(int) $lockedResource->id, (int) $student->id]
            );

            if ($existing) {
                throw ValidationException::withMessages([
                    'request' => __('You already have an open request for this resource.'),
                ]);
            }

            $now = now()->toDateTimeString();

            DB::insert(
                'INSERT INTO borrowing_records
                    (resource_id, requester_id, owner_id, status, request_message, pickup_date, return_date, created_at, updated_at)
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)',
                [
                    (int) $lockedResource->id,
                    (int) $student->id,
                    (int) $lockedResource->owner_id,
                    'pending',
                    $validated['message'] ?? null,
                    $validated['pickup_date'] ?? null,
                    $validated['return_date'] ?? null,
                    $now,
                    $now,
                ]
            );

            $this->createNotification(
                (int) $lockedResource->owner_id,
                $student->name.' requested to borrow "'.$lockedResource->title.'".',
                'borrow_request'
            );
        });

        return redirect()
            ->route('public.borrowings.index')
            ->with('success', __('Your borrowing request has been sent to the owner.'));
    }

    public function cancel(int $borrowing): RedirectResponse
    {
        $student = $this->activeStudentOrFail();

        DB::transaction(function () use ($borrowing, $student): void {
            $record = DB::selectOne(
                'SELECT id, resource_id, requester_id, owner_id, status
                 FROM borrowing_records
                 WHERE id = ? AND requester_id = ? AND deleted_at IS NULL
                 LIMIT 1'.$this->lockClause(),
                [$borrowing, (int) $student->id]
            );

            abort_unless($record, 404);

            if ($record->status !== 'pending') {
                throw ValidationException::withMessages([
                    'cancel' => __('Only requests that are still awaiting approval can be cancelled.'),
                ]);
            }

            DB::update(
                'UPDATE borrowing_records SET status = ?, updated_at = ? WHERE id = ?',
                ['cancelled', now()->toDateTimeString(), (int) $record->id]
            );

            $item = PublicCatalog::findVisibleResource((int) $record->resource_id);
            $title = $item?->title ?? __('a resource');

            $this->createNotification(
                (int) $record->owner_id,
                $student->name.' cancelled their borrowing request for "'.$title.'".',
                'borrow_cancelled'
            );
        });

        return redirect()
            ->route('public.borrowings.index')
            ->with('success', __('Your borrowing request was cancelled.'));
    }

    private function findActiveRequest(int $resourceId, int $requesterId): ?object
    {
        return DB::selectOne(
            "SELECT id, status
             FROM borrowing_records
             WHERE resource_id = ?
               AND requester_id = ?
               AND status IN ('pending', 'approved', 'picked_up')
               AND deleted_at IS NULL
             ORDER BY id DESC
             LIMIT 1",
            [$resourceId, $requesterId]
        );
    }

    private function requestEligibility(object $resource, object $student): ?string
    {
        if ((int) $resource->owner_id === (int) $student->id) {
            return __('You cannot request your own resource.');
        }

        if ($resource->type !== 'physical') {
            return __('Only physical resources can receive borrowing requests.');
        }

        if ($resource->availability_status !== 'available') {
            return __('This resource is not currently available for borrowing.');
        }

        return null;
    }
}
