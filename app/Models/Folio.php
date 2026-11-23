<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Folio extends Model
{
    /** @var list<string> */
    protected $fillable = ['hotel_id', 'reservation_id', 'code', 'status', 'closed_at'];

    /** @return array<string, string> */
    protected function casts(): array
    {
        return ['closed_at' => 'datetime'];
    }

    /** @return BelongsTo<Reservation, $this> */
    public function reservation(): BelongsTo
    {
        return $this->belongsTo(Reservation::class);
    }

    /** @return HasMany<FolioLine, $this> */
    public function lines(): HasMany
    {
        return $this->hasMany(FolioLine::class);
    }

    /** @return HasMany<Payment, $this> */
    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }
}
