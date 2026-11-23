<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function __invoke(Request $request): Response
    {
        $user = $request->user();

        return Inertia::render('Dashboard', [
            'hotel' => $user->hotel === null ? null : [
                'name' => $user->hotel->name,
                'city' => $user->hotel->city,
            ],
        ]);
    }
}
