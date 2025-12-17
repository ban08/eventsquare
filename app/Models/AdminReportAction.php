<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AdminReportAction extends Model
{
    protected $table = 'admin_report_action';
    protected $primaryKey = 'id_action';
    public $timestamps = false;

    protected $fillable = [
        'id_action',
        'action',
        'id_report',
    ];

    public function adminAction()
    {
        return $this->belongsTo(AdminAction::class, 'id_action', 'id_action');
    }

    public function report()
    {
        return $this->belongsTo(EventReport::class, 'id_report', 'id_report');
    }
}
