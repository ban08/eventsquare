<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * App\Models\Participation
 *
 * Represents a user's participation in an event (M03).
 * The table uses a composite primary key (id_event, id_user).
 *
 * @property int $id_event
 * @property int $id_user
 * @property \Illuminate\Support\Carbon $joined_at
 * @property \Illuminate\Support\Carbon|null $left_at
 *
 * @property-read \App\Models\Event $event
 * @property-read \App\Models\User $user
 */
class Participation extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'participation';

    /**
     * Indicates if the IDs are auto-incrementing.
     *
     * @var bool
     */
    public $incrementing = false;

    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = false;

    /**
     * The primary key associated with the table.
     *
     * @var string|null
     */
    protected $primaryKey = null; // hint: no single primary key column

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'id_event',
        'id_user',
        'joined_at',
        'left_at',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'joined_at' => 'datetime',
            'left_at'   => 'datetime',
        ];
    }

    /**
     * Set the keys for a save update query.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    protected function setKeysForSaveQuery($query)
    {
        $query->where('id_event', $this->getAttribute('id_event'))
              ->where('id_user', $this->getAttribute('id_user'));
        return $query;
    }

    /**
     * Get the event associated with the participation.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class, 'id_event');
    }

    /**
     * Get the user associated with the participation.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'id_user');
    }
}
