<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Services\DashboardMetrics;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    /**
     * The property and the business date reach the page through the shared
     * Inertia props, since the whole backoffice shell needs them.
     *
     * An account with no property, such as a platform administrator, gets no
     * metrics: the page then shows why rather than a wall of zeros.
     */
    public function __invoke(Request $request): Response
    {
        $hotel = $request->user()?->hotel;

        return Inertia::render('Dashboard', [
            'metrics' => $hotel === null ? null : (new DashboardMetrics($hotel))->all(),
        ]);
    }
}
