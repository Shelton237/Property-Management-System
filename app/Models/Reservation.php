<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Reservation extends Model
{
    /** @var list<string> */
    public const SOURCES = ['walk_in', 'direct_phone', 'corporate', 'ota'];

    /** @var list<string> */
    public const STATUSES = ['tentative', 'confirmed', 'in_house', 'departed', 'cancelled', 'no_show'];

    /** @var list<string> */
    protected $fillable = [
        'hotel_id', 'guest_id', 'room_id', 'code', 'source', 'status',
        'check_in_date', 'check_out_date', 'room_rate',
    ];

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'check_in_date' => 'date',
            'check_out_date' => 'date',
        ];
    }

    /** @return BelongsTo<Hotel, $this> */
    public function hotel(): BelongsTo
    {
        return $this->belongsTo(Hotel::class);
    }

    /** @return BelongsTo<Guest, $this> */
    public function guest(): BelongsTo
    {
        return $this->belongsTo(Guest::class);
    }

    /** @return BelongsTo<Room, $this> */
    public function room(): BelongsTo
    {
        return $this->belongsTo(Room::class);
    }

    /** @return HasOne<Folio, $this> */
    public function folio(): HasOne
    {
        return $this->hasOne(Folio::class);
    }

    public function nights(): int
    {
        return max(1, (int) $this->check_in_date->diffInDays($this->check_out_date));
    }
}
