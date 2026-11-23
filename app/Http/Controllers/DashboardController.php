<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    /**
     * The property and the business date reach the page through the shared
     * Inertia props, since the whole backoffice shell needs them.
     */
    public function __invoke(): Response
    {
        return Inertia::render('Dashboard');
    }
}
