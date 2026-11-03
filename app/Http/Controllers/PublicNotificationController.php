<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class PublicNotificationController extends PublicPortalController
{
    public function index(Request $request): View
    {
        $user = $this->activeUserOrFail();
        $filter = $request->query('filter') === 'unread' ? 'unread' : 'all';

        $where = ['user_id = ?', 'deleted_at IS NULL'];
        $bindings = [(int) $user->id];

        if ($filter === 'unread') {
            $where[] = 'is_read = 0';
        }

        $whereSql = implode(' AND ', $where);

        $total = (int) (DB::selectOne(
            'SELECT COUNT(*) AS total FROM notifications WHERE '.$whereSql,
            $bindings
        )->total ?? 0);

        $perPage = 15;
        $lastPage = max(1, (int) ceil($total / $perPage));
        $page = min(max(1, (int) $request->query('page', 1)), $lastPage);
        $offset = ($page - 1) * $perPage;

        $notifications = DB::select(
            'SELECT id, message, type, is_read, created_at
             FROM notifications
             WHERE '.$whereSql.'
             ORDER BY created_at DESC, id DESC
             LIMIT '.$perPage.' OFFSET '.$offset,
            $bindings
        );

        return view('public.notifications.index', [
            'notifications' => new LengthAwarePaginator(
                $notifications,
                $total,
                $perPage,
                $page,
                [
                    'path' => route('public.notifications.index'),
                    'query' => $request->query(),
                ]
            ),
            'filter' => $filter,
            'unreadCount' => $this->notificationCount((int) $user->id, true),
            'totalCount' => $this->notificationCount((int) $user->id, false),
        ]);
    }

    public function markAsRead(int $notification): RedirectResponse
    {
        $user = $this->activeUserOrFail();

        DB::update(
            'UPDATE notifications
             SET is_read = 1, updated_at = ?
             WHERE id = ? AND user_id = ? AND deleted_at IS NULL',
            [now()->toDateTimeString(), $notification, (int) $user->id]
        );

        return back()->with('success', __('Notification marked as read.'));
    }

    public function markAllAsRead(): RedirectResponse
    {
        $user = $this->activeUserOrFail();

        DB::update(
            'UPDATE notifications
             SET is_read = 1, updated_at = ?
             WHERE user_id = ? AND is_read = 0 AND deleted_at IS NULL',
            [now()->toDateTimeString(), (int) $user->id]
        );

        return back()->with('success', __('All notifications marked as read.'));
    }

    private function notificationCount(int $userId, bool $unreadOnly): int
    {
        $sql = 'SELECT COUNT(*) AS total FROM notifications WHERE user_id = ? AND deleted_at IS NULL';
        $bindings = [$userId];

        if ($unreadOnly) {
            $sql .= ' AND is_read = 0';
        }

        return (int) (DB::selectOne($sql, $bindings)->total ?? 0);
    }
}
