<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * App\Models\Invitation
 *
 * Represents an invitation to an event (M03).
 *
 * @property int $id_invitation
 * @property int $id_event
 * @property int $id_invitee
 * @property string $status 'pending', 'accepted', 'declined', 'canceled', 'expired'
 * @property \Illuminate\Support\Carbon $sent_at
 * @property \Illuminate\Support\Carbon|null $responded_at
 *
 * @property-read \App\Models\Event $event
 * @property-read \App\Models\User $invitee
 */
class Invitation extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'invitation';

    /**
     * The primary key associated with the table.
     *
     * @var string
     */
    protected $primaryKey = 'id_invitation';

    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = false; // Using custom sent_at/responded_at columns

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'id_event',
        'id_invitee',
        'status',
        'sent_at',
        'responded_at',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'sent_at' => 'datetime',
            'responded_at' => 'datetime',
        ];
    }

    /**
     * Get the event associated with the invitation.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class, 'id_event');
    }

    /**
     * Get the user invited.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function invitee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'id_invitee');
    }

    /**
     * Scope a query to only include pending invitations.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }
}
