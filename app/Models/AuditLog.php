<?php

namespace App\Models;

use App\Models\Traits\UsesUuid;
use Illuminate\Database\Eloquent\Model;

class AuditLog extends Model
{
    use UsesUuid;

    protected $table = 'audit_logs';

    public $timestamps = true;

    protected $fillable = [
        'id',
        'actor_user_id',
        'company_id',
        'action',
        'entity_type',
        'entity_id',
        'severity',
        'ip',
        'user_agent',
        'request_id',
        'metadata_json',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];
}
