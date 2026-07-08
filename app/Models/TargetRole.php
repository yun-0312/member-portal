<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class TargetRole extends Model
{
    use HasFactory;

    protected $fillable = [
        'role_id',
    ];

    public function targetable() {
        return $this->morphTo();
    }

    public function role() {
        return $this->belongsTo(Role::class);
    }
}
