<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\FrontDeskController;
use App\Http\Controllers\LocaleController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

/*
 * There is no public landing page yet, so the root sends visitors straight to
 * the part of the product they can actually use.
 */
Route::get('/', function () {
    return redirect()->route(Auth::check() ? 'dashboard' : 'login');
})->name('home');

Route::get('locale/{locale}', LocaleController::class)->name('locale.update');

Route::middleware(['auth', 'hotel.active'])->group(function () {
    Route::get('dashboard', DashboardController::class)->name('dashboard');

    Route::prefix('front-desk')->name('front-desk.')->group(function () {
        Route::get('/', [FrontDeskController::class, 'index'])->name('index');
        Route::post('rooms/{room}/check-in', [FrontDeskController::class, 'checkIn'])->name('check-in');
        Route::post('rooms/{room}/check-out', [FrontDeskController::class, 'checkOut'])->name('check-out');
        Route::post('rooms/{room}/housekeeping', [FrontDeskController::class, 'housekeeping'])->name('housekeeping');
        Route::post('rooms/{room}/service', [FrontDeskController::class, 'service'])->name('service');
    });
});

require __DIR__.'/settings.php';
require __DIR__.'/auth.php';
