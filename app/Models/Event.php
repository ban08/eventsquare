<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;


// The Event model represents one row in the "event" table in the database.
class Event extends Model
{
    use HasFactory;


    // Tell Laravel which database table this model uses.
    protected $table = 'event';


    // Tell Laravel which column is the primary key of this table.
    protected $primaryKey = 'id_event';

    // Attributes (columns) that can be mass-assigned.
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


    public function polls(): HasMany
    {
        return $this->hasMany(Poll::class, 'id_event');
    }

    // Convert some columns to special PHP types automatically.
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


    // This event BELONGS TO one organizer (a User).
    // $event->organizer- returns the User who organized the event
    public function organizer(): BelongsTo
    {
        // 'id_organizer' is the foreign key column in the "event" table
        return $this->belongsTo(User::class, 'id_organizer');
    }

    // This event HAS MANY participations.
    // $event->participations- returns a collection of Participation objects
    public function participations(): HasMany
    {
        // 'id_event' is the foreign key column in the "participation" table
        return $this->hasMany(Participation::class, 'id_event');
    }

    // This event has many PARTICIPANTS (Users) through the participation table.
    // $event->participants  // returns a collection of User objects
    public function participants(): BelongsToMany
    {
        return $this->belongsToMany(
            User::class,       
            'participation',   
            'id_event',        
            'id_user'          
        )->withPivot('joined_at', 'left_at'); 
    }

    // This event HAS MANY applications (people who applied to join).
    public function applications(): HasMany
    {
        return $this->hasMany(Application::class, 'id_event');
    }

    // This event HAS MANY invitations.
    public function invitations(): HasMany
    {
        return $this->hasMany(Invitation::class, 'id_event');
    }

    // Get the number of current participants.
    // This makes a "virtual" attribute:
    //   $event->current_participants_count
    // It counts how many participations do NOT have "left_at" filled
    // (meaning the user has not left the event).
    public function getCurrentParticipantsCountAttribute(): int
    {
        return $this->participations()
            ->whereNull('left_at') // only people who haven't left
            ->count();
    }

    // Check if the event is full.
    // This creates a "virtual" boolean attribute:
    //   $event->is_full
    // It compares the number of current participants with the capacity.
    public function getIsFullAttribute(): bool
    {
        // Uses the accessor above: current_participants_count
        return $this->current_participants_count >= $this->capacity;
    }

    // Check if the event is upcoming (in the future).
    // Virtual attribute:
    //   $event->is_upcoming
    // It is upcoming if the start date/time is later than now.
    public function getIsUpcomingAttribute(): bool
    {
        return $this->start_at > now();
    }

    // Check if the event is in the past.
    // Virtual attribute:
    // $event->is_past
    // If "end_at" exists, it uses that.
    // Otherwise it uses "start_at" to decide if the event is past.
    public function getIsPastAttribute(): bool
    {
        // If there is an end time, check if that is before now
        // Otherwise, fall back to the start time
        return $this->end_at
            ? $this->end_at < now()
            : $this->start_at < now();
    }

    // Check if the event is happening right now (ongoing).
    // Virtual attribute:
    // $event->is_ongoing
    // It is ongoing if:
    // start_at <= now <= end_at
    public function getIsOngoingAttribute(): bool
    {
        $now = now();

        return $this->start_at <= $now
            &&  $this->end_at >= $now;
    }

    // Only public events.
    // Usage example:
    //   Event::public()->get();
    // This adds "WHERE visibility = 'public'" to the query.
    public function scopePublic($query)
    {
        return $query->where('visibility', 'public');
    }

    // Only published events.
    // Usage example:
    //   Event::published()->get();
    // This adds "WHERE status = 'published'" to the query.
    public function scopePublished($query)
    {
        return $query->where('status', 'published');
    }

    // Only upcoming events.
    // Usage example:
    //   Event::upcoming()->get();
    // This adds "WHERE start_at > now()" to the query.
    public function scopeUpcoming($query)
    {
        return $query->where('start_at', '>', now());
    }

    public function getRouteKeyName()
    {
        return 'id_event';
    }

    // This event has many tags (many-to-many).
    // Usage example:
    //   $event->tags
    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(
            Tag::class,      
            'event_tag',    
            'id_event',      
            'id_tag'         
        );
    }

    // Get the effective status of the event.
    // Returns 'completed' if the event has ended, regardless of stored status.
    // This ensures completed events cannot be edited even if status wasn't updated in DB.
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

    // Check if the event can be edited.
    // Events that have ended or are canceled cannot be edited.
    public function getIsEditableAttribute(): bool
    {
        return !$this->is_past && $this->status !== 'canceled';
    }

    // Check if the event can be canceled.
    // Only published events that haven't ended can be canceled.
    public function getIsCancelableAttribute(): bool
    {
        return $this->status === 'published' && !$this->is_past;
    }

    // Check if the event can be hard deleted.
    // Events can only be hard deleted if they have no activity:
    // - No applications
    // - No participations
    // - No invitations
    public function getCanHardDeleteAttribute(): bool
    {
        // Check if there are any applications
        $hasApplications = $this->applications()->exists();

        // Check if there are any participations
        $hasParticipations = $this->participations()->exists();

        // Check if there are any invitations
        $hasInvitations = $this->invitations()->exists();

        // Event can be hard deleted only if there's no activity
        return !$hasApplications && !$hasParticipations && !$hasInvitations;
    }
}
