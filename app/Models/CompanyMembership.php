<?php

namespace App\Models;

use App\Models\Traits\UsesUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CompanyMembership extends Model
{
    use SoftDeletes;
    use UsesUuid;

    protected $table = 'company_memberships';

    protected $fillable = [
        'id',
        'company_id',
        'user_id',
        'role',
        'status',
        'invited_email',
        'invited_at',
        'accepted_at',
        'created_by',
        'updated_by',
        'deleted_by',
    ];

    protected $casts = [
        'invited_at' => 'datetime',
        'accepted_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];
}
