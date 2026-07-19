<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Video extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'description',
        'external_url',
        'published_at',
        'expired_at',
        'created_by',
    ];

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
