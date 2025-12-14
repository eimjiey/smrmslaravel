<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class IncidentAuditLog extends Model
{
    use HasFactory;
    
    protected $table = 'incident_audit_logs'; 

    public $timestamps = false; 

    protected $fillable = [
        'incident_id',
        'action_type',
        'field_changed',
        'old_value',
        'new_value',
        'changed_at',
    ];

    public function incident()
    {
        return $this->belongsTo(\App\Models\Incident::class);
    }
}
