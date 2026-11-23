<?php

declare(strict_types=1);

return [

    'page_title' => 'Front desk',
    'title' => 'Front desk',

    'views' => [
        'today' => 'Today',
        'planner' => '7-day planner',
    ],

    'filters' => [
        'show' => 'Show filters',
        'hide' => 'Hide filters',
        'search' => 'Search room or guest name',
        'all_floors' => 'All floors',
        'floor' => 'Floor :floor',
        'all_types' => 'All room types',
        'all' => 'All',
        'vacant' => 'Vacant',
        'occupied' => 'Occupied',
        'stay_over' => 'Stay-over',
        'arrivals' => 'Arrivals',
        'departures' => 'Departures',
        'dirty' => 'Dirty',
        'out_of_order' => 'Out of order',
    ],

    'room' => [
        'number' => 'Room :number',
        'free' => 'Free',
        'until' => 'Until :date',
        'no_guest' => 'No guest',
    ],

    'status' => [
        'vacant_clean' => 'Vacant clean',
        'vacant_dirty' => 'Vacant dirty',
        'occupied' => 'Occupied',
        'out_of_order' => 'Out of order',
    ],

    'housekeeping' => [
        'clean' => 'Clean',
        'in_progress' => 'In progress',
        'to_inspect' => 'To inspect',
        'dirty' => 'Dirty',
    ],

    'actions' => [
        'check_in' => 'Check in',
        'check_out' => 'Check out',
        'mark_clean' => 'Mark clean',
        'mark_dirty' => 'Mark dirty',
        'out_of_order' => 'Take out of order',
        'back_in_service' => 'Put back in service',
        'more' => 'More actions',
    ],

    'flash' => [
        'checked_in' => 'Checked in room :room.',
        'checked_out' => 'Checked out room :room.',
        'housekeeping' => 'Cleaning state updated for room :room.',
        'out_of_order' => 'Room :room taken out of order.',
        'back_in_service' => 'Room :room back in service.',
    ],

    'errors' => [
        'no_arrival' => 'No arrival expected today on room :room.',
        'no_departure' => 'No departure to record on room :room.',
        'out_of_order' => 'Room :room is out of order.',
        'occupied' => 'Room :room is occupied: it cannot be taken out of order.',
        'unknown_state' => 'Unknown cleaning state.',
    ],

    'activity' => [
        'checkin' => 'Checked a guest in · room :room',
        'checkout' => 'Checked a guest out · room :room',
        'housekeeping_clean' => 'Room :room marked clean',
        'housekeeping_in_progress' => 'Cleaning in progress · room :room',
        'housekeeping_to_inspect' => 'Room :room awaiting inspection',
        'housekeeping_dirty' => 'Room :room marked dirty',
        'out_of_order' => 'Room :room taken out of order',
        'back_in_service' => 'Room :room back in service',
    ],

    'folio' => [
        'night' => 'Room night',
    ],

    'planner' => [
        'room' => 'Room',
        'free' => 'Free',
        'arrival' => 'Arrival',
    ],

    'empty' => [
        'title' => 'No room matches',
        'body' => 'Change the filters to see other rooms.',
    ],

    'no_rooms' => [
        'title' => 'No rooms configured',
        'body' => 'Create your room types and rooms to start working from the front desk.',
    ],

];
