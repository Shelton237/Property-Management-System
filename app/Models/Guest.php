<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Guest extends Model
{
    /** @var list<string> */
    protected $fillable = ['hotel_id', 'name', 'country', 'email', 'phone'];

    /** @return BelongsTo<Hotel, $this> */
    public function hotel(): BelongsTo
    {
        return $this->belongsTo(Hotel::class);
    }

    /** @return HasMany<Reservation, $this> */
    public function reservations(): HasMany
    {
        return $this->hasMany(Reservation::class);
    }

    /**
     * Initials shown in the arrivals and departures lists.
     */
    public function initials(): string
    {
        $parts = array_filter(explode(' ', $this->name));

        return mb_strtoupper(implode('', array_map(
            static fn (string $part): string => mb_substr($part, 0, 1),
            array_slice($parts, 0, 2),
        )));
    }
}
