<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Activity extends Model
{
    /**
     * Kinds carry the dot colour in the activity feed.
     *
     * @var list<string>
     */
    public const KINDS = ['payment', 'folio', 'checkin', 'checkout', 'housekeeping', 'room'];

    /** @var list<string> */
    protected $fillable = ['hotel_id', 'user_id', 'kind', 'description', 'reference'];

    /** @return BelongsTo<User, $this> */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
