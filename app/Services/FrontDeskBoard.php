<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Hotel;
use App\Models\Reservation;
use App\Models\Room;
use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;

/**
 * The room board the front desk works from: every sellable room with its state
 * today, the stay occupying it, and the movements expected on it.
 */
class FrontDeskBoard
{
    private CarbonImmutable $today;

    public function __construct(private readonly Hotel $hotel)
    {
        $this->today = CarbonImmutable::today($hotel->timezone ?: config('app.timezone'));
    }

    /**
     * @return array<string, mixed>
     */
    public function today(): array
    {
        $rooms = Room::with('roomType')
            ->where('hotel_id', $this->hotel->id)
            ->orderBy('floor')
            ->orderBy('number')
            ->get();

        $stays = $this->staysByRoom();

        $cards = $rooms->map(fn (Room $room): array => $this->card($room, $stays))->all();

        return [
            'businessDate' => $this->today->toDateString(),
            'rooms' => $cards,
            'counts' => $this->counts($cards),
            'floors' => $rooms->pluck('floor')->filter()->unique()->sort()->values()->all(),
            'roomTypes' => $rooms->pluck('roomType.name', 'roomType.id')
                ->filter()
                ->unique()
                ->map(static fn (string $name, int|string $id): array => ['id' => (int) $id, 'name' => $name])
                ->values()
                ->all(),
        ];
    }

    /**
     * Seven days of occupancy, one row per room.
     *
     * @return array<string, mixed>
     */
    public function planner(int $days = 7): array
    {
        $until = $this->today->addDays($days - 1);

        $rooms = Room::with('roomType')
            ->where('hotel_id', $this->hotel->id)
            ->orderBy('floor')
            ->orderBy('number')
            ->get();

        $stays = Reservation::with('guest')
            ->where('hotel_id', $this->hotel->id)
            ->whereIn('status', ['confirmed', 'in_house'])
            ->where('check_in_date', '<=', $until->toDateString())
            ->where('check_out_date', '>', $this->today->toDateString())
            ->get();

        $dates = [];
        for ($date = $this->today; $date->lessThanOrEqualTo($until); $date = $date->addDay()) {
            $dates[] = $date->toDateString();
        }

        $rows = $rooms->map(function (Room $room) use ($dates, $stays): array {
            $cells = [];

            foreach ($dates as $date) {
                $stay = $stays->first(
                    fn (Reservation $reservation): bool => $reservation->room_id === $room->id
                        && $reservation->check_in_date->toDateString() <= $date
                        && $reservation->check_out_date->toDateString() > $date
                );

                $cells[] = [
                    'date' => $date,
                    'guest' => $stay?->guest?->name,
                    'code' => $stay?->code,
                    'status' => $stay?->status,
                    'arrival' => $stay !== null && $stay->check_in_date->toDateString() === $date,
                ];
            }

            return [
                'id' => $room->id,
                'number' => $room->number,
                'type' => $room->roomType?->name,
                'outOfOrder' => $room->status === 'out_of_order',
                'cells' => $cells,
            ];
        })->all();

        return ['dates' => $dates, 'rows' => $rows];
    }

    /**
     * Stays that matter today, grouped by room.
     *
     * @return Collection<int, Collection<int, Reservation>>
     */
    private function staysByRoom(): Collection
    {
        return Reservation::with('guest')
            ->where('hotel_id', $this->hotel->id)
            ->whereIn('status', ['confirmed', 'in_house'])
            ->where('check_in_date', '<=', $this->today->toDateString())
            ->where('check_out_date', '>=', $this->today->toDateString())
            ->whereNotNull('room_id')
            ->get()
            ->groupBy('room_id');
    }

    /**
     * @param  Collection<int, Collection<int, Reservation>>  $stays
     * @return array<string, mixed>
     */
    private function card(Room $room, Collection $stays): array
    {
        $roomStays = $stays->get($room->id, collect());

        $inHouse = $roomStays->first(static fn (Reservation $r): bool => $r->status === 'in_house');
        $arrival = $roomStays->first(
            fn (Reservation $r): bool => $r->status === 'confirmed'
                && $r->check_in_date->toDateString() === $this->today->toDateString()
        );

        $departsToday = $inHouse !== null
            && $inHouse->check_out_date->toDateString() <= $this->today->toDateString();

        $stay = $inHouse ?? $arrival;

        return [
            'id' => $room->id,
            'number' => $room->number,
            'floor' => $room->floor,
            'roomTypeId' => $room->room_type_id,
            'roomType' => $room->roomType?->name,
            'status' => $room->status,
            'housekeeping' => $room->housekeeping_status,
            'guest' => $stay?->guest?->name,
            'code' => $stay?->code,
            'checkOut' => $inHouse?->check_out_date?->toDateString(),
            // What the board may do with this room right now.
            'canCheckIn' => $arrival !== null && $inHouse === null && $room->status !== 'out_of_order',
            'canCheckOut' => $departsToday,
            'isArrival' => $arrival !== null,
            'isDeparture' => $departsToday,
            'isStayOver' => $inHouse !== null && ! $departsToday,
        ];
    }

    /**
     * Filter counts. The buckets overlap on purpose: a dirty vacant room is
     * both vacant and dirty, and the desk filters on either.
     *
     * @param  array<int, array<string, mixed>>  $cards
     * @return array<string, int>
     */
    private function counts(array $cards): array
    {
        $collection = collect($cards);

        return [
            'all' => $collection->count(),
            'vacant' => $collection->whereIn('status', ['vacant_clean', 'vacant_dirty'])->count(),
            'occupied' => $collection->where('status', 'occupied')->count(),
            'stay_over' => $collection->where('isStayOver', true)->count(),
            'arrivals' => $collection->where('isArrival', true)->count(),
            'departures' => $collection->where('isDeparture', true)->count(),
            'dirty' => $collection->where('housekeeping', 'dirty')->count(),
            'out_of_order' => $collection->where('status', 'out_of_order')->count(),
        ];
    }
}
