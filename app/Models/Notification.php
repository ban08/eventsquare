<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * R16: Notification model
 * Notifications inform users about invitations, event updates, etc.
 */
class Notification extends Model
{
    protected $table = 'notification';
    protected $primaryKey = 'id_notification';

    public $timestamps = false; // Using created_at manually, no updated_at

    protected $fillable = [
        'id_user',
        'message',
        'id_event',
        'id_invitation',
        'id_application',
        'created_at',
        'read_at',
    ];

    /**
     * Get the route key for the model.
     */
    public function getRouteKeyName()
    {
        return 'id_notification';
    }

    protected function casts(): array
    {
        return [
            'created_at' => 'datetime',
            'read_at' => 'datetime',
        ];
    }

    /**
     * The user this notification belongs to.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'id_user');
    }

    /**
     * The event related to this notification (optional).
     */
    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class, 'id_event');
    }

    /**
     * The invitation related to this notification (optional).
     */
    public function invitation(): BelongsTo
    {
        return $this->belongsTo(Invitation::class, 'id_invitation');
    }

    /**
     * The application related to this notification (optional).
     */
    public function application(): BelongsTo
    {
        return $this->belongsTo(Application::class, 'id_application');
    }

    /**
     * Check if notification has been read.
     */
    public function getIsReadAttribute(): bool
    {
        return $this->read_at !== null;
    }

    /**
     * Mark notification as read.
     */
    public function markAsRead(): void
    {
        if ($this->read_at === null) {
            $this->update(['read_at' => now()]);
        }
    }

    /**
     * Get human-readable message based on notification type.
     */
    public function getDisplayMessageAttribute(): string
    {
        return match($this->message) {
            'invited' => 'You have been invited to an event',
            'event updated' => 'An event you\'re participating in has been updated',
            'new application' => 'Someone applied to join your event',
            'application accepted' => 'Your application to join an event was accepted',
            'application rejected' => 'Your application to join an event was rejected',
            default => $this->message,
        };
    }
}
