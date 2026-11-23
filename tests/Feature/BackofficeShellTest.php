<?php

declare(strict_types=1);

use App\Models\Hotel;
use App\Models\User;

it('shares the property, the business date and the navigation labels', function () {
    $hotel = Hotel::factory()->create([
        'name' => 'Hôtel Démo Yaoundé',
        'city' => 'Yaoundé',
        'timezone' => 'Africa/Douala',
    ]);

    $user = User::factory()->for($hotel)->create();

    $this->actingAs($user)
        ->get(route('dashboard'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('Dashboard')
            ->where('hotel.name', 'Hôtel Démo Yaoundé')
            ->where('hotel.city', 'Yaoundé')
            ->has('businessDate.label')
            ->has('businessDate.iso')
            ->has('translations.navigation.items.front_desk')
            ->has('translations.navigation.sections.operations'));
});

it('shares no property for an account not attached to one', function () {
    $user = User::factory()->superAdmin()->create();

    $this->actingAs($user)
        ->get(route('dashboard'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page->where('hotel', null));
});

it('dates the business day in the timezone of the property', function () {
    $hotel = Hotel::factory()->create(['timezone' => 'Pacific/Kiritimati']);
    $user = User::factory()->for($hotel)->create();

    $expected = now()->timezone('Pacific/Kiritimati')->toDateString();

    $this->actingAs($user)
        ->get(route('dashboard'))
        ->assertInertia(fn ($page) => $page->where('businessDate.iso', $expected));
});

it('translates every navigation entry in both languages', function () {
    $french = trans('navigation.items', [], 'fr');
    $english = trans('navigation.items', [], 'en');

    // The mockup lists 27 entries across the five sections.
    expect($french)->toHaveCount(27)
        ->and(array_keys($english))->toEqual(array_keys($french))
        ->and(array_filter($french, fn ($line) => $line === ''))->toBeEmpty();
});
