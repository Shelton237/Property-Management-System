<?php

declare(strict_types=1);

namespace App\Enums;

enum HotelStatus: string
{
    case Trial = 'trial';
    case Active = 'active';
    case Suspended = 'suspended';

    /**
     * @return array<int, string>
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    /**
     * Whether members of the hotel may sign in and use the application.
     */
    public function allowsAccess(): bool
    {
        return $this !== self::Suspended;
    }
}
