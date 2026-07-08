<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Workshop extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'description',
        'start_at',
        'end_at',
        'location',
        'lecture',
        'created_by',
    ];

    public function creator() {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function videos() {
        return $this->hasMany(Video::class, 'workshop_id');
    }

    public function notices() {
        return $this->hasMany(Notice::class, 'workshop_id');
    }

    public function schedules() {
        return $this->hasMany(Schedule::class, 'workshop_id');
    }

    public function targetRoles() {
        return $this->morphMany(TargetRoles::class, 'targetable');
    }

    public function files() {
        return $this->morphMany(File::class, 'fileable');
    }
}
