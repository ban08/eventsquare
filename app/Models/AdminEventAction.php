<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * R20 (admin_event_action) - Logs admin actions on events.
 * Used for AD03 (Delete Event) per EBD schema.
 */
class AdminEventAction extends Model
{
    protected $table = 'admin_event_action';
    protected $primaryKey = 'id_action';
    public $timestamps = false;
    public $incrementing = false; // id_action comes from admin_action

    protected $fillable = [
        'id_action',
        'action',
        'target_event',
    ];

    public function adminAction()
    {
        return $this->belongsTo(AdminAction::class, 'id_action', 'id_action');
    }

    public function event()
    {
        return $this->belongsTo(Event::class, 'target_event', 'id_event');
    }
}
