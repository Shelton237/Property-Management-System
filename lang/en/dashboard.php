<?php

declare(strict_types=1);

return [

    'page_title' => 'Dashboard',
    'welcome' => 'Welcome, :name',
    'no_hotel' => 'No hotel linked to this account',
    'logout' => 'Sign out',

    'greeting' => [
        'morning' => 'Good morning, :name',
        'afternoon' => 'Good afternoon, :name',
        'evening' => 'Good evening, :name',
    ],

    'month_to_date' => 'Month to date',
    'total' => 'Total',
    'day' => 'Day',
    'today' => 'Today',
    'footnote' => 'Figures cover the selected property and its business date.',

    'view' => [
        'chart' => 'Chart',
        'table' => 'Table',
    ],

    'kpi' => [
        'occupancy' => 'Occupancy',
        'adr' => 'Average daily rate',
        'revpar' => 'RevPAR',
        'revenue_today' => 'Revenue today',
        'movements' => 'Arrivals / departures',
        'guests_in_house' => 'Guests in house',
        'basis_yesterday' => 'vs yesterday',
        'basis_week' => 'vs 7-day average',
        'movements_caption' => ':in in · :out out',
        'rooms_caption' => ':count rooms',
    ],

    'revenue' => [
        'title' => 'Revenue, last 14 days',
        'subtitle' => ':total in total, net of taxes',
        'series' => [
            'rooms' => 'Rooms',
            'food_beverage' => 'Food & beverage',
            'other' => 'Other services',
        ],
    ],

    'room_status' => [
        'title' => 'Room status',
        'subtitle' => ':occupied of :total sellable rooms occupied',
        'vacant_clean' => 'Vacant clean',
        'vacant_dirty' => 'Vacant dirty',
        'occupied' => 'Occupied',
        'arriving' => 'Arriving today',
        'departing' => 'Departing today',
        'reserved' => 'Reserved',
        'out_of_order' => 'Out of order',
    ],

    'occupancy' => [
        'title' => 'Occupancy trend',
        'subtitle' => 'Over 30 days',
    ],

    'on_the_books' => [
        'title' => 'Next 14 days on the books',
        'subtitle' => 'Rooms already sold',
        'rooms_sold' => ':count rooms',
    ],

    'channels' => [
        'title' => 'Booking channels',
        'walk_in' => 'Walk-in',
        'direct_phone' => 'Direct · phone',
        'corporate' => 'Corporate',
        'ota' => 'Online travel agency',
    ],

    'departments' => [
        'title' => 'Revenue by department',
        'rooms' => 'Rooms',
        'cancellation_fees' => 'Cancellation & no-show fees',
        'restaurant' => 'Restaurant',
        'room_service' => 'Room service',
        'laundry' => 'Laundry',
        'front_office' => 'Front office',
    ],

    'payments' => [
        'title' => 'Payment methods',
        'cash' => 'Cash',
        'card' => 'Card',
        'orange_money' => 'Orange Money',
        'mtn_momo' => 'MTN MoMo',
    ],

    'movements' => [
        'title' => 'Arrivals & departures',
        'subtitle' => 'Expected movements for :date',
        'arrivals' => 'Arrivals',
        'departures' => 'Departures',
        'done' => ':count done',
        'done_badge' => 'Done',
        'room' => 'Room :number',
        'no_room' => 'No room yet',
        'empty_arrivals' => 'No arrivals today.',
        'empty_departures' => 'No departures today.',
    ],

    'activity' => [
        'title' => 'Recent activity',
        'empty' => 'No activity recorded.',
    ],

    'outstanding' => [
        'title' => 'Outstanding balances',
        'subtitle' => ':accounts open accounts · :total',
        'empty' => 'No outstanding balance.',
    ],

    'reservation_status' => [
        'tentative' => 'Tentative',
        'confirmed' => 'Confirmed',
        'in_house' => 'In house',
        'departed' => 'Departed',
        'cancelled' => 'Cancelled',
        'no_show' => 'No-show',
    ],

    'nationalities' => [
        'title' => 'Guest nationalities',
        'subtitle' => 'Share of :count guests this month',
        'unknown' => 'Not recorded',
    ],

    'housekeeping' => [
        'title' => 'Housekeeping',
        'subtitle' => ':clean of :total rooms clean · :done % done',
        'clean' => 'Clean',
        'in_progress' => 'In progress',
        'to_inspect' => 'To inspect',
        'dirty' => 'Dirty',
    ],

];
