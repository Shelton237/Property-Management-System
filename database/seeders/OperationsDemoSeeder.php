<?php

declare(strict_types=1);

namespace Database\Seeders;

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
use Carbon\CarbonImmutable;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Fills the demo hotel with a month of plausible operations, so the dashboard
 * shows a working property instead of fourteen empty widgets.
 *
 * Amounts are whole francs: XAF has no minor unit.
 *
 * The generator is seeded, so running it twice produces the same hotel.
 */
class OperationsDemoSeeder extends Seeder
{
    private const HISTORY_DAYS = 35;

    private const HORIZON_DAYS = 14;

    public function run(): void
    {
        mt_srand(20261123);

        $hotel = Hotel::where('slug', 'hotel-demo-yaounde')->firstOrFail();

        $this->reset($hotel);

        $types = $this->createRoomTypes($hotel);
        $rooms = $this->createRooms($hotel, $types);
        $guests = $this->createGuests($hotel);

        $this->createStays($hotel, $rooms, $guests);
        $this->applyRoomStates($hotel);
        $this->createActivities($hotel);

        mt_srand();
    }

    private function reset(Hotel $hotel): void
    {
        DB::table('activities')->where('hotel_id', $hotel->id)->delete();
        DB::table('payments')->where('hotel_id', $hotel->id)->delete();
        DB::table('folio_lines')->where('hotel_id', $hotel->id)->delete();
        DB::table('folios')->where('hotel_id', $hotel->id)->delete();
        DB::table('reservations')->where('hotel_id', $hotel->id)->delete();
        DB::table('guests')->where('hotel_id', $hotel->id)->delete();
        DB::table('rooms')->where('hotel_id', $hotel->id)->delete();
        DB::table('room_types')->where('hotel_id', $hotel->id)->delete();
    }

    /** @return array<string, RoomType> */
    private function createRoomTypes(Hotel $hotel): array
    {
        $definitions = [
            ['Standard', 'STD', 35000],
            ['Deluxe', 'DLX', 55000],
            ['Suite', 'STE', 90000],
        ];

        $types = [];

        foreach ($definitions as [$name, $code, $rate]) {
            $types[$code] = RoomType::create([
                'hotel_id' => $hotel->id,
                'name' => $name,
                'code' => $code,
                'base_rate' => $rate,
            ]);
        }

        return $types;
    }

    /**
     * @param  array<string, RoomType>  $types
     * @return array<int, Room>
     */
    private function createRooms(Hotel $hotel, array $types): array
    {
        $plan = [
            1 => ['STD', 'STD', 'STD', 'STD', 'STD', 'DLX', 'DLX', 'DLX'],
            2 => ['STD', 'STD', 'STD', 'DLX', 'DLX', 'DLX', 'DLX', 'STE'],
            3 => ['DLX', 'DLX', 'DLX', 'STE', 'STE', 'STE', 'STE', 'STE', 'STE'],
        ];

        $rooms = [];

        foreach ($plan as $floor => $codes) {
            foreach ($codes as $index => $code) {
                $rooms[] = Room::create([
                    'hotel_id' => $hotel->id,
                    'room_type_id' => $types[$code]->id,
                    'number' => sprintf('%d%02d', $floor, $index + 1),
                    'floor' => (string) $floor,
                    'status' => 'vacant_clean',
                    'housekeeping_status' => 'clean',
                    'is_sellable' => true,
                ]);
            }
        }

        // One room out of service, as any real property has.
        $rooms[6]->update(['status' => 'out_of_order', 'is_sellable' => false]);

        return $rooms;
    }

    /** @return array<int, Guest> */
    private function createGuests(Hotel $hotel): array
    {
        $first = ['Aurélie', 'Jean-Pierre', 'Fadimatou', 'Serge', 'Nadège', 'Boubacar', 'Christelle',
            'Emmanuel', 'Mireille', 'Olivier', 'Sandrine', 'Thierry', 'Brigitte', 'Hervé',
            'Patricia', 'Alain', 'Clarisse', 'Blaise', 'Léonie', 'Armand'];
        $last = ['Mbarga', 'Etoa', 'Ngo Bell', 'Fotso', 'Nkolo', 'Abena', 'Tchoumi', 'Essomba',
            'Biya', 'Mendomo', 'Kamga', 'Owona', 'Njoya', 'Simo', 'Eyenga'];

        // Mostly domestic, with the regional and European mix a Yaoundé hotel sees.
        $countries = array_merge(
            array_fill(0, 24, 'CM'),
            ['FR', 'FR', 'CI', 'SN', 'GA', 'TD', 'CD', 'BE', 'CN', 'US'],
        );

        $guests = [];

        for ($i = 0; $i < 46; $i++) {
            $guests[] = Guest::create([
                'hotel_id' => $hotel->id,
                'name' => $first[$i % count($first)].' '.$last[intdiv($i, 3) % count($last)],
                'country' => $countries[$i % count($countries)],
                'phone' => '+2376'.mt_rand(10000000, 99999999),
            ]);
        }

        return $guests;
    }

    /**
     * @param  array<int, Room>  $rooms
     * @param  array<int, Guest>  $guests
     */
    private function createStays(Hotel $hotel, array $rooms, array $guests): void
    {
        $today = CarbonImmutable::today();
        $sellable = array_values(array_filter($rooms, static fn (Room $room): bool => $room->is_sellable));

        $sequence = 1;
        $guestIndex = 0;

        // A room is free again only on its previous departure date: without
        // this, the generator double books rooms and the occupancy figures
        // contradict the room list.
        $freeFrom = [];

        foreach ($sellable as $room) {
            $freeFrom[$room->id] = $today->subDays(self::HISTORY_DAYS + 1);
        }

        for ($offset = -self::HISTORY_DAYS; $offset <= self::HORIZON_DAYS; $offset++) {
            $date = $today->addDays($offset);

            // Occupancy climbs over the period, then thins out for future dates
            // which are not sold yet.
            $arrivals = match (true) {
                $offset < -20 => mt_rand(1, 2),
                $offset < -8 => mt_rand(2, 4),
                $offset <= 0 => mt_rand(3, 5),
                default => mt_rand(0, 2),
            };

            for ($i = 0; $i < $arrivals; $i++) {
                $available = array_values(array_filter(
                    $sellable,
                    static fn (Room $room): bool => $freeFrom[$room->id]->lessThanOrEqualTo($date),
                ));

                if ($available === []) {
                    continue;
                }

                $room = $available[array_rand($available)];
                $guest = $guests[$guestIndex % count($guests)];
                $guestIndex++;

                $nights = mt_rand(1, 4);
                $checkOut = $date->addDays($nights);
                $freeFrom[$room->id] = $checkOut;
                $rate = $room->roomType->base_rate + (mt_rand(-3, 4) * 1000);

                $status = match (true) {
                    // Finished before today.
                    $checkOut->lessThan($today) => mt_rand(1, 14) === 1 ? 'no_show' : 'departed',
                    // Leaving today: mostly still in the room, waiting at the desk.
                    $checkOut->equalTo($today) => mt_rand(1, 3) === 1 ? 'departed' : 'in_house',
                    // Arriving today: most are still expected, some already in.
                    $date->equalTo($today) => mt_rand(1, 10) <= 6 ? 'confirmed' : 'in_house',
                    $date->greaterThan($today) => mt_rand(1, 12) === 1 ? 'cancelled' : 'confirmed',
                    default => 'in_house',
                };

                $reservation = Reservation::create([
                    'hotel_id' => $hotel->id,
                    'guest_id' => $guest->id,
                    'room_id' => $room->id,
                    'code' => sprintf('BK-%06d', $sequence++),
                    'source' => $this->pickSource(),
                    'status' => $status,
                    'check_in_date' => $date,
                    'check_out_date' => $checkOut,
                    'room_rate' => $rate,
                ]);

                $this->createFolio($hotel, $reservation, $today);
            }
        }
    }

    private function pickSource(): string
    {
        $draw = mt_rand(1, 100);

        return match (true) {
            $draw <= 38 => 'walk_in',
            $draw <= 76 => 'direct_phone',
            $draw <= 92 => 'corporate',
            default => 'ota',
        };
    }

    private function createFolio(Hotel $hotel, Reservation $reservation, CarbonImmutable $today): void
    {
        // Nothing is charged before the guest arrives: a confirmed booking has
        // no folio yet, the front desk opens it at check-in.
        if (in_array($reservation->status, ['cancelled', 'confirmed'], true)) {
            return;
        }

        $closed = $reservation->status === 'departed';

        $folio = Folio::create([
            'hotel_id' => $hotel->id,
            'reservation_id' => $reservation->id,
            'code' => str_replace('BK-', 'FO-', $reservation->code),
            'status' => $closed ? 'closed' : 'open',
            'closed_at' => $closed ? $reservation->check_out_date : null,
        ]);

        $total = 0;

        if ($reservation->status === 'no_show') {
            // A no-show is still billed: it feeds the cancellation fee line.
            $amount = (int) round($reservation->room_rate * 0.5);
            $this->line($folio, 'cancellation_fees', 'Frais de non-présentation', $amount, $reservation->check_in_date);
            $total += $amount;
        } else {
            $night = CarbonImmutable::parse($reservation->check_in_date);
            $last = CarbonImmutable::parse($reservation->check_out_date);

            while ($night->lessThan($last) && $night->lessThanOrEqualTo($today)) {
                $this->line($folio, 'rooms', 'Nuitée', $reservation->room_rate, $night);
                $total += $reservation->room_rate;

                if (mt_rand(1, 100) <= 55) {
                    $amount = mt_rand(3, 24) * 1000;
                    $this->line($folio, 'restaurant', 'Restaurant', $amount, $night);
                    $total += $amount;
                }

                if (mt_rand(1, 100) <= 18) {
                    $amount = mt_rand(2, 9) * 1000;
                    $this->line($folio, 'room_service', 'Service en chambre', $amount, $night);
                    $total += $amount;
                }

                if (mt_rand(1, 100) <= 12) {
                    $amount = mt_rand(1, 6) * 1000;
                    $this->line($folio, 'laundry', 'Blanchisserie', $amount, $night);
                    $total += $amount;
                }

                $night = $night->addDay();
            }

            // Occasional goodwill gesture: a negative line, which the department
            // breakdown has to be able to show.
            if (mt_rand(1, 100) <= 6 && $total > 0) {
                $amount = -1 * mt_rand(2, 8) * 1000;
                $this->line($folio, 'front_office', 'Geste commercial', $amount, $reservation->check_in_date);
                $total += $amount;
            }
        }

        if ($total <= 0) {
            return;
        }

        // Closed folios are settled; open ones are often partly paid, which is
        // what the outstanding balances widget reports.
        $paid = $closed ? $total : (mt_rand(1, 100) <= 45 ? (int) round($total * (mt_rand(20, 70) / 100)) : 0);

        if ($paid > 0) {
            Payment::create([
                'hotel_id' => $hotel->id,
                'folio_id' => $folio->id,
                'method' => $this->pickMethod(),
                'amount' => $paid,
                'received_on' => $closed ? $reservation->check_out_date : $today,
            ]);
        }
    }

    private function pickMethod(): string
    {
        $draw = mt_rand(1, 100);

        return match (true) {
            $draw <= 52 => 'cash',
            $draw <= 70 => 'card',
            $draw <= 88 => 'orange_money',
            default => 'mtn_momo',
        };
    }

    private function line(Folio $folio, string $department, string $description, int $amount, mixed $date): void
    {
        FolioLine::create([
            'hotel_id' => $folio->hotel_id,
            'folio_id' => $folio->id,
            'department' => $department,
            'description' => $description,
            'amount' => $amount,
            'posted_on' => $date,
        ]);
    }

    /**
     * Room occupancy and cleaning state must agree with today's reservations.
     */
    private function applyRoomStates(Hotel $hotel): void
    {
        $today = CarbonImmutable::today();

        $occupied = Reservation::where('hotel_id', $hotel->id)
            ->where('status', 'in_house')
            ->pluck('room_id')
            ->filter()
            ->unique();

        Room::where('hotel_id', $hotel->id)
            ->whereIn('id', $occupied)
            ->update(['status' => 'occupied']);

        $arriving = Reservation::where('hotel_id', $hotel->id)
            ->where('status', 'confirmed')
            ->whereDate('check_in_date', $today)
            ->pluck('room_id')
            ->filter()
            ->unique()
            ->diff($occupied);

        Room::where('hotel_id', $hotel->id)
            ->whereIn('id', $arriving)
            ->update(['status' => 'reserved']);

        $free = Room::where('hotel_id', $hotel->id)
            ->where('status', 'vacant_clean')
            ->get();

        foreach ($free as $index => $room) {
            if ($index % 3 === 0) {
                $room->update(['status' => 'vacant_dirty', 'housekeeping_status' => 'dirty']);
            }
        }

        // Cleaning progress across the property.
        $all = Room::where('hotel_id', $hotel->id)->get();

        foreach ($all as $index => $room) {
            if ($room->status === 'out_of_order') {
                continue;
            }

            $room->update([
                'housekeeping_status' => match ($index % 7) {
                    0, 1, 2, 3 => 'clean',
                    4 => 'in_progress',
                    5 => 'to_inspect',
                    default => 'dirty',
                },
            ]);
        }
    }

    private function createActivities(Hotel $hotel): void
    {
        $user = User::where('hotel_id', $hotel->id)->where('role', 'owner')->first()
            ?? User::where('hotel_id', $hotel->id)->first();

        $recent = Reservation::where('hotel_id', $hotel->id)
            ->whereIn('status', ['in_house', 'departed'])
            ->latest('id')
            ->take(10)
            ->get();

        $kinds = ['payment', 'folio', 'checkin', 'checkout', 'housekeeping', 'room'];

        foreach ($recent as $index => $reservation) {
            $kind = $kinds[$index % count($kinds)];

            Activity::create([
                'hotel_id' => $hotel->id,
                'user_id' => $user?->id,
                'kind' => $kind,
                'description' => match ($kind) {
                    'payment' => 'Encaissement enregistré',
                    'folio' => 'Note clôturée et facture émise',
                    'checkin' => 'Arrivée enregistrée',
                    'checkout' => 'Départ enregistré',
                    'housekeeping' => 'Chambre passée en propre',
                    default => 'Changement de chambre',
                },
                'reference' => $reservation->code,
                'created_at' => now()->subMinutes(12 * ($index + 1)),
                'updated_at' => now()->subMinutes(12 * ($index + 1)),
            ]);
        }
    }
}
