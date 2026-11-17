<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Wpuszcza tylko konta o podanej roli, np. ->middleware('role:company').
 * Zablokowane konta są wylogowywane.
 */
class EnsureRole
{
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();

        if ($user && $user->isBlocked()) {
            auth()->logout();
            $request->session()->invalidate();

            return redirect()->route('login')->withErrors(['email' => __('To konto zostało zablokowane.')]);
        }

        abort_unless($user && in_array($user->role, $roles, true), 403);

        return $next($request);
    }
}
