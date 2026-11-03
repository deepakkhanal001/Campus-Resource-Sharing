<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class RegisteredMemberController extends Controller
{
    private const ROLES = ['student', 'admin'];

    public function index(Request $request): View
    {
        $search = mb_substr(trim((string) $request->query('q', '')), 0, 100);
        $role = strtolower((string) $request->query('role', ''));

        if (! in_array($role, self::ROLES, true)) {
            $role = '';
        }

        $where = ['1 = 1'];
        $bindings = [];

        if ($search !== '') {
            $where[] = '(LOWER(u.name) LIKE ? OR LOWER(u.email) LIKE ? OR u.college_student_id LIKE ?)';
            $term = '%'.mb_strtolower($search, 'UTF-8').'%';
            $bindings[] = $term;
            $bindings[] = $term;
            $bindings[] = '%'.$search.'%';
        }

        if ($role !== '') {
            $where[] = 'LOWER(u.role) = ?';
            $bindings[] = $role;
        }

        $whereSql = implode(' AND ', $where);

        $total = (int) (DB::selectOne(
            'SELECT COUNT(*) AS total FROM users u WHERE '.$whereSql,
            $bindings
        )->total ?? 0);

        $perPage = 15;
        $lastPage = max(1, (int) ceil($total / $perPage));
        $page = min(max(1, (int) $request->query('page', 1)), $lastPage);
        $offset = ($page - 1) * $perPage;

        $members = DB::select(
            'SELECT
                u.id,
                u.name,
                u.email,
                u.role,
                u.status,
                u.college_student_id,
                u.program,
                u.created_at,
                (
                    SELECT COUNT(*)
                    FROM resources r
                    WHERE r.owner_id = u.id
                      AND r.deleted_at IS NULL
                ) AS resources_count
             FROM users u
             WHERE '.$whereSql.'
             ORDER BY u.created_at DESC, u.id DESC
             LIMIT '.$perPage.' OFFSET '.$offset,
            $bindings
        );

        return view('admin.members.index', [
            'members' => new LengthAwarePaginator(
                $members,
                $total,
                $perPage,
                $page,
                [
                    'path' => route('admin.members.index'),
                    'query' => $request->query(),
                ]
            ),
            'filters' => [
                'q' => $search,
                'role' => $role,
            ],
            'hasFilters' => $search !== '' || $role !== '',
        ]);
    }
}
