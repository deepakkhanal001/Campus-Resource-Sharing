<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class ResourceController extends Controller
{
    private function ensureStudentOrAdmin(): void
    {
        $user = Auth::user();
        if (! $user) {
            abort(403);
        }

        $role = strtolower((string) ($user->role ?? ''));
        $roles = method_exists($user, 'getRoleNames') ? $user->getRoleNames()->map(fn ($r) => strtolower((string) $r))->all() : [];

        if ($role === 'admin' || in_array('admin', $roles, true)) {
            return;
        }

        if ($role !== 'student' && ! in_array('student', $roles, true)) {
            abort(403);
        }

        if (strtolower((string) ($user->status ?? 'active')) !== 'active') {
            abort(403, 'Your account is not active.');
        }
    }

    private function authorizeOwnerOrAdmin(array $resource): void
    {
        $user = Auth::user();
        if (! $user) {
            abort(403);
        }

        $role = strtolower((string) ($user->role ?? ''));
        $roles = method_exists($user, 'getRoleNames') ? $user->getRoleNames()->map(fn ($r) => strtolower((string) $r))->all() : [];
        if ($role === 'admin' || in_array('admin', $roles, true)) {
            return;
        }

        if ((int) $resource['owner_id'] !== (int) $user->id) {
            abort(403);
        }
    }

    public function index(Request $request): View
    {
        $this->ensureStudentOrAdmin();

        $search = trim((string) $request->query('q', ''));
        $type = (string) $request->query('type', '');
        $availability = (string) $request->query('availability_status', '');
        $categoryId = $request->integer('category_id');

        $where = ['r.deleted_at IS NULL'];
        $bindings = [];

        $user = Auth::user();
        $role = strtolower((string) ($user->role ?? ''));
        $roles = method_exists($user, 'getRoleNames') ? $user->getRoleNames()->map(fn ($r) => strtolower((string) $r))->all() : [];
        if ($role !== 'admin' && ! in_array('admin', $roles, true)) {
            $where[] = 'r.owner_id = ?';
            $bindings[] = $user->id;
        }

        if ($search !== '') {
            $where[] = '(r.title LIKE ? OR r.description LIKE ?)';
            $bindings[] = '%'.$search.'%';
            $bindings[] = '%'.$search.'%';
        }

        if (in_array($type, ['physical', 'digital'], true)) {
            $where[] = 'r.type = ?';
            $bindings[] = $type;
        }

        if (in_array($availability, ['available', 'unavailable', 'borrowed'], true)) {
            $where[] = 'r.availability_status = ?';
            $bindings[] = $availability;
        }

        if ($categoryId > 0) {
            $where[] = 'r.category_id = ?';
            $bindings[] = $categoryId;
        }

        $whereSql = implode(' AND ', $where);

        $total = (int) (DB::selectOne('SELECT COUNT(*) AS total FROM resources r WHERE '.$whereSql, $bindings)->total ?? 0);
        $perPage = 15;
        $lastPage = max(1, (int) ceil($total / $perPage));
        $page = min(max(1, (int) $request->query('page', 1)), $lastPage);
        $offset = ($page - 1) * $perPage;

        $resources = DB::select(
            'SELECT r.*, c.name AS category_name, u.name AS owner_name
             FROM resources r
             LEFT JOIN categories c ON c.id = r.category_id
             LEFT JOIN users u ON u.id = r.owner_id
             WHERE '.$whereSql.'
             ORDER BY r.id DESC
             LIMIT '.$perPage.' OFFSET '.$offset,
            $bindings
        );

        $categories = DB::select('SELECT id, name FROM categories WHERE deleted_at IS NULL ORDER BY name ASC');

        return view('resources.index', [
            'resources' => new LengthAwarePaginator($resources, $total, $perPage, $page, ['path' => $request->url(), 'query' => $request->query()]),
            'categories' => $categories,
        ]);
    }

    public function create(): View
    {
        $this->ensureStudentOrAdmin();

        $categories = DB::select('SELECT id, name FROM categories WHERE deleted_at IS NULL ORDER BY name ASC');

        return view('resources.create', [
            'categories' => $categories,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->ensureStudentOrAdmin();

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'type' => ['required', 'in:physical,digital'],
            'category_id' => ['required', 'exists:categories,id'],
            'availability_status' => ['required', 'in:available,unavailable,borrowed'],
        ]);

        $now = now()->toDateTimeString();
        $resourceId = DB::table('resources')->insertGetId([
            'title' => $validated['title'],
            'description' => $validated['description'] ?? null,
            'type' => $validated['type'],
            'owner_id' => Auth::id(),
            'category_id' => $validated['category_id'],
            'availability_status' => $validated['availability_status'],
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        return redirect()
            ->route('resources.show', $resourceId)
            ->with('success', 'Resource created successfully.');
    }

    public function show($resource): View
    {
        $this->ensureStudentOrAdmin();

        $resourceRow = DB::selectOne(
            'SELECT r.*, c.name AS category_name, u.name AS owner_name, u.email AS owner_email
             FROM resources r
             LEFT JOIN categories c ON c.id = r.category_id
             LEFT JOIN users u ON u.id = r.owner_id
             WHERE r.id = ? AND r.deleted_at IS NULL',
            [$resource]
        );
        abort_unless($resourceRow, 404);

        $this->authorizeOwnerOrAdmin((array) $resourceRow);

        $digitalFiles = DB::select('SELECT * FROM digital_files WHERE resource_id = ? AND deleted_at IS NULL ORDER BY id ASC', [$resource]);
        $borrowingRecords = DB::select('SELECT br.*, requester.name AS requester_name FROM borrowing_records br LEFT JOIN users requester ON requester.id = br.requester_id WHERE br.resource_id = ? AND br.deleted_at IS NULL ORDER BY br.id DESC', [$resource]);

        return view('resources.show', [
            'resource' => $resourceRow,
            'digitalFiles' => $digitalFiles,
            'borrowingRecords' => $borrowingRecords,
        ]);
    }

    public function edit($resource): View
    {
        $this->ensureStudentOrAdmin();

        $resourceRow = DB::selectOne('SELECT * FROM resources WHERE id = ? AND deleted_at IS NULL', [$resource]);
        abort_unless($resourceRow, 404);
        $this->authorizeOwnerOrAdmin((array) $resourceRow);

        $categories = DB::select('SELECT id, name FROM categories WHERE deleted_at IS NULL ORDER BY name ASC');

        return view('resources.edit', [
            'resource' => $resourceRow,
            'categories' => $categories,
        ]);
    }

    public function update(Request $request, $resource): RedirectResponse
    {
        $this->ensureStudentOrAdmin();

        $resourceRow = DB::selectOne('SELECT * FROM resources WHERE id = ? AND deleted_at IS NULL', [$resource]);
        abort_unless($resourceRow, 404);
        $this->authorizeOwnerOrAdmin((array) $resourceRow);

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'type' => ['required', 'in:physical,digital'],
            'category_id' => ['required', 'exists:categories,id'],
            'availability_status' => ['required', 'in:available,unavailable,borrowed'],
        ]);

        $now = now()->toDateTimeString();
        DB::table('resources')->where('id', $resource)->update([
            'title' => $validated['title'],
            'description' => $validated['description'] ?? null,
            'type' => $validated['type'],
            'category_id' => $validated['category_id'],
            'availability_status' => $validated['availability_status'],
            'updated_at' => $now,
        ]);

        return redirect()
            ->route('resources.show', $resource)
            ->with('success', 'Resource updated successfully.');
    }

    public function destroy($resource): RedirectResponse
    {
        $this->ensureStudentOrAdmin();

        $resourceRow = DB::selectOne('SELECT * FROM resources WHERE id = ? AND deleted_at IS NULL', [$resource]);
        abort_unless($resourceRow, 404);
        $this->authorizeOwnerOrAdmin((array) $resourceRow);

        DB::table('resources')->where('id', $resource)->update([
            'deleted_at' => now()->toDateTimeString(),
            'updated_at' => now()->toDateTimeString(),
        ]);

        return redirect()
            ->route('resources.index')
            ->with('success', 'Resource deleted successfully.');
    }
}
