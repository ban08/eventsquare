<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SecurityAnswer extends Model
{
    // Table uses singular name like other tables (e.g., user, profile).
    protected $table = 'security_answer';
    protected $primaryKey = 'id_security_answer';

    protected $fillable = [
        'id_user',
        'question',
        'answer_hash',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'id_user', 'id_user');
    }
}
