<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AuditLog extends Model
{
    use HasFactory;

    protected $table = 'audit_log';

    public $timestamps = false;

    protected $fillable = [
        'occurred_at',
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
}
