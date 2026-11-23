<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\HotelStatus;
use App\Enums\UserRole;
use App\Models\Hotel;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

/**
 * Demo data: one hotel with an owner, a receptionist and a deactivated account.
 *
 * These accounts share a well known password, so they are only meant for a
 * demonstration environment. Remove them before opening the application to
 * real customers.
 */
class DemoSeeder extends Seeder
{
    public function run(): void
    {
        $hotel = Hotel::updateOrCreate(
            ['slug' => 'hotel-demo-yaounde'],
            [
                'name' => 'Hôtel Démo Yaoundé',
                'city' => 'Yaoundé',
                'country' => 'CM',
                'currency' => 'XAF',
                'timezone' => 'Africa/Douala',
                'locale' => 'fr',
                'status' => HotelStatus::Trial,
                'trial_ends_at' => now()->addDays(14),
            ]
        );

        $accounts = [
            [
                'email' => 'owner@demo.test',
                'name' => 'Aurélie Mbarga',
                'role' => UserRole::Owner,
                'is_active' => true,
            ],
            [
                'email' => 'reception@demo.test',
                'name' => 'Jean-Pierre Etoa',
                'role' => UserRole::Receptionist,
                'is_active' => true,
            ],
            [
                'email' => 'inactif@demo.test',
                'name' => 'Compte Désactivé',
                'role' => UserRole::Receptionist,
                'is_active' => false,
            ],
        ];

        foreach ($accounts as $account) {
            User::updateOrCreate(
                ['email' => $account['email']],
                [
                    'hotel_id' => $hotel->id,
                    'name' => $account['name'],
                    'password' => Hash::make('password'),
                    'role' => $account['role'],
                    'is_active' => $account['is_active'],
                    'locale' => 'fr',
                    'email_verified_at' => now(),
                ]
            );
        }
    }
}
