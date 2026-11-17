<?php

namespace App\Http\Middleware;

use App\Support\Languages;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Ustawia język portalu: najpierw wybór zalogowanego użytkownika (z rejestracji/ustawień),
 * potem wybór z przełącznika (sesja), na końcu polski.
 */
class SetLocale
{
    public function handle(Request $request, Closure $next): Response
    {
        $locale = $request->user()?->locale ?? $request->session()->get('locale', 'pl');
        if (! array_key_exists($locale, Languages::ALL)) {
            $locale = 'pl';
        }
        app()->setLocale($locale);

        return $next($request);
    }
}
