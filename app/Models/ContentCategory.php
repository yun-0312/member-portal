<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Traits\VisibleToScope;
use App\Traits\HasAvailableSortOrder;

class ContentCategory extends Model
{
    use HasFactory, VisibleToScope, HasAvailableSortOrder;

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

    public function roles() {
        return $this->morphToMany(Role::class, 'targetable', 'role_targetables');
    }

}
