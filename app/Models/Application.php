<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * App\Models\Application
 *
 * Represents a user's application to join an event (M03).
 *
 * @property int $id_application
 * @property int $id_event
 * @property int $id_user
 * @property string $status 'pending', 'approved', 'rejected', 'canceled'
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon|null $decided_at
 *
 * @property-read \App\Models\User $user
 * @property-read \App\Models\Event $event
 */
class Application extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'application';

    /**
     * The primary key associated with the table.
     *
     * @var string
     */
    protected $primaryKey = 'id_application';

    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'id_event',
        'id_user',
        'status',
        'created_at',
        'decided_at'
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'created_at' => 'datetime',
            'decided_at' => 'datetime',
        ];
    }

    /**
     * Get the user who applied.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'id_user');
    }   

    /**
     * Get the event applied to.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class, 'id_event');
    }
}