<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\User;
use App\Traits\VisibleToScope;
use App\Traits\HasPublishedScope;

class Video extends Model
{
    use HasFactory, VisibleToScope, HasPublishedScope;

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

    public function roles() {
        return $this->morphToMany(Role::class, 'targetable', 'role_targetables');
    }

    public function files() {
        return $this->morphMany(File::class, 'fileable');
    }

    public function getPublishEndColumn(): ?string {
        return 'expired_at';
    }

}
