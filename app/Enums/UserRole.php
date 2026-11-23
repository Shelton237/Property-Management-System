<?php

declare(strict_types=1);

namespace App\Enums;

enum UserRole: string
{
    case SuperAdmin = 'super_admin';
    case Owner = 'owner';
    case Manager = 'manager';
    case Receptionist = 'receptionist';
    case Housekeeping = 'housekeeping';
    case Cashier = 'cashier';
    case Accountant = 'accountant';

    /**
     * @return array<int, string>
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    /**
     * A super admin belongs to the platform rather than to a single hotel.
     */
    public function isPlatformWide(): bool
    {
        return $this === self::SuperAdmin;
    }
}
