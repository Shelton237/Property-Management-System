<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class LocaleController extends Controller
{
    /**
     * Store the display language chosen from the language switcher.
     */
    public function __invoke(Request $request, string $locale): RedirectResponse
    {
        abort_unless(in_array($locale, config('branding.locales'), true), 404);

        $request->session()->put('locale', $locale);

        return back();
    }
}
