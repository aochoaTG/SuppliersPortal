<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserAuthorizerRole extends Model
{
    protected $fillable = [
        'user_id',
        'authorizer_role_id',
        'assigned_by',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function authorizerRole(): BelongsTo
    {
        return $this->belongsTo(AuthorizerRole::class);
    }

    public function assignedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_by');
    }
}
