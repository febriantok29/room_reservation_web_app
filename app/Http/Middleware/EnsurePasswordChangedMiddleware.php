<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsurePasswordChangedMiddleware
{
    /**
     * Redirect authenticated admins to the change-password page until their password is changed.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user && $user->must_change_password && ! $request->routeIs('admin.password.change*')) {
            return redirect()->route('admin.password.change');
        }

        return $next($request);
    }
}
