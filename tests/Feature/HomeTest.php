<?php

declare(strict_types=1);

use App\Models\User;

it('sends a visitor to the login page', function () {
    $this->get('/')->assertRedirect(route('login'));
});

it('sends a signed in user to the dashboard', function () {
    $this->actingAs(User::factory()->create())
        ->get('/')
        ->assertRedirect(route('dashboard'));
});
