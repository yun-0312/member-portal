<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Group extends Model
{
    use HasFactory;

    protected $fillable = [
        'group_category_id',
        'name',
    ];

    public function category() {
        return $this->belongsTo(GroupCategory::class, 'group_category_id');
    }

    public function contents() {
        return $this->hasMany(Content::class);
    }
}
