<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Traits\VisibleToScope;
use App\Traits\HasAvailableSortOrder;

class ContentCategory extends Model
{
    use HasFactory, VisibleToScope, HasAvailableSortOrder;

    protected $fillable = [
        'name',
        'slug',
        'section',
        'sort_order',
        'display_type',
    ];

    public function contents() {
        return $this->hasMany(Content::class, 'category_id');
    }

    public function subcategories() {
        return $this->hasMany(ContentSubcategory::class, 'category_id');
    }

     //このカテゴリーに属する「第一階層（親サブカテゴリーを持たないもの）」のみを取得
    public function topLevelSubcategories() {
        return $this->hasMany(ContentSubcategory::class, 'category_id')->whereNull('parent_id');
    }

    public function roles() {
        return $this->morphToMany(Role::class, 'targetable', 'role_targetables');
    }

}
