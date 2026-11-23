<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Activity;
use App\Models\Folio;
use App\Models\FolioLine;
use App\Models\Reservation;
use App\Models\Room;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;
use RuntimeException;

/**
 * The state changes the front desk performs on a room.
 *
 * Each one is guarded: the board decides what it offers, but the guard decides
 * what actually happens, so a stale page cannot check a guest in twice.
 */
class FrontDeskActions
{
    public function __construct(private readonly User $user) {}

    /**
     * Admits the guest expected in this room today.
     *
     * @throws RuntimeException when no arrival is pending on the room
     */
    public function checkIn(Room $room): Reservation
    {
        return DB::transaction(function () use ($room): Reservation {
            $reservation = $this->pendingArrival($room);

            if ($reservation === null) {
                throw new RuntimeException('front_desk.errors.no_arrival');
            }

            if ($room->status === 'out_of_order') {
                throw new RuntimeException('front_desk.errors.out_of_order');
            }

            $reservation->update(['status' => 'in_house']);
            $room->update(['status' => 'occupied']);

            $this->openFolio($reservation);

            $this->record($room, 'checkin', 'front_desk.activity.checkin', $reservation->code);

            return $reservation;
        });
    }

    /**
     * Releases the room: the stay ends and the room goes back to housekeeping.
     *
     * @throws RuntimeException when no stay is due to leave
     */
    public function checkOut(Room $room): Reservation
    {
        return DB::transaction(function () use ($room): Reservation {
            $reservation = $this->departingStay($room);

            if ($reservation === null) {
                throw new RuntimeException('front_desk.errors.no_departure');
            }

            $reservation->update(['status' => 'departed']);

            // A departed room is dirty until housekeeping says otherwise.
            $room->update(['status' => 'vacant_dirty', 'housekeeping_status' => 'dirty']);

            // The folio only closes once nothing is owed: an unpaid balance has
            // to stay visible on the dashboard rather than disappear.
            $folio = $reservation->folio;

            if ($folio !== null) {
                $charges = (int) $folio->lines()->sum('amount');
                $paid = (int) $folio->payments()->sum('amount');

                if ($charges - $paid <= 0) {
                    $folio->update(['status' => 'closed', 'closed_at' => now()]);
                }
            }

            $this->record($room, 'checkout', 'front_desk.activity.checkout', $reservation->code);

            return $reservation;
        });
    }

    /**
     * A stay needs a folio from the moment the guest is in the room, with the
     * first night already charged: that is what the desk bills against.
     */
    private function openFolio(Reservation $reservation): void
    {
        $folio = $reservation->folio ?? Folio::create([
            'hotel_id' => $reservation->hotel_id,
            'reservation_id' => $reservation->id,
            'code' => str_replace('BK-', 'FO-', $reservation->code),
            'status' => 'open',
        ]);

        $night = $this->today($reservation->room);

        $alreadyPosted = $folio->lines()
            ->where('department', 'rooms')
            ->whereDate('posted_on', $night)
            ->exists();

        if ($alreadyPosted) {
            return;
        }

        FolioLine::create([
            'hotel_id' => $reservation->hotel_id,
            'folio_id' => $folio->id,
            'department' => 'rooms',
            'description' => trans('front_desk.folio.night'),
            'amount' => $reservation->room_rate,
            'posted_on' => $night,
        ]);
    }

    /**
     * Records the cleaning state. A clean vacant room becomes sellable again.
     */
    public function setHousekeeping(Room $room, string $state): Room
    {
        if (! in_array($state, Room::HOUSEKEEPING_STATUSES, true)) {
            throw new RuntimeException('front_desk.errors.unknown_state');
        }

        $attributes = ['housekeeping_status' => $state];

        if ($room->status === 'vacant_dirty' && $state === 'clean') {
            $attributes['status'] = 'vacant_clean';
        }

        if ($room->status === 'vacant_clean' && $state === 'dirty') {
            $attributes['status'] = 'vacant_dirty';
        }

        $room->update($attributes);

        $this->record($room, 'housekeeping', "front_desk.activity.housekeeping_{$state}");

        return $room;
    }

    /**
     * Takes a room out of service, or puts it back.
     *
     * @throws RuntimeException when a guest is in the room
     */
    public function setOutOfOrder(Room $room, bool $outOfOrder): Room
    {
        if ($outOfOrder && $room->status === 'occupied') {
            throw new RuntimeException('front_desk.errors.occupied');
        }

        $room->update(
            $outOfOrder
                ? ['status' => 'out_of_order', 'is_sellable' => false]
                : ['status' => 'vacant_dirty', 'housekeeping_status' => 'dirty', 'is_sellable' => true]
        );

        $this->record(
            $room,
            'room',
            $outOfOrder ? 'front_desk.activity.out_of_order' : 'front_desk.activity.back_in_service',
        );

        return $room;
    }

    private function pendingArrival(Room $room): ?Reservation
    {
        return Reservation::where('hotel_id', $room->hotel_id)
            ->where('room_id', $room->id)
            ->where('status', 'confirmed')
            ->whereDate('check_in_date', $this->today($room))
            ->lockForUpdate()
            ->first();
    }

    private function departingStay(Room $room): ?Reservation
    {
        return Reservation::where('hotel_id', $room->hotel_id)
            ->where('room_id', $room->id)
            ->where('status', 'in_house')
            ->whereDate('check_out_date', '<=', $this->today($room))
            ->lockForUpdate()
            ->first();
    }

    private function today(Room $room): string
    {
        return CarbonImmutable::today($room->hotel?->timezone ?: config('app.timezone'))->toDateString();
    }

    /**
     * The activity feed is written in the reader's language at display time, so
     * the translation key is stored rather than a sentence.
     */
    private function record(Room $room, string $kind, string $key, ?string $reference = null): void
    {
        Activity::create([
            'hotel_id' => $room->hotel_id,
            'user_id' => $this->user->id,
            'kind' => $kind,
            'description' => trans($key, ['room' => $room->number]),
            'reference' => $reference ?? $room->number,
        ]);
    }
}
