<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

class Content extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'category_id',
        'title',
        'body',
        'published_at',
        'meeting_date',
        'created_by',
    ];

    public function category() {
        return $this->belongsTo(ContentCategory::class, 'category_id');
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
