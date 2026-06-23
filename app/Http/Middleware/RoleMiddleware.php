<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
public function handle(Request $request, Closure $next, ...$roles): Response
{
    $user = Auth::user();
    // $roles is an array of strings passed after $next
    // e.g., if middleware is 'role:admin,editor', $roles = ['admin', 'editor']
    // e.g., if middleware is 'role:admin|superadmin', $roles = ['admin|superadmin']

    if (!$user) { // First, check if user is authenticated
        abort(403, 'Unauthorized');
    }

    $userHasRole = false;
    $normalizedRoles = [];

    foreach ($roles as $roleString) {
        $individualRoles = explode('|', $roleString);
        foreach ($individualRoles as $individualRole) {
            $normalizedRoles[] = trim($individualRole); // Trim whitespace just in case
        }
    }
    // At this point, $normalizedRoles is a flat array of all allowed roles
    // e.g., for 'role:admin|superadmin,editor', $normalizedRoles would be ['admin', 'superadmin', 'editor']

    if (in_array($user->role, $normalizedRoles)) {
        $userHasRole = true;
    }

    if (!$userHasRole) {
        abort(403, 'Unauthorized');
    }

    return $next($request);
}
}
