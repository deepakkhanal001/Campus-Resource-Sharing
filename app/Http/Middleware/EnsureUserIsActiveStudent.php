<?php

namespace App\Http\Middleware;

use App\Support\CollegeStudentRegistry;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsActiveStudent
{
    public function handle(Request $request, Closure $next): Response
    {
        $account = CollegeStudentRegistry::account((int) $request->user()?->getAuthIdentifier());

        if (! $account) {
            abort(403, 'Your account could not be found.');
        }

        if (CollegeStudentRegistry::isAdmin($account)) {
            return $next($request);
        }

        if (! CollegeStudentRegistry::isActiveStudent($account)) {
            abort(403, 'Your student account must be active before using this feature.');
        }

        return $next($request);
    }
}
