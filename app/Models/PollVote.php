<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PollVote extends Model
{
    use HasFactory;

    protected $table = 'poll_vote';
    public $incrementing = false;
    public $timestamps = false; // Only created_at exists

    // Composite primary key handling is tricky in Eloquent.
    // We'll define it but might need to use `where` clauses manually for updates/deletes.
    protected $primaryKey = ['id_poll', 'id_participation'];

    protected $fillable = [
        'id_poll',
        'id_option',
        'id_participation',
        'created_at'
    ];

    protected $casts = [
        'created_at' => 'datetime',
    ];

    public function poll(): BelongsTo
    {
        return $this->belongsTo(Poll::class, 'id_poll');
    }

    public function option(): BelongsTo
    {
        return $this->belongsTo(PollOption::class, 'id_option');
    }

    public function participation(): BelongsTo
    {
        return $this->belongsTo(Participation::class, 'id_participation');
    }
}
