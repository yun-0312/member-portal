<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Builder;
use App\Models\User;
use App\Traits\HasPublishedScope;

class Content extends Model
{
    use HasFactory, HasPublishedScope;

    protected $casts = [
        'published_at' => 'datetime',
    ];

    protected $fillable = [
        'category_id',
        'subcategory_id',
        'title',
        'body',
        'published_at',
        'meeting_date',
        'created_by',
    ];

    public function category() {
        return $this->belongsTo(ContentCategory::class, 'category_id');
    }

    public function subcategory() {
        return $this->belongsTo(ContentSubcategory::class, 'subcategory_id');
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

    public function scopeVisibleTo(Builder $query, User $user): Builder {
        // admin / staff は全件（Gate::beforeと合わせる）
        if (in_array(optional($user->role)->name, config('auth.super_roles', []), true)) {
            return $query;
        }

        if (!$user->role_id) {
            return $query->whereRaw('1 = 0');
        }

        return $query->where(function ($q) use ($user) {

            // ① Contentにrolesがある
            $q->whereHas('roles', function ($q2) use ($user) {
                $q2->where('roles.id', $user->role_id);
            });

            // ② Contentにrolesが無く、Categoryにrolesがある
            $q->orWhere(function ($q2) use ($user) {
                $q2->whereDoesntHave('roles')
                ->whereHas('category', function ($q3) use ($user) {
                    $q3->whereHas('roles', function ($q4) use ($user) {
                        $q4->where('roles.id', $user->role_id);
                    });
                });
            });

            // ③ ContentにもCategoryにもrolesが無い → 全公開
            $q->orWhere(function ($q2) {
                $q2->whereDoesntHave('roles')
                ->where(function ($q3) {
                    $q3->whereDoesntHave('category')
                        ->orWhereDoesntHave('category.roles');
                });
            });
        });
    }
}
