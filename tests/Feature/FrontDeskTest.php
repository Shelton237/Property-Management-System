<?php

declare(strict_types=1);

use App\Models\Activity;
use App\Models\Folio;
use App\Models\FolioLine;
use App\Models\Guest;
use App\Models\Hotel;
use App\Models\Payment;
use App\Models\Reservation;
use App\Models\Room;
use App\Models\RoomType;
use App\Models\User;

/**
 * @return array{0: Hotel, 1: User, 2: Room}
 */
function frontDeskSetup(string $roomStatus = 'vacant_clean'): array
{
    $hotel = Hotel::factory()->create(['timezone' => 'Africa/Douala']);
    $user = User::factory()->for($hotel)->create();

    $type = RoomType::create([
        'hotel_id' => $hotel->id, 'name' => 'Standard', 'code' => 'STD', 'base_rate' => 30000,
    ]);

    $room = Room::create([
        'hotel_id' => $hotel->id, 'room_type_id' => $type->id, 'number' => '101',
        'floor' => '1', 'status' => $roomStatus, 'housekeeping_status' => 'clean',
    ]);

    return [$hotel, $user, $room];
}

function reservationFor(Hotel $hotel, Room $room, string $status, string $checkIn, string $checkOut): Reservation
{
    $guest = Guest::create(['hotel_id' => $hotel->id, 'name' => 'Awa Nkolo', 'country' => 'CM']);

    return Reservation::create([
        'hotel_id' => $hotel->id,
        'guest_id' => $guest->id,
        'room_id' => $room->id,
        'code' => 'BK-'.fake()->unique()->numerify('######'),
        'source' => 'direct_phone',
        'status' => $status,
        'check_in_date' => $checkIn,
        'check_out_date' => $checkOut,
        'room_rate' => 30000,
    ]);
}

it('shows the board with its filter counts', function () {
    [$hotel, $user, $room] = frontDeskSetup();
    reservationFor($hotel, $room, 'confirmed', now()->toDateString(), now()->addDay()->toDateString());

    $this->actingAs($user)
        ->get(route('front-desk.index'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('front-desk/Index')
            ->has('board.rooms', 1)
            ->where('board.counts.all', 1)
            ->where('board.counts.arrivals', 1)
            ->where('board.rooms.0.canCheckIn', true)
            ->has('planner.dates', 7));
});

it('checks a guest in', function () {
    [$hotel, $user, $room] = frontDeskSetup();
    $reservation = reservationFor($hotel, $room, 'confirmed', now()->toDateString(), now()->addDay()->toDateString());

    $this->actingAs($user)
        ->post(route('front-desk.check-in', $room))
        ->assertSessionHasNoErrors();

    expect($reservation->fresh()->status)->toBe('in_house')
        ->and($room->fresh()->status)->toBe('occupied')
        ->and(Activity::where('kind', 'checkin')->count())->toBe(1);
});

it('refuses a check in when nobody is expected', function () {
    [, $user, $room] = frontDeskSetup();

    $this->actingAs($user)
        ->post(route('front-desk.check-in', $room))
        ->assertSessionHasErrors('room');

    expect($room->fresh()->status)->toBe('vacant_clean');
});

it('refuses a check in on a room out of service', function () {
    [$hotel, $user, $room] = frontDeskSetup('out_of_order');
    reservationFor($hotel, $room, 'confirmed', now()->toDateString(), now()->addDay()->toDateString());

    $this->actingAs($user)
        ->post(route('front-desk.check-in', $room))
        ->assertSessionHasErrors('room');

    expect($room->fresh()->status)->toBe('out_of_order');
});

it('checks a guest out and leaves the room dirty', function () {
    [$hotel, $user, $room] = frontDeskSetup('occupied');
    $reservation = reservationFor($hotel, $room, 'in_house', now()->subDay()->toDateString(), now()->toDateString());

    $this->actingAs($user)
        ->post(route('front-desk.check-out', $room))
        ->assertSessionHasNoErrors();

    expect($reservation->fresh()->status)->toBe('departed')
        ->and($room->fresh()->status)->toBe('vacant_dirty')
        ->and($room->fresh()->housekeeping_status)->toBe('dirty');
});

it('keeps the folio open when money is still owed', function () {
    [$hotel, $user, $room] = frontDeskSetup('occupied');
    $reservation = reservationFor($hotel, $room, 'in_house', now()->subDay()->toDateString(), now()->toDateString());

    $folio = Folio::create([
        'hotel_id' => $hotel->id, 'reservation_id' => $reservation->id, 'code' => 'FO-1', 'status' => 'open',
    ]);
    FolioLine::create([
        'hotel_id' => $hotel->id, 'folio_id' => $folio->id, 'department' => 'rooms',
        'description' => 'Nuitée', 'amount' => 30000, 'posted_on' => now()->toDateString(),
    ]);

    $this->actingAs($user)->post(route('front-desk.check-out', $room));

    expect($folio->fresh()->status)->toBe('open');
});

it('closes the folio once it is settled', function () {
    [$hotel, $user, $room] = frontDeskSetup('occupied');
    $reservation = reservationFor($hotel, $room, 'in_house', now()->subDay()->toDateString(), now()->toDateString());

    $folio = Folio::create([
        'hotel_id' => $hotel->id, 'reservation_id' => $reservation->id, 'code' => 'FO-2', 'status' => 'open',
    ]);
    FolioLine::create([
        'hotel_id' => $hotel->id, 'folio_id' => $folio->id, 'department' => 'rooms',
        'description' => 'Nuitée', 'amount' => 30000, 'posted_on' => now()->toDateString(),
    ]);
    Payment::create([
        'hotel_id' => $hotel->id, 'folio_id' => $folio->id, 'method' => 'cash',
        'amount' => 30000, 'received_on' => now()->toDateString(),
    ]);

    $this->actingAs($user)->post(route('front-desk.check-out', $room));

    expect($folio->fresh()->status)->toBe('closed')
        ->and($folio->fresh()->closed_at)->not->toBeNull();
});

it('marks a room clean and makes it sellable again', function () {
    [, $user, $room] = frontDeskSetup('vacant_dirty');
    $room->update(['housekeeping_status' => 'dirty']);

    $this->actingAs($user)
        ->post(route('front-desk.housekeeping', $room), ['state' => 'clean'])
        ->assertSessionHasNoErrors();

    expect($room->fresh()->housekeeping_status)->toBe('clean')
        ->and($room->fresh()->status)->toBe('vacant_clean');
});

it('rejects an unknown cleaning state', function () {
    [, $user, $room] = frontDeskSetup();

    $this->actingAs($user)
        ->post(route('front-desk.housekeeping', $room), ['state' => 'sparkling'])
        ->assertSessionHasErrors('state');
});

it('takes a room out of service and puts it back', function () {
    [, $user, $room] = frontDeskSetup();

    $this->actingAs($user)->post(route('front-desk.service', $room), ['out_of_order' => true]);

    expect($room->fresh()->status)->toBe('out_of_order')
        ->and($room->fresh()->is_sellable)->toBeFalse();

    $this->actingAs($user)->post(route('front-desk.service', $room), ['out_of_order' => false]);

    expect($room->fresh()->status)->toBe('vacant_dirty')
        ->and($room->fresh()->is_sellable)->toBeTrue();
});

it('refuses to take an occupied room out of service', function () {
    [$hotel, $user, $room] = frontDeskSetup('occupied');
    reservationFor($hotel, $room, 'in_house', now()->subDay()->toDateString(), now()->addDay()->toDateString());

    $this->actingAs($user)
        ->post(route('front-desk.service', $room), ['out_of_order' => true])
        ->assertSessionHasErrors('room');

    expect($room->fresh()->status)->toBe('occupied');
});

it('refuses to act on a room belonging to another hotel', function () {
    [, $user] = frontDeskSetup();
    [$otherHotel, , $otherRoom] = frontDeskSetup();
    reservationFor($otherHotel, $otherRoom, 'confirmed', now()->toDateString(), now()->addDay()->toDateString());

    $this->actingAs($user)
        ->post(route('front-desk.check-in', $otherRoom))
        ->assertNotFound();

    expect($otherRoom->fresh()->status)->toBe('vacant_clean');
});

it('cannot check the same guest in twice', function () {
    [$hotel, $user, $room] = frontDeskSetup();
    reservationFor($hotel, $room, 'confirmed', now()->toDateString(), now()->addDay()->toDateString());

    $this->actingAs($user)->post(route('front-desk.check-in', $room))->assertSessionHasNoErrors();

    // The board is now stale: the button would still be there on an old page.
    $this->actingAs($user)
        ->post(route('front-desk.check-in', $room))
        ->assertSessionHasErrors('room');

    expect(Activity::where('kind', 'checkin')->count())->toBe(1);
});

it("opens the folio and charges the first night on check in", function () {
    [$hotel, $user, $room] = frontDeskSetup();
    $reservation = reservationFor($hotel, $room, "confirmed", now()->toDateString(), now()->addDays(2)->toDateString());

    expect($reservation->folio)->toBeNull();

    $this->actingAs($user)->post(route("front-desk.check-in", $room))->assertSessionHasNoErrors();

    $folio = $reservation->fresh()->folio;

    expect($folio)->not->toBeNull()
        ->and($folio->status)->toBe("open")
        ->and((int) $folio->lines()->where("department", "rooms")->sum("amount"))->toBe(30000);
});
