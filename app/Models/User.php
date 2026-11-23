<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\UserRole;
use Illuminate\Contracts\Translation\HasLocalePreference;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable implements HasLocalePreference
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'hotel_id',
        'name',
        'email',
        'password',
        'role',
        'is_active',
        'locale',
    ];

    /**
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'role' => UserRole::class,
            'is_active' => 'boolean',
            'last_login_at' => 'datetime',
        ];
    }

    /**
     * @return BelongsTo<Hotel, $this>
     */
    public function hotel(): BelongsTo
    {
        return $this->belongsTo(Hotel::class);
    }

    /**
     * Whether the account itself may sign in, regardless of its hotel.
     */
    public function canSignIn(): bool
    {
        return $this->is_active;
    }

    /**
     * Whether the hotel the user belongs to currently blocks access.
     */
    public function hotelIsSuspended(): bool
    {
        return $this->hotel !== null && $this->hotel->isSuspended();
    }

    public function recordSuccessfulLogin(?string $ipAddress): void
    {
        $this->forceFill([
            'last_login_at' => now(),
            'last_login_ip' => $ipAddress,
        ])->save();
    }

    /**
     * Locale used for notifications such as the password reset mail.
     */
    public function preferredLocale(): string
    {
        return $this->locale ?: config('app.locale');
    }
}
