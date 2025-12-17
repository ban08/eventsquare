<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PollOption extends Model
{
    use HasFactory;

    protected $table = 'poll_option';
    protected $primaryKey = 'id_option';
    public $timestamps = false;

    protected $fillable = [
        'id_poll',
        'label',
        'position'
    ];

    public function poll(): BelongsTo
    {
        return $this->belongsTo(Poll::class, 'id_poll');
    }

    public function votes(): HasMany
    {
        return $this->hasMany(PollVote::class, 'id_option');
    }
}
