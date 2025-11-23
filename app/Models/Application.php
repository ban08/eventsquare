<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Application extends Model
{
    protected $table = 'application';
    protected $primaryKey = 'id_application';
    public $timestamps = false;

    protected $fillable = [
        'id_event',
        'id_user',
        'status',
        'created_at'
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'id_user');
    }

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class, 'id_event');
    }
}