<?php

declare(strict_types=1);

use App\Models\User;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Support\Facades\Notification;

it('shows the password reset request page', function () {
    $this->get(route('password.request'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page->component('auth/ForgotPassword'));
});

it('sends a reset link by email', function () {
    Notification::fake();

    $user = User::factory()->create();

    $this->post(route('password.email'), ['email' => $user->email])
        ->assertSessionHasNoErrors();

    Notification::assertSentTo($user, ResetPassword::class);
});

it('shows the reset form reached from the emailed link', function () {
    Notification::fake();

    $user = User::factory()->create();

    $this->post(route('password.email'), ['email' => $user->email]);

    Notification::assertSentTo($user, ResetPassword::class, function (ResetPassword $notification) {
        $this->get(route('password.reset', $notification->token))
            ->assertOk()
            ->assertInertia(fn ($page) => $page->component('auth/ResetPassword'));

        return true;
    });
});

it('resets the password with a valid token', function () {
    Notification::fake();

    $user = User::factory()->create();

    $this->post(route('password.email'), ['email' => $user->email]);

    Notification::assertSentTo($user, ResetPassword::class, function (ResetPassword $notification) use ($user) {
        $this->post(route('password.store'), [
            'token' => $notification->token,
            'email' => $user->email,
            'password' => 'new-password',
            'password_confirmation' => 'new-password',
        ])->assertSessionHasNoErrors();

        return true;
    });

    // The new password is the one that works from now on.
    $this->post(route('login'), [
        'email' => $user->email,
        'password' => 'new-password',
    ])->assertRedirect(route('dashboard', absolute: false));

    $this->assertAuthenticatedAs($user->fresh());
});
