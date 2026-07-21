<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Traits\VisibleToScope;
use App\Traits\HasPublishedScope;

class Notice extends Model
{
    use HasFactory, VisibleToScope, HasPublishedScope;

    protected $casts = [
        'published_at' => 'datetime',
    ];

    protected $fillable= [
        'title',
        'committee_name',
        'body',
        'category_id',
        'published_at',
        'created_by',
    ];

    public function category() {
        return $this->belongsTo(NoticeCategory::class, 'category_id');
    }

    public function creator() {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function roles() {
        return $this->morphToMany(Role::class, 'targetable', 'role_targetables');
    }

    public function files() {
        return $this->morphMany(File::class, 'fileable');
    }

}
