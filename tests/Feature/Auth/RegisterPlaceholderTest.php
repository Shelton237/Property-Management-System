<?php

declare(strict_types=1);

it('shows a coming soon page instead of sign up', function () {
    $this->get(route('register'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page->component('auth/ComingSoon'));
});
