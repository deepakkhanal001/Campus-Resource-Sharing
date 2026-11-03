<?php

namespace App\Http\Controllers;

use App\Support\PublicCatalog;
use Illuminate\Support\Facades\DB;

abstract class PublicPortalController extends Controller
{
    protected function activeUserOrFail(): object
    {
        $user = PublicCatalog::findUser((int) auth()->id());

        abort_unless(
            $user && strtolower((string) $user->status) === 'active',
            403,
            'Your account is not active.'
        );

        return $user;
    }

    protected function activeStudentOrFail(): object
    {
        $user = $this->activeUserOrFail();

        abort_unless(
            strtolower((string) $user->role) === 'student',
            403,
            'Only student accounts can request resources.'
        );

        return $user;
    }

    protected function lockClause(): string
    {
        return in_array(DB::connection()->getDriverName(), ['mysql', 'mariadb', 'pgsql'], true)
            ? ' FOR UPDATE'
            : '';
    }

    protected function createNotification(int $userId, string $message, string $type): void
    {
        DB::insert(
            'INSERT INTO notifications (user_id, message, type, is_read, created_at)
             VALUES (?, ?, ?, 0, ?)',
            [$userId, $message, $type, now()->toDateTimeString()]
        );
    }
}
