<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ContentCategory extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'sort_order',
    ];

    public function contents() {
        return $this->hasMany(Content::class, 'category_id');
    }

    public function subcategories() {
        return $this->hasMany(ContentSubcategory::class, 'category_id');
    }

    public function targetRoles() {
        return $this->morphMany(TargetRole::class, 'targetable');
    }
}
