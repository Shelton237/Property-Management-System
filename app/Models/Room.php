<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Room extends Model
{
    /**
     * Occupancy state, in the order the dashboard lists it.
     *
     * @var list<string>
     */
    public const STATUSES = [
        'vacant_clean',
        'vacant_dirty',
        'occupied',
        'arriving',
        'departing',
        'reserved',
        'out_of_order',
    ];

    /**
     * Cleaning state, tracked separately: an occupied room can be due service.
     *
     * @var list<string>
     */
    public const HOUSEKEEPING_STATUSES = ['clean', 'in_progress', 'to_inspect', 'dirty'];

    /** @var list<string> */
    protected $fillable = [
        'hotel_id', 'room_type_id', 'number', 'floor',
        'status', 'housekeeping_status', 'is_sellable',
    ];

    /** @return array<string, string> */
    protected function casts(): array
    {
        return ['is_sellable' => 'boolean'];
    }

    /** @return BelongsTo<Hotel, $this> */
    public function hotel(): BelongsTo
    {
        return $this->belongsTo(Hotel::class);
    }

    /** @return BelongsTo<RoomType, $this> */
    public function roomType(): BelongsTo
    {
        return $this->belongsTo(RoomType::class);
    }
}
