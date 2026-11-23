<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Resolves the display language: an explicit choice stored in the session wins,
 * then the signed in user's own preference, then the application default.
 */
class SetLocale
{
    public function handle(Request $request, Closure $next): Response
    {
        $supported = config('branding.locales');

        $locale = $request->session()->get('locale')
            ?? $request->user()?->locale
            ?? config('app.locale');

        if (! in_array($locale, $supported, true)) {
            $locale = config('app.locale');
        }

        app()->setLocale($locale);

        return $next($request);
    }
}
