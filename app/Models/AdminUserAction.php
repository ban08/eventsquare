<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AdminUserAction extends Model
{
    protected $table = 'admin_user_action';
    protected $primaryKey = 'id_action';
    public $timestamps = false;

    protected $fillable = [
        'id_action',
        'action',
        'target_user',
    ];
}
