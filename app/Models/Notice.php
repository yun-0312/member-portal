<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Notice extends Model
{
    use HasFactory;

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

    public function workshop() {
        return $this->belongsTo(Workshop::class, 'workshop_id');
    }

    public function targetRoles() {
        return $this->morphMany(TargetRole::class, 'targetable');
    }

    public function files() {
        return $this->morphMany(File::class, 'fileable');
    }
}
