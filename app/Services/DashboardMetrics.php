<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Activity;
use App\Models\FolioLine;
use App\Models\Hotel;
use App\Models\Payment;
use App\Models\Reservation;
use App\Models\Room;
use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Every figure the dashboard shows, computed from the operational tables.
 *
 * Amounts are whole francs. A property with no rooms yet returns zeros rather
 * than failing: the dashboard has to render on day one.
 */
class DashboardMetrics
{
    private const SPARK_DAYS = 14;

    private CarbonImmutable $today;

    /** @var array<string, array<string, int>> */
    private array $daily = [];

    private int $sellableRooms = 0;

    public function __construct(private readonly Hotel $hotel)
    {
        $this->today = CarbonImmutable::today($hotel->timezone ?: config('app.timezone'));
    }

    /**
     * @return array<string, mixed>
     */
    public function all(): array
    {
        $this->sellableRooms = Room::where('hotel_id', $this->hotel->id)->where('is_sellable', true)->count();
        $this->daily = $this->buildDailySeries();

        return [
            'currency' => $this->hotel->currency,
            'kpis' => $this->kpis(),
            'revenueSeries' => $this->revenueSeries(),
            'roomStatus' => $this->roomStatus(),
            'occupancyTrend' => $this->occupancyTrend(),
            'onTheBooks' => $this->onTheBooks(),
            'bookingChannels' => $this->bookingChannels(),
            'revenueByDepartment' => $this->revenueByDepartment(),
            'paymentMethods' => $this->paymentMethods(),
            'movements' => $this->movements(),
            'activity' => $this->activity(),
            'outstanding' => $this->outstanding(),
            'nationalities' => $this->nationalities(),
            'housekeeping' => $this->housekeeping(),
        ];
    }

    /**
     * One pass over the last fourteen days, reused by every sparkline.
     *
     * @return array<string, array<string, int>>
     */
    private function buildDailySeries(): array
    {
        $from = $this->today->subDays(self::SPARK_DAYS - 1);

        $days = [];
        for ($date = $from; $date->lessThanOrEqualTo($this->today); $date = $date->addDay()) {
            $key = $date->toDateString();
            $days[$key] = ['sold' => 0, 'roomRevenue' => 0, 'revenue' => 0, 'arrivals' => 0, 'departures' => 0];
        }

        $stays = Reservation::where('hotel_id', $this->hotel->id)
            ->whereIn('status', ['in_house', 'departed', 'confirmed'])
            ->where('check_in_date', '<=', $this->today->toDateString())
            ->where('check_out_date', '>', $from->toDateString())
            ->get(['check_in_date', 'check_out_date']);

        foreach ($stays as $stay) {
            $night = CarbonImmutable::parse($stay->check_in_date);
            $last = CarbonImmutable::parse($stay->check_out_date);

            while ($night->lessThan($last)) {
                $key = $night->toDateString();
                if (isset($days[$key])) {
                    $days[$key]['sold']++;
                }
                $night = $night->addDay();
            }
        }

        $lines = FolioLine::where('hotel_id', $this->hotel->id)
            ->whereBetween('posted_on', [$from->toDateString(), $this->today->toDateString()])
            ->selectRaw('posted_on, department, SUM(amount) AS total')
            ->groupBy('posted_on', 'department')
            ->get();

        foreach ($lines as $line) {
            $key = CarbonImmutable::parse($line->posted_on)->toDateString();
            if (! isset($days[$key])) {
                continue;
            }

            $days[$key]['revenue'] += (int) $line->total;

            if ($line->department === 'rooms') {
                $days[$key]['roomRevenue'] += (int) $line->total;
            }
        }

        foreach (['check_in_date' => 'arrivals', 'check_out_date' => 'departures'] as $column => $bucket) {
            $rows = Reservation::where('hotel_id', $this->hotel->id)
                ->whereNotIn('status', ['cancelled'])
                ->whereBetween($column, [$from->toDateString(), $this->today->toDateString()])
                ->selectRaw("{$column} AS day, COUNT(*) AS total")
                ->groupBy($column)
                ->get();

            foreach ($rows as $row) {
                $key = CarbonImmutable::parse($row->day)->toDateString();
                if (isset($days[$key])) {
                    $days[$key][$bucket] = (int) $row->total;
                }
            }
        }

        return $days;
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function kpis(): array
    {
        $series = array_values($this->daily);
        $todayRow = end($series) ?: ['sold' => 0, 'roomRevenue' => 0, 'revenue' => 0, 'arrivals' => 0, 'departures' => 0];
        $yesterdayRow = $series[count($series) - 2] ?? $todayRow;

        $occupancy = fn (array $row): float => $this->sellableRooms > 0 ? $row['sold'] / $this->sellableRooms * 100 : 0.0;
        $adr = fn (array $row): float => $row['sold'] > 0 ? $row['roomRevenue'] / $row['sold'] : 0.0;
        $revpar = fn (array $row): float => $this->sellableRooms > 0 ? $row['roomRevenue'] / $this->sellableRooms : 0.0;

        $averageOf = function (callable $metric) use ($series): float {
            $window = array_slice($series, -8, 7);
            $values = array_map($metric, $window);

            return $values === [] ? 0.0 : array_sum($values) / count($values);
        };

        $guestsInHouse = Reservation::where('hotel_id', $this->hotel->id)->where('status', 'in_house')->count();
        $roomsInHouse = Reservation::where('hotel_id', $this->hotel->id)->where('status', 'in_house')
            ->distinct()->count('room_id');

        return [
            [
                'key' => 'occupancy',
                'value' => round($occupancy($todayRow), 1),
                'format' => 'percent',
                'delta' => round($occupancy($todayRow) - $occupancy($yesterdayRow), 1),
                'deltaFormat' => 'points',
                'deltaBasis' => 'yesterday',
                'spark' => array_map(fn (array $row): float => round($occupancy($row), 1), $series),
            ],
            [
                'key' => 'adr',
                'value' => round($adr($todayRow)),
                'format' => 'money',
                'delta' => $this->percentChange($adr($todayRow), $averageOf($adr)),
                'deltaFormat' => 'percent',
                'deltaBasis' => 'week',
                'spark' => array_map(fn (array $row): float => round($adr($row)), $series),
            ],
            [
                'key' => 'revpar',
                'value' => round($revpar($todayRow)),
                'format' => 'money',
                'delta' => $this->percentChange($revpar($todayRow), $averageOf($revpar)),
                'deltaFormat' => 'percent',
                'deltaBasis' => 'week',
                'spark' => array_map(fn (array $row): float => round($revpar($row)), $series),
            ],
            [
                'key' => 'revenue_today',
                'value' => $todayRow['revenue'],
                'format' => 'money',
                'delta' => $this->percentChange((float) $todayRow['revenue'], (float) $yesterdayRow['revenue']),
                'deltaFormat' => 'percent',
                'deltaBasis' => 'yesterday',
                'spark' => array_map(static fn (array $row): float => (float) $row['revenue'], $series),
            ],
            [
                'key' => 'movements',
                'value' => $todayRow['arrivals'],
                'secondaryValue' => $todayRow['departures'],
                'format' => 'pair',
                'delta' => null,
                'spark' => array_map(static fn (array $row): float => (float) ($row['arrivals'] + $row['departures']), $series),
            ],
            [
                'key' => 'guests_in_house',
                'value' => $guestsInHouse,
                'secondaryValue' => $roomsInHouse,
                'format' => 'count',
                'delta' => null,
                'spark' => array_map(static fn (array $row): float => (float) $row['sold'], $series),
            ],
        ];
    }

    private function percentChange(float $value, float $basis): ?float
    {
        if ($basis <= 0.0) {
            return null;
        }

        return round(($value - $basis) / $basis * 100, 1);
    }

    /**
     * Stacked revenue: rooms, food and beverage, everything else.
     *
     * @return array<string, mixed>
     */
    private function revenueSeries(): array
    {
        $from = $this->today->subDays(self::SPARK_DAYS - 1);

        $rows = FolioLine::where('hotel_id', $this->hotel->id)
            ->whereBetween('posted_on', [$from->toDateString(), $this->today->toDateString()])
            ->selectRaw('posted_on, department, SUM(amount) AS total')
            ->groupBy('posted_on', 'department')
            ->get();

        $days = [];
        for ($date = $from; $date->lessThanOrEqualTo($this->today); $date = $date->addDay()) {
            $days[$date->toDateString()] = ['rooms' => 0, 'food_beverage' => 0, 'other' => 0];
        }

        foreach ($rows as $row) {
            $key = CarbonImmutable::parse($row->posted_on)->toDateString();
            if (! isset($days[$key])) {
                continue;
            }

            $bucket = match (true) {
                $row->department === 'rooms' => 'rooms',
                in_array($row->department, FolioLine::FOOD_AND_BEVERAGE, true) => 'food_beverage',
                default => 'other',
            };

            $days[$key][$bucket] += (int) $row->total;
        }

        return [
            'days' => array_map(
                static fn (string $day, array $values): array => ['day' => $day] + $values,
                array_keys($days),
                $days,
            ),
            'total' => array_sum(array_map(static fn (array $v): int => array_sum($v), $days)),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function roomStatus(): array
    {
        $counts = Room::where('hotel_id', $this->hotel->id)
            ->selectRaw('status, COUNT(*) AS total')
            ->groupBy('status')
            ->pluck('total', 'status');

        $arriving = Reservation::where('hotel_id', $this->hotel->id)
            ->where('status', 'confirmed')
            ->whereDate('check_in_date', $this->today->toDateString())
            ->count();

        $departing = Reservation::where('hotel_id', $this->hotel->id)
            ->where('status', 'in_house')
            ->whereDate('check_out_date', $this->today->toDateString())
            ->count();

        $total = max(1, $this->sellableRooms);

        $rows = [];
        foreach (Room::STATUSES as $status) {
            $count = match ($status) {
                'arriving' => $arriving,
                'departing' => $departing,
                default => (int) ($counts[$status] ?? 0),
            };

            $rows[] = [
                'key' => $status,
                'count' => $count,
                'share' => round($count / $total * 100),
            ];
        }

        return [
            'rows' => $rows,
            'occupied' => (int) ($counts['occupied'] ?? 0),
            'sellable' => $this->sellableRooms,
        ];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function occupancyTrend(int $days = 30): array
    {
        $from = $this->today->subDays($days - 1);

        $stays = Reservation::where('hotel_id', $this->hotel->id)
            ->whereIn('status', ['in_house', 'departed', 'confirmed'])
            ->where('check_in_date', '<=', $this->today->toDateString())
            ->where('check_out_date', '>', $from->toDateString())
            ->get(['check_in_date', 'check_out_date']);

        $counts = [];
        for ($date = $from; $date->lessThanOrEqualTo($this->today); $date = $date->addDay()) {
            $counts[$date->toDateString()] = 0;
        }

        foreach ($stays as $stay) {
            $night = CarbonImmutable::parse($stay->check_in_date);
            $last = CarbonImmutable::parse($stay->check_out_date);

            while ($night->lessThan($last)) {
                $key = $night->toDateString();
                if (isset($counts[$key])) {
                    $counts[$key]++;
                }
                $night = $night->addDay();
            }
        }

        $sellable = max(1, $this->sellableRooms);

        return array_map(
            static fn (string $day, int $sold): array => [
                'day' => $day,
                'value' => round($sold / $sellable * 100, 1),
            ],
            array_keys($counts),
            $counts,
        );
    }

    /**
     * Rooms already sold for each of the next fourteen days.
     *
     * @return array<int, array<string, mixed>>
     */
    private function onTheBooks(int $days = 14): array
    {
        $until = $this->today->addDays($days - 1);

        $stays = Reservation::where('hotel_id', $this->hotel->id)
            ->whereIn('status', ['in_house', 'confirmed'])
            ->where('check_in_date', '<=', $until->toDateString())
            ->where('check_out_date', '>', $this->today->toDateString())
            ->get(['check_in_date', 'check_out_date']);

        $counts = [];
        for ($date = $this->today; $date->lessThanOrEqualTo($until); $date = $date->addDay()) {
            $counts[$date->toDateString()] = 0;
        }

        foreach ($stays as $stay) {
            $night = CarbonImmutable::parse($stay->check_in_date);
            $last = CarbonImmutable::parse($stay->check_out_date);

            while ($night->lessThan($last)) {
                $key = $night->toDateString();
                if (isset($counts[$key])) {
                    $counts[$key]++;
                }
                $night = $night->addDay();
            }
        }

        $sellable = max(1, $this->sellableRooms);

        return array_map(
            static fn (string $day, int $sold): array => [
                'day' => $day,
                'sold' => $sold,
                'share' => round($sold / $sellable * 100),
            ],
            array_keys($counts),
            $counts,
        );
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function bookingChannels(): array
    {
        $rows = DB::table('folio_lines')
            ->join('folios', 'folios.id', '=', 'folio_lines.folio_id')
            ->join('reservations', 'reservations.id', '=', 'folios.reservation_id')
            ->where('folio_lines.hotel_id', $this->hotel->id)
            ->where('folio_lines.department', 'rooms')
            ->where('folio_lines.posted_on', '>=', $this->today->startOfMonth()->toDateString())
            ->selectRaw('reservations.source AS source, SUM(folio_lines.amount) AS total')
            ->groupBy('reservations.source')
            ->pluck('total', 'source');

        return $this->share(Reservation::SOURCES, $rows);
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function revenueByDepartment(): array
    {
        $rows = FolioLine::where('hotel_id', $this->hotel->id)
            ->where('posted_on', '>=', $this->today->startOfMonth()->toDateString())
            ->selectRaw('department, SUM(amount) AS total')
            ->groupBy('department')
            ->pluck('total', 'department');

        return $this->share(FolioLine::DEPARTMENTS, $rows);
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function paymentMethods(): array
    {
        $rows = Payment::where('hotel_id', $this->hotel->id)
            ->where('received_on', '>=', $this->today->startOfMonth()->toDateString())
            ->selectRaw('method, SUM(amount) AS total')
            ->groupBy('method')
            ->pluck('total', 'method');

        return $this->share(Payment::METHODS, $rows);
    }

    /**
     * Shares are taken on positive amounts only: a credit note must not make a
     * bar longer than the department that earned the money.
     *
     * @param  list<string>  $keys
     * @param  Collection<string, mixed>  $totals
     * @return array<int, array<string, mixed>>
     */
    private function share(array $keys, Collection $totals): array
    {
        $values = [];
        foreach ($keys as $key) {
            $values[$key] = (int) ($totals[$key] ?? 0);
        }

        $positive = array_sum(array_filter($values, static fn (int $v): bool => $v > 0));
        $base = max(1, $positive);

        $rows = [];
        foreach ($values as $key => $amount) {
            if ($amount === 0) {
                continue;
            }

            $rows[] = [
                'key' => $key,
                'amount' => $amount,
                'share' => (int) round(max(0, $amount) / $base * 100),
            ];
        }

        usort($rows, static fn (array $a, array $b): int => $b['amount'] <=> $a['amount']);

        return $rows;
    }

    /**
     * @return array<string, mixed>
     */
    private function movements(): array
    {
        // `done` marks a movement already handled: a guest who has checked in
        // is no longer an expected arrival, but still belongs to the day.
        $map = static fn (Reservation $reservation, string $doneStatus): array => [
            'code' => $reservation->code,
            'guest' => $reservation->guest?->name ?? '',
            'initials' => $reservation->guest?->initials() ?? '',
            'room' => $reservation->room?->number,
            'roomType' => $reservation->room?->roomType?->name,
            'done' => $reservation->status === $doneStatus,
        ];

        // The same statuses the arrivals and departures KPI counts, so the
        // figure on the card and the length of the list always agree.
        $arrivals = Reservation::with(['guest', 'room.roomType'])
            ->where('hotel_id', $this->hotel->id)
            ->whereIn('status', ['confirmed', 'in_house'])
            ->whereDate('check_in_date', $this->today->toDateString())
            ->orderBy('id')
            ->get()
            ->map(static fn (Reservation $reservation): array => $map($reservation, 'in_house'))
            ->all();

        $departures = Reservation::with(['guest', 'room.roomType'])
            ->where('hotel_id', $this->hotel->id)
            ->whereIn('status', ['in_house', 'departed'])
            ->whereDate('check_out_date', $this->today->toDateString())
            ->orderBy('id')
            ->get()
            ->map(static fn (Reservation $reservation): array => $map($reservation, 'departed'))
            ->all();

        return [
            'arrivals' => $arrivals,
            'departures' => $departures,
            'arrivalsDone' => count(array_filter($arrivals, static fn (array $r): bool => $r['done'])),
            'departuresDone' => count(array_filter($departures, static fn (array $r): bool => $r['done'])),
        ];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function activity(int $limit = 10): array
    {
        return Activity::with('user')
            ->where('hotel_id', $this->hotel->id)
            ->latest('created_at')
            ->take($limit)
            ->get()
            ->map(static fn (Activity $activity): array => [
                'kind' => $activity->kind,
                'description' => $activity->description,
                'reference' => $activity->reference,
                'author' => $activity->user?->name,
                'at' => $activity->created_at?->toIso8601String(),
            ])
            ->all();
    }

    /**
     * Open folios whose charges exceed what has been paid.
     *
     * @return array<string, mixed>
     */
    private function outstanding(int $limit = 6): array
    {
        $rows = DB::table('folios')
            ->join('reservations', 'reservations.id', '=', 'folios.reservation_id')
            ->join('guests', 'guests.id', '=', 'reservations.guest_id')
            ->leftJoin('folio_lines', 'folio_lines.folio_id', '=', 'folios.id')
            ->where('folios.hotel_id', $this->hotel->id)
            ->where('folios.status', 'open')
            ->groupBy('folios.id', 'reservations.code', 'reservations.status', 'guests.name')
            ->selectRaw('folios.id AS folio_id, reservations.code AS code, reservations.status AS status, guests.name AS guest, COALESCE(SUM(folio_lines.amount), 0) AS charges')
            ->get();

        $payments = DB::table('payments')
            ->where('hotel_id', $this->hotel->id)
            ->selectRaw('folio_id, SUM(amount) AS paid')
            ->groupBy('folio_id')
            ->pluck('paid', 'folio_id');

        $balances = [];
        foreach ($rows as $row) {
            $balance = (int) $row->charges - (int) ($payments[$row->folio_id] ?? 0);

            if ($balance <= 0) {
                continue;
            }

            $balances[] = [
                'code' => $row->code,
                'guest' => $row->guest,
                'status' => $row->status,
                'balance' => $balance,
            ];
        }

        usort($balances, static fn (array $a, array $b): int => $b['balance'] <=> $a['balance']);

        return [
            'accounts' => count($balances),
            'total' => array_sum(array_column($balances, 'balance')),
            'rows' => array_slice($balances, 0, $limit),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function nationalities(int $limit = 5): array
    {
        $rows = DB::table('reservations')
            ->join('guests', 'guests.id', '=', 'reservations.guest_id')
            ->where('reservations.hotel_id', $this->hotel->id)
            ->whereNotIn('reservations.status', ['cancelled'])
            ->where('reservations.check_in_date', '>=', $this->today->startOfMonth()->toDateString())
            ->selectRaw('guests.country AS country, COUNT(DISTINCT guests.id) AS total')
            ->groupBy('guests.country')
            ->orderByDesc('total')
            ->get();

        $guests = (int) $rows->sum('total');
        $base = max(1, $guests);

        return [
            'guests' => $guests,
            'rows' => $rows->take($limit)->map(static fn (object $row): array => [
                'country' => $row->country ?? 'ZZ',
                'count' => (int) $row->total,
                'share' => (int) round((int) $row->total / $base * 100),
            ])->all(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function housekeeping(): array
    {
        $counts = Room::where('hotel_id', $this->hotel->id)
            ->selectRaw('housekeeping_status, COUNT(*) AS total')
            ->groupBy('housekeeping_status')
            ->pluck('total', 'housekeeping_status');

        $rows = [];
        $total = 0;
        foreach (Room::HOUSEKEEPING_STATUSES as $status) {
            $count = (int) ($counts[$status] ?? 0);
            $total += $count;
            $rows[] = ['key' => $status, 'count' => $count];
        }

        $clean = (int) ($counts['clean'] ?? 0);

        return [
            'rows' => $rows,
            'total' => $total,
            'clean' => $clean,
            'done' => $total > 0 ? (int) round($clean / $total * 100) : 0,
        ];
    }
}
