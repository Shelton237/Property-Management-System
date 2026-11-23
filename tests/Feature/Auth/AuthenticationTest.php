<?php

declare(strict_types=1);

use App\Http\Requests\Auth\LoginRequest;
use App\Models\Hotel;
use App\Models\User;
use Illuminate\Support\Facades\RateLimiter;

beforeEach(function () {
    // The login throttle is keyed on email and IP, and survives between tests
    // because it lives in the cache rather than the database.
    RateLimiter::clear('test@example.com|127.0.0.1');
});

it('shows the login page', function () {
    $this->get(route('login'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page->component('auth/Login')
            ->where('canResetPassword', true));
});

it('signs a user in and redirects to the dashboard', function () {
    $user = User::factory()->create(['email' => 'test@example.com']);

    $this->post(route('login'), [
        'email' => 'test@example.com',
        'password' => 'password',
    ])->assertRedirect(route('dashboard', absolute: false));

    $this->assertAuthenticatedAs($user);
});

it('refuses a wrong password', function () {
    User::factory()->create(['email' => 'test@example.com']);

    $this->post(route('login'), [
        'email' => 'test@example.com',
        'password' => 'wrong-password',
    ])->assertSessionHasErrors(LoginRequest::REFUSAL_KEY);

    $this->assertGuest();
});

it('refuses a deactivated account with its own message', function () {
    User::factory()->inactive()->create(['email' => 'test@example.com']);

    $this->post(route('login'), [
        'email' => 'test@example.com',
        'password' => 'password',
    ])->assertSessionHasErrors([
        LoginRequest::REFUSAL_KEY => trans('auth.inactive'),
    ]);

    $this->assertGuest();
});

it('refuses a user whose hotel is suspended with its own message', function () {
    $hotel = Hotel::factory()->suspended()->create();
    User::factory()->for($hotel)->create(['email' => 'test@example.com']);

    $this->post(route('login'), [
        'email' => 'test@example.com',
        'password' => 'password',
    ])->assertSessionHasErrors([
        LoginRequest::REFUSAL_KEY => trans('auth.hotel_suspended'),
    ]);

    $this->assertGuest();
});

it('rate limits login after five failed attempts', function () {
    User::factory()->create(['email' => 'test@example.com']);

    foreach (range(1, 5) as $attempt) {
        $this->post(route('login'), [
            'email' => 'test@example.com',
            'password' => 'wrong-password',
        ])->assertSessionHasErrors([
            LoginRequest::REFUSAL_KEY => trans('auth.failed'),
        ]);
    }

    // The sixth attempt is refused before the credentials are even checked, and
    // the message tells the user how long to wait.
    $response = $this->post(route('login'), [
        'email' => 'test@example.com',
        'password' => 'password',
    ]);

    $response->assertSessionHasErrorsIn('default', [LoginRequest::REFUSAL_KEY]);

    expect(session('errors')->get(LoginRequest::REFUSAL_KEY)[0])
        ->toContain('secondes');

    $this->assertGuest();
});

it('records the time and address of a successful login', function () {
    $user = User::factory()->create([
        'email' => 'test@example.com',
        'last_login_at' => null,
        'last_login_ip' => null,
    ]);

    $this->post(route('login'), [
        'email' => 'test@example.com',
        'password' => 'password',
    ]);

    $user->refresh();

    expect($user->last_login_at)->not->toBeNull()
        ->and($user->last_login_ip)->toBe('127.0.0.1');
});

it('keeps the user signed in when remember me is checked', function () {
    $user = User::factory()->create(['email' => 'test@example.com']);

    $this->post(route('login'), [
        'email' => 'test@example.com',
        'password' => 'password',
        'remember' => true,
    ]);

    $this->assertAuthenticatedAs($user);

    expect($user->fresh()->remember_token)->not->toBeNull();
});

it('signs the user out', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->post(route('logout'))
        ->assertRedirect(route('login'));

    $this->assertGuest();
});

it('redirects a guest to the login page', function () {
    $this->get(route('dashboard'))->assertRedirect(route('login'));
});

it('signs out a user whose hotel became suspended during the session', function () {
    $hotel = Hotel::factory()->create();
    $user = User::factory()->for($hotel)->create();

    $this->actingAs($user)->get(route('dashboard'))->assertOk();

    $hotel->update(['status' => 'suspended']);

    // A real request reloads the user from the database, so the test must not
    // keep the instance whose hotel relation was loaded before the change.
    $this->actingAs($user->fresh());

    $this->get(route('dashboard'))
        ->assertRedirect(route('login'))
        ->assertSessionHasErrors([
            LoginRequest::REFUSAL_KEY => trans('auth.hotel_suspended'),
        ]);

    $this->assertGuest();
});
