<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FolioLine extends Model
{
    /**
     * Revenue departments, in the order the dashboard lists them.
     *
     * @var list<string>
     */
    public const DEPARTMENTS = [
        'rooms',
        'cancellation_fees',
        'restaurant',
        'room_service',
        'laundry',
        'front_office',
    ];

    /**
     * Departments rolled up as "food and beverage" in the revenue chart.
     *
     * @var list<string>
     */
    public const FOOD_AND_BEVERAGE = ['restaurant', 'room_service'];

    /** @var list<string> */
    protected $fillable = ['folio_id', 'hotel_id', 'department', 'description', 'amount', 'posted_on'];

    /** @return array<string, string> */
    protected function casts(): array
    {
        return ['posted_on' => 'date'];
    }

    /** @return BelongsTo<Folio, $this> */
    public function folio(): BelongsTo
    {
        return $this->belongsTo(Folio::class);
    }
}
