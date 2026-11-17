<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/** Konto działa dopiero po potwierdzeniu numeru telefonu kodem SMS. */
class EnsurePhoneVerified
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        if ($user && ! $user->isAdmin() && ! $user->hasVerifiedPhone()) {
            return redirect()->route('verify.show');
        }

        return $next($request);
    }
}
