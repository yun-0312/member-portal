<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Traits\HasAvailableSortOrder;

class ContentSubcategory extends Model
{
    use HasFactory, HasAvailableSortOrder;

    protected $fillable = [
        'category_id',
        'parent_id',
        'name',
        'slug',
        'sort_order',
        'display_type',
    ];

    public function category() {
        return $this->belongsTo(ContentCategory::class, 'category_id');
    }

    public function contents() {
        return $this->hasMany(Content::class, 'subcategory_id');
    }

    public function roles() {
        return $this->morphToMany(Role::class, 'targetable', 'role_targetables');
    }

    public function parent()
    {
        return $this->belongsTo(ContentSubcategory::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(ContentSubcategory::class, 'parent_id');
    }

}
