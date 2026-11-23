<?php

declare(strict_types=1);

use App\Models\Folio;
use App\Models\FolioLine;
use App\Models\Guest;
use App\Models\Hotel;
use App\Models\Payment;
use App\Models\Reservation;
use App\Models\Room;
use App\Models\RoomType;
use App\Models\User;
use App\Services\DashboardMetrics;

/**
 * Builds a two-room property with one stay in progress, so every figure has a
 * value that can be checked by hand.
 */
function demoProperty(): Hotel
{
    $hotel = Hotel::factory()->create(['timezone' => 'Africa/Douala', 'currency' => 'XAF']);

    $type = RoomType::create([
        'hotel_id' => $hotel->id, 'name' => 'Standard', 'code' => 'STD', 'base_rate' => 30000,
    ]);

    $occupied = Room::create([
        'hotel_id' => $hotel->id, 'room_type_id' => $type->id, 'number' => '101',
        'status' => 'occupied', 'housekeeping_status' => 'dirty',
    ]);

    Room::create([
        'hotel_id' => $hotel->id, 'room_type_id' => $type->id, 'number' => '102',
        'status' => 'vacant_clean', 'housekeeping_status' => 'clean',
    ]);

    $guest = Guest::create(['hotel_id' => $hotel->id, 'name' => 'Awa Nkolo', 'country' => 'CM']);

    $reservation = Reservation::create([
        'hotel_id' => $hotel->id,
        'guest_id' => $guest->id,
        'room_id' => $occupied->id,
        'code' => 'BK-000001',
        'source' => 'walk_in',
        'status' => 'in_house',
        'check_in_date' => now()->toDateString(),
        'check_out_date' => now()->addDay()->toDateString(),
        'room_rate' => 30000,
    ]);

    $folio = Folio::create([
        'hotel_id' => $hotel->id, 'reservation_id' => $reservation->id,
        'code' => 'FO-000001', 'status' => 'open',
    ]);

    FolioLine::create([
        'hotel_id' => $hotel->id, 'folio_id' => $folio->id, 'department' => 'rooms',
        'description' => 'Nuitée', 'amount' => 30000, 'posted_on' => now()->toDateString(),
    ]);

    FolioLine::create([
        'hotel_id' => $hotel->id, 'folio_id' => $folio->id, 'department' => 'restaurant',
        'description' => 'Restaurant', 'amount' => 10000, 'posted_on' => now()->toDateString(),
    ]);

    Payment::create([
        'hotel_id' => $hotel->id, 'folio_id' => $folio->id, 'method' => 'orange_money',
        'amount' => 15000, 'received_on' => now()->toDateString(),
    ]);

    return $hotel;
}

it('computes the key figures of the day', function () {
    $metrics = (new DashboardMetrics(demoProperty()))->all();

    $byKey = collect($metrics['kpis'])->keyBy('key');

    // One of two sellable rooms sold.
    expect($byKey['occupancy']['value'])->toBe(50.0)
        // Room revenue divided by rooms sold.
        ->and($byKey['adr']['value'])->toBe(30000.0)
        // Room revenue spread over every sellable room.
        ->and($byKey['revpar']['value'])->toBe(15000.0)
        // Every charge posted today, rooms and restaurant alike.
        ->and($byKey['revenue_today']['value'])->toBe(40000)
        ->and($byKey['guests_in_house']['value'])->toBe(1);
});

it('splits revenue into rooms, food and beverage, and the rest', function () {
    $metrics = (new DashboardMetrics(demoProperty()))->all();

    $today = collect($metrics['revenueSeries']['days'])->last();

    expect($today['rooms'])->toBe(30000)
        ->and($today['food_beverage'])->toBe(10000)
        ->and($today['other'])->toBe(0)
        ->and($metrics['revenueSeries']['total'])->toBe(40000);
});

it('reports the balance left on an open folio', function () {
    $metrics = (new DashboardMetrics(demoProperty()))->all();

    // 40 000 charged, 15 000 paid.
    expect($metrics['outstanding']['accounts'])->toBe(1)
        ->and($metrics['outstanding']['total'])->toBe(25000)
        ->and($metrics['outstanding']['rows'][0]['guest'])->toBe('Awa Nkolo');
});

it('keeps the movements lists and the movements figure in step', function () {
    $metrics = (new DashboardMetrics(demoProperty()))->all();

    $movements = collect($metrics['kpis'])->firstWhere('key', 'movements');

    expect(count($metrics['movements']['arrivals']))->toBe($movements['value'])
        ->and(count($metrics['movements']['departures']))->toBe($movements['secondaryValue']);
});

it('counts every room exactly once across the cleaning states', function () {
    $metrics = (new DashboardMetrics(demoProperty()))->all();

    expect($metrics['housekeeping']['total'])->toBe(2)
        ->and($metrics['housekeeping']['clean'])->toBe(1)
        ->and($metrics['housekeeping']['done'])->toBe(50);
});

it('renders the dashboard with its metrics', function () {
    $hotel = demoProperty();
    $user = User::factory()->for($hotel)->create();

    $this->actingAs($user)
        ->get(route('dashboard'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('Dashboard')
            ->has('metrics.kpis', 6)
            ->has('metrics.roomStatus.rows', 7)
            ->has('metrics.occupancyTrend', 30)
            ->has('metrics.onTheBooks', 14)
            ->where('metrics.currency', 'XAF'));
});

it('shows no metrics for an account without a property', function () {
    $user = User::factory()->superAdmin()->create();

    $this->actingAs($user)
        ->get(route('dashboard'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page->where('metrics', null));
});
