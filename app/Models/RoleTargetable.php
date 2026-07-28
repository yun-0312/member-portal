<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RoleTargetable extends Model
{
    protected $fillable = [
        'targetable_id',
        'targetable_type',
        'role_id',
    ];

    public function targetable() {
        return $this->morphTo();
    }

    public function role() {
        return $this->belongsTo(Role::class);
    }
}
