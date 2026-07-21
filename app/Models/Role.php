<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Role extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'name',
    ];

    protected $hidden = [
        'pivot',
    ];

    public function users() {
        return $this->hasMany(User::class);
    }

    public function permissions() {
        return $this->belongsToMany(Permission::class, 'role_permissions');
    }

    public function videos() {
        return $this->morphedByMany(Video::class, 'targetable', 'role_targetables');
    }

    public function contents() {
        return $this->morphedByMany(Content::class, 'targetable', 'role_targetables');
    }

    public function notices() {
        return $this->morphedByMany(Notice::class, 'targetable', 'role_targetables');
    }

}
