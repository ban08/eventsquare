<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * App\Models\Event
 *
 * Represents an event in the system (M02).
 *
 * @property int $id_event
 * @property string $title
 * @property string $description
 * @property string $visibility 'public', 'private'
 * @property string $status 'published', 'completed', 'canceled', 'draft', 'deleted'
 * @property int $capacity
 * @property \Illuminate\Support\Carbon $start_at
 * @property \Illuminate\Support\Carbon|null $end_at
 * @property string|null $venue
 * @property int $id_organizer
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $canceled_at
 *
 * @property-read \App\Models\User $organizer
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Participation[] $participations
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\User[] $participants
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Application[] $applications
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Invitation[] $invitations
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\EventReport[] $reports
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Tag[] $tags
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Poll[] $polls
 *
 * @property-read int $current_participants_count
 * @property-read bool $is_full
 * @property-read bool $is_upcoming
 * @property-read bool $is_past
 * @property-read bool $is_ongoing
 * @property-read string $effective_status
 * @property-read bool $is_editable
 * @property-read bool $is_cancelable
 * @property-read bool $can_hard_delete
 */
class Event extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'event';

    /**
     * The primary key associated with the table.
     *
     * @var string
     */
    protected $primaryKey = 'id_event';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'title',
        'description',
        'visibility',   
        'status',       
        'capacity',     
        'start_at',     
        'end_at',       
        'venue',        
        'id_organizer', 
    ];

    /**
     * Get the polls for the event.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function polls(): HasMany
    {
        return $this->hasMany(Poll::class, 'id_event');
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'start_at'   => 'datetime',
            'end_at'     => 'datetime',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
            'canceled_at'=> 'datetime',
        ];
    }

    /**
     * Get the organizer of the event (BR09).
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function organizer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'id_organizer');
    }

    /**
     * Get the participations for the event.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function participations(): HasMany
    {
        return $this->hasMany(Participation::class, 'id_event');
    }

    /**
     * Get the participants of the event.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function participants(): BelongsToMany
    {
        return $this->belongsToMany(
            User::class,       
            'participation',   
            'id_event',        
            'id_user'          
        )
        ->withPivot('joined_at', 'left_at')
        ->wherePivotNull('left_at')
        ->where('user.status', '!=', 'deleted');
    }

    /**
     * Get the applications for the event.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function applications(): HasMany
    {
        return $this->hasMany(Application::class, 'id_event');
    }

    /**
     * Get the invitations for the event.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function invitations(): HasMany
    {
        return $this->hasMany(Invitation::class, 'id_event');
    }

    /**
     * Get the reports for the event.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function reports(): HasMany
    {
        return $this->hasMany(EventReport::class, 'id_event');
    }

    /**
     * Get the number of current participants.
     *
     * @return int
     */
    public function getCurrentParticipantsCountAttribute(): int
    {
        return $this->participations()
            ->whereNull('left_at') // only people who haven't left
            ->whereHas('user', function ($query) {
                $query->where('status', '!=', 'deleted');
            })
            ->count();
    }

    /**
     * Check if the event is full (BR10).
     *
     * @return bool
     */
    public function getIsFullAttribute(): bool
    {
        return $this->current_participants_count >= $this->capacity;
    }

    /**
     * Check if the event is upcoming.
     *
     * @return bool
     */
    public function getIsUpcomingAttribute(): bool
    {
        return $this->start_at > now();
    }

    /**
     * Check if the event is in the past.
     *
     * @return bool
     */
    public function getIsPastAttribute(): bool
    {
        return $this->end_at
            ? $this->end_at < now()
            : $this->start_at < now();
    }

    /**
     * Check if the event is ongoing.
     *
     * @return bool
     */
    public function getIsOngoingAttribute(): bool
    {
        $now = now();

        return $this->start_at <= $now
            &&  $this->end_at >= $now;
    }

    /**
     * Scope a query to only include public events (BR02).
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopePublic($query)
    {
        return $query->where('visibility', 'public');
    }

    /**
     * Scope a query to only include published events (BR14).
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopePublished($query)
    {
        return $query->where('status', 'published');
    }

    /**
     * Scope a query to only include upcoming events.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeUpcoming($query)
    {
        return $query->where('start_at', '>', now());
    }

    /**
     * Get the route key for the model.
     *
     * @return string
     */
    public function getRouteKeyName()
    {
        return 'id_event';
    }

    /**
     * Get the tags for the event.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(
            Tag::class,      
            'event_tag',    
            'id_event',      
            'id_tag'         
        );
    }

    /**
     * Get the effective status of the event.
     *
     * @return string
     */
    public function getEffectiveStatusAttribute(): string
    {
        // For canceled events, keep the canceled status even if past
        if ($this->status === 'canceled') {
            return 'canceled';
        }
        // If event has ended, it's completed regardless of stored status
        if ($this->is_past) {
            return 'completed';
        }
        return $this->status;
    }

    /**
     * Check if the event can be edited (BR06).
     *
     * @return bool
     */
    public function getIsEditableAttribute(): bool
    {
        return !$this->is_past && $this->status !== 'canceled';
    }

    /**
     * Check if the event can be canceled.
     *
     * @return bool
     */
    public function getIsCancelableAttribute(): bool
    {
        return $this->status === 'published' && !$this->is_past;
    }

    /**
     * Check if the event can be hard deleted.
     *
     * @return bool
     */
    public function getCanHardDeleteAttribute(): bool
    {
        // Check if there are any active participations
        $hasActiveParticipations = $this->participations()
            ->whereNull('left_at')
            ->exists();

        // Event can be hard deleted only if there are no current attendees
        return !$hasActiveParticipations;
    }
}
