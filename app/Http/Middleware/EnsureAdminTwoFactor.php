<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureAdminTwoFactor
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user?->isAdmin()) {
            abort(403);
        }

        if ($user->two_factor_confirmed_at === null) {
            return to_route('security.edit')
                ->with('error', 'Set up two-factor authentication before opening the admin area.');
        }

        return $next($request);
    }
}
