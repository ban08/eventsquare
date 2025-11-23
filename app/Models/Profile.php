<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Profile extends Model
{
    protected $table = 'profile';
    protected $primaryKey = 'id_user';
    public $timestamps = false; // table has no timestamp columns

    protected $fillable = [
        'id_user',
        'photo_url',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'id_user', 'id_user');
    }
}
