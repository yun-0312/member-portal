<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\Builder;
use App\Models\User;

trait VisibleToScope
{
    public function scopeVisibleTo(Builder $query, User $user): Builder
    {
        if (in_array(optional($user->role)->name, config('auth.super_roles', []), true)) {
            return $query;
        }

        if (!$user->role_id) {
            return $query->whereRaw('1 = 0');
        }

        return $query->where(function ($q) use ($user) {
            $q->whereHas('roles', fn($q2) =>
                $q2->where('roles.id', $user->role_id)
            )->orWhereDoesntHave('roles');
        });
    }
}