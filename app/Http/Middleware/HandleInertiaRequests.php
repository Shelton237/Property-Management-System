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
    private const TRANSLATION_GROUPS = ['common', 'login', 'dashboard', 'password_reset', 'navigation'];

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
            // The backoffice shell shows the property and the business date on
            // every page, so both are shared rather than passed per controller.
            'hotel' => $this->hotel($request),
            'businessDate' => $this->businessDate($request),
            'locale' => app()->getLocale(),
            'locales' => config('branding.locales'),
            'translations' => $this->translations(),
        ];
    }

    /**
     * @return array<string, string|null>|null
     */
    private function hotel(Request $request): ?array
    {
        $hotel = $request->user()?->hotel;

        if ($hotel === null) {
            return null;
        }

        return [
            'name' => $hotel->name,
            'city' => $hotel->city,
        ];
    }

    /**
     * The date the property is working on, in its own timezone.
     *
     * Until the night audit module exists, it is simply today: the audit will
     * later be what moves this date forward.
     *
     * @return array<string, string>|null
     */
    private function businessDate(Request $request): ?array
    {
        $user = $request->user();

        if ($user === null) {
            return null;
        }

        $date = now()
            ->timezone($user->hotel?->timezone ?? config('app.timezone'))
            ->locale(app()->getLocale());

        return [
            'label' => $date->isoFormat('ddd D MMM YYYY'),
            'iso' => $date->toDateString(),
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
