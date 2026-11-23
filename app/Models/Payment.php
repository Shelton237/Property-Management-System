<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payment extends Model
{
    /**
     * Means of payment, adapted to the target markets: mobile money replaces
     * the bKash and Nagad of the reference product.
     *
     * @var list<string>
     */
    public const METHODS = ['cash', 'card', 'orange_money', 'mtn_momo'];

    /** @var list<string> */
    protected $fillable = ['folio_id', 'hotel_id', 'method', 'amount', 'received_on'];

    /** @return array<string, string> */
    protected function casts(): array
    {
        return ['received_on' => 'date'];
    }

    /** @return BelongsTo<Folio, $this> */
    public function folio(): BelongsTo
    {
        return $this->belongsTo(Folio::class);
    }
}
