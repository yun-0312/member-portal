<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Video extends Model
{
    use HasFactory;

    protected $fillable = [
        'type',
        'workshop_id',
        'title',
        'description',
        'external_url',
        'published_at',
        'created_by',
    ];

    public function workshop() {
        return $this->belongsTo(Workshop::class);
    }

    public function creator() {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function targetRoles() {
        return $this->morphMany(TargetRole::class, 'targetable');
    }

    public function files() {
        return $this->morphMany(File::class, 'fileable');
    }
}
