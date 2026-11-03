<?php

namespace App\Http\Middleware;

use App\Support\CollegeStudentRegistry;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsAdmin
{
    public function handle(Request $request, Closure $next): Response
    {
        $account = CollegeStudentRegistry::account((int) $request->user()?->getAuthIdentifier());

        abort_unless(
            CollegeStudentRegistry::isAdmin($account),
            403,
            'Only administrators can perform this action.'
        );

        return $next($request);
    }
}
