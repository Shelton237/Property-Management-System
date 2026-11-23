<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\HotelStatus;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<\App\Models\Hotel>
 */
class HotelFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = 'Hotel '.fake()->city();

        return [
            'name' => $name,
            'slug' => Str::slug($name).'-'.fake()->unique()->numberBetween(1, 999999),
            'city' => fake()->city(),
            'country' => 'CM',
            'currency' => 'XAF',
            'timezone' => 'Africa/Douala',
            'locale' => 'fr',
            'status' => HotelStatus::Active,
            'trial_ends_at' => null,
        ];
    }

    public function trial(): static
    {
        return $this->state(fn () => [
            'status' => HotelStatus::Trial,
            'trial_ends_at' => now()->addDays(14),
        ]);
    }

    public function suspended(): static
    {
        return $this->state(fn () => ['status' => HotelStatus::Suspended]);
    }
}
