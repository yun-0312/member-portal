<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Builder;
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

    public function scopeVisibleTo(Builder $query, User $user): Builder
{
    // ① Admin / Staff などのスーパーロールは全件表示
    if (in_array(optional($user->role)->name, config('auth.super_roles', []), true)) {
        return $query;
    }

    // ② role_id が無いユーザーは非表示
    if (!$user->role_id) {
        return $query->whereRaw('1 = 0');
    }

    return $query->where(function ($q) use ($user) {
        // 条件A: このカテゴリにアクセス権限（roles）が設定されていて、ユーザーの role_id と一致する
        $q->whereHas('roles', function ($q2) use ($user) {
            $q2->where('roles.id', $user->role_id);
        })
        // 条件B: このカテゴリ自体にロール制限が無い（＝全ユーザーに公開されるカテゴリ）
        ->orWhereDoesntHave('roles');
    });
}

}
