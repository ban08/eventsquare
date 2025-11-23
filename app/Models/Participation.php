<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Participation model represents a row in the pivot-like `participation` table
 * (user participates in an event). The table uses a composite primary key
 * (id_event, id_user). Laravel does not natively support composite primary
 * keys, so we override the save key logic for update operations.
 */
class Participation extends Model
{
    protected $table = 'participation';

    // Composite key -> disable auto incrementing and timestamps.
    public $incrementing = false;
    public $timestamps = false;

    // We will manually handle the composite key on save.
    protected $primaryKey = null; // hint: no single primary key column

    protected $fillable = [
        'id_event',
        'id_user',
        'joined_at',
        'left_at',
    ];

    protected function casts(): array
    {
        return [
            'joined_at' => 'datetime',
            'left_at'   => 'datetime',
        ];
    }

    /**
     * Ensure updates/deletes target the correct composite key row.
     */
    protected function setKeysForSaveQuery($query)
    {
        $query->where('id_event', $this->getAttribute('id_event'))
              ->where('id_user', $this->getAttribute('id_user'));
        return $query;
    }

    /** Event this participation belongs to */
    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class, 'id_event');
    }

    /** User who participates */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'id_user');
    }
}
