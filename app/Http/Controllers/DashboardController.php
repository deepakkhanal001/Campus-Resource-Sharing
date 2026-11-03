<?php

namespace App\Http\Controllers;

use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $user = auth()->user();

        if ($user->hasRole('admin')) {
            return $this->adminDashboard();
        }

        abort_unless(
            strtolower((string) ($user->role ?? '')) === 'student'
                && strtolower((string) ($user->status ?? '')) === 'active',
            403,
            'Your student account must be active before using this feature.'
        );

        return $this->studentDashboard($user->id);
    }

    private function adminDashboard(): View
    {
        $today = now()->toDateString();

        $totalUsers = DB::table('users')->count();
        $activeStudents = DB::table('users')
            ->where('role', 'student')
            ->where('status', 'active')
            ->count();
        $totalResources = DB::table('resources')->whereNull('deleted_at')->count();
        $pendingRequests = DB::table('borrowing_records')
            ->whereNull('deleted_at')
            ->where('status', 'pending')
            ->count();
        $onLoan = DB::table('borrowing_records')
            ->whereNull('deleted_at')
            ->whereIn('status', ['approved', 'picked_up'])
            ->where(function ($query) use ($today) {
                $query->whereNull('return_date')
                    ->orWhereDate('return_date', '>=', $today);
            })
            ->count();
        $overdue = DB::table('borrowing_records')
            ->whereNull('deleted_at')
            ->whereIn('status', ['approved', 'picked_up'])
            ->whereNotNull('return_date')
            ->whereDate('return_date', '<', $today)
            ->count();

        $stats = [
            [
                'label' => __('Total users'),
                'value' => number_format($totalUsers),
                'icon' => 'users',
                'tone' => 'slate',
                'href' => null,
                'hint' => __('Registered accounts'),
            ],
            [
                'label' => __('Active students'),
                'value' => number_format($activeStudents),
                'icon' => 'academic-cap',
                'tone' => 'emerald',
                'href' => null,
                'hint' => __('Student accounts with active status'),
            ],
            [
                'label' => __('Resource listings'),
                'value' => number_format($totalResources),
                'icon' => 'collection',
                'tone' => 'sky',
                'href' => route('resources.index'),
                'hint' => __('Physical and digital resources'),
            ],
            [
                'label' => __('Pending requests'),
                'value' => number_format($pendingRequests),
                'icon' => 'clock',
                'tone' => 'amber',
                'href' => route('borrowing-records.index', ['status' => 'pending']),
                'hint' => __('Awaiting a decision'),
            ],
            [
                'label' => __('On loan'),
                'value' => number_format($onLoan),
                'icon' => 'borrowing',
                'tone' => 'indigo',
                'href' => route('borrowing-records.index'),
                'hint' => __('Approved or taken and not yet returned'),
            ],
            [
                'label' => __('Overdue returns'),
                'value' => number_format($overdue),
                'icon' => 'exclamation-triangle',
                'tone' => 'rose',
                'href' => route('borrowing-records.index'),
                'hint' => __('Past the recorded return date'),
            ],
        ];

        $recentUsers = DB::table('users')
            ->select('id', 'name', 'email', 'role', 'status', 'created_at')
            ->latest('id')
            ->limit(5)
            ->get();

        $recentResources = DB::table('resources as r')
            ->leftJoin('categories as c', 'c.id', '=', 'r.category_id')
            ->leftJoin('users as u', 'u.id', '=', 'r.owner_id')
            ->whereNull('r.deleted_at')
            ->select(
                'r.id',
                'r.title',
                'r.type',
                'r.availability_status',
                'r.created_at',
                'c.name as category_name',
                'u.name as owner_name'
            )
            ->latest('r.id')
            ->limit(5)
            ->get();

        $recentBorrowings = $this->recentBorrowingsQuery()
            ->limit(6)
            ->get()
            ->each(fn ($record) => $this->markOverdue($record, $today));

        return view('dashboard', [
            'isAdmin' => true,
            'stats' => $stats,
            'recentUsers' => $recentUsers,
            'recentResources' => $recentResources,
            'recentBorrowings' => $recentBorrowings,
            'recentNotifications' => collect(),
            'today' => now()->format('l, j F Y'),
        ]);
    }

    private function studentDashboard(int $userId): View
    {
        $today = now()->toDateString();

        $myListings = DB::table('resources')
            ->whereNull('deleted_at')
            ->where('owner_id', $userId)
            ->count();
        $pendingRequests = DB::table('borrowing_records')
            ->whereNull('deleted_at')
            ->where('requester_id', $userId)
            ->where('status', 'pending')
            ->count();
        $approvedLoans = DB::table('borrowing_records')
            ->whereNull('deleted_at')
            ->where('requester_id', $userId)
            ->whereIn('status', ['approved', 'picked_up'])
            ->count();
        $overdue = DB::table('borrowing_records')
            ->whereNull('deleted_at')
            ->where('requester_id', $userId)
            ->whereIn('status', ['approved', 'picked_up'])
            ->whereNotNull('return_date')
            ->whereDate('return_date', '<', $today)
            ->count();
        $unreadNotifications = DB::table('notifications')
            ->whereNull('deleted_at')
            ->where('user_id', $userId)
            ->where('is_read', false)
            ->count();

        $stats = [
            [
                'label' => __('My listings'),
                'value' => number_format($myListings),
                'icon' => 'collection',
                'tone' => 'sky',
                'href' => route('resources.index'),
                'hint' => __('Resources you have shared'),
            ],
            [
                'label' => __('Pending requests'),
                'value' => number_format($pendingRequests),
                'icon' => 'clock',
                'tone' => 'amber',
                'href' => route('public.borrowings.index', ['status' => 'pending']),
                'hint' => __('Waiting for approval'),
            ],
            [
                'label' => __('Active loans'),
                'value' => number_format($approvedLoans),
                'icon' => 'borrowing',
                'tone' => 'indigo',
                'href' => route('public.borrowings.index'),
                'hint' => __('Approved or taken and not yet returned'),
            ],
            [
                'label' => __('Overdue returns'),
                'value' => number_format($overdue),
                'icon' => 'exclamation-triangle',
                'tone' => 'rose',
                'href' => route('public.borrowings.index'),
                'hint' => __('Past the recorded return date'),
            ],
            [
                'label' => __('Unread notifications'),
                'value' => number_format($unreadNotifications),
                'icon' => 'bell',
                'tone' => 'emerald',
                'href' => route('public.notifications.index', ['filter' => 'unread']),
                'hint' => __('Updates you have not opened'),
            ],
        ];

        $recentResources = DB::table('resources as r')
            ->leftJoin('categories as c', 'c.id', '=', 'r.category_id')
            ->leftJoin('users as u', 'u.id', '=', 'r.owner_id')
            ->whereNull('r.deleted_at')
            ->where('r.availability_status', 'available')
            ->where('r.owner_id', '!=', $userId)
            ->select(
                'r.id',
                'r.title',
                'r.type',
                'r.availability_status',
                'r.created_at',
                'c.name as category_name',
                'u.name as owner_name'
            )
            ->latest('r.id')
            ->limit(5)
            ->get();

        $recentBorrowings = $this->recentBorrowingsQuery()
            ->where(function ($query) use ($userId) {
                $query->where('b.requester_id', $userId)
                    ->orWhere('b.owner_id', $userId);
            })
            ->limit(6)
            ->get()
            ->each(fn ($record) => $this->markOverdue($record, $today));

        $recentNotifications = DB::table('notifications')
            ->whereNull('deleted_at')
            ->where('user_id', $userId)
            ->latest('id')
            ->limit(5)
            ->get();

        return view('dashboard', [
            'isAdmin' => false,
            'stats' => $stats,
            'recentUsers' => collect(),
            'recentResources' => $recentResources,
            'recentBorrowings' => $recentBorrowings,
            'recentNotifications' => $recentNotifications,
            'today' => now()->format('l, j F Y'),
        ]);
    }

    private function recentBorrowingsQuery(): Builder
    {
        return DB::table('borrowing_records as b')
            ->leftJoin('resources as r', 'r.id', '=', 'b.resource_id')
            ->leftJoin('users as requester', 'requester.id', '=', 'b.requester_id')
            ->leftJoin('users as owner', 'owner.id', '=', 'b.owner_id')
            ->whereNull('b.deleted_at')
            ->select(
                'b.id',
                'b.status',
                'b.pickup_date',
                'b.return_date',
                'b.created_at',
                'r.title as resource_title',
                'requester.name as requester_name',
                'owner.name as owner_name'
            )
            ->orderByDesc('b.id');
    }

    private function markOverdue(object $record, string $today): void
    {
        $record->is_overdue = in_array($record->status, ['approved', 'picked_up'], true)
            && $record->return_date !== null
            && $record->return_date < $today;
    }
}
