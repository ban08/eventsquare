<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Poll extends Model
{
    use HasFactory;

    protected $table = 'poll';
    protected $primaryKey = 'id_poll';
    public $timestamps = false; // Only created_at exists in schema

    protected $fillable = [
        'id_event',
        'question',
        'created_at'
    ];

    protected $casts = [
        'created_at' => 'datetime',
    ];

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class, 'id_event');
    }

    public function options(): HasMany
    {
        return $this->hasMany(PollOption::class, 'id_poll')->orderBy('position');
    }

    public function votes(): HasMany
    {
        return $this->hasMany(PollVote::class, 'id_poll');
    }
}
