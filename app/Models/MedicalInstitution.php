<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Builder;
use App\Models\User;


class MedicalInstitution extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'address',
        'phone',
    ];

    public function users() {
        return $this->hasMany(User::class);
    }

    public function scopeVisibleTo(Builder $query, User $user): Builder
    {
        $role = optional($user->role)->name;

        // admin / staff → 全件
        if (in_array($role, config('auth.super_roles', []), true)) {
            return $query;
        }

        // roleなし → 何も見せない
        if (!$user->role_id) {
            return $query->whereRaw('1 = 0');
        }

        // member / director → 自分の医療機関のみ
        if (in_array($role, ['member', 'director'], true)) {
            if (!$user->medical_institution_id) {
                return $query->whereRaw('1 = 0');
            }

            return $query->where('id', $user->medical_institution_id);
        }

        // medical_staff → 閲覧不可
        if ($role === 'medical_staff') {
            return $query->whereRaw('1 = 0');
        }

        // その他 → NG
        return $query->whereRaw('1 = 0');
    }
}
