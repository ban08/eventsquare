<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AdminAction extends Model
{
    protected $table = 'admin_action';
    protected $primaryKey = 'id_action';
    public $timestamps = false;

    protected $fillable = [
        'id_admin',
        'details',
        'created_at',
    ];

    public function admin()
    {
        return $this->belongsTo(Admin::class, 'id_admin', 'id_admin');
    }
}
