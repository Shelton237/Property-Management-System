<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template loaded on the first page visit.
     */
    protected $rootView = 'app';

    /**
     * Translation groups handed to the front end, so no component holds a
     * visible string of its own.
     *
     * @var list<string>
     */
    private const TRANSLATION_GROUPS = ['common', 'login', 'dashboard', 'password_reset'];

    /**
     * Define the props that are shared by default.
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        return [
            ...parent::share($request),
            'name' => config('app.name'),
            'brand' => [
                'name' => config('branding.name'),
            ],
            'auth' => [
                'user' => $request->user(),
            ],
            'locale' => app()->getLocale(),
            'locales' => config('branding.locales'),
            'translations' => $this->translations(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function translations(): array
    {
        $translations = [];

        foreach (self::TRANSLATION_GROUPS as $group) {
            $lines = trans($group);

            $translations[$group] = is_array($lines) ? $lines : [];
        }

        return $translations;
    }
}
