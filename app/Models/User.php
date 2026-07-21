<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use App\Enums\UserStatus;
use App\Notifications\VerifyEmail;
use App\Notifications\ResetPasswordNotification;
use Illuminate\Database\Eloquent\Builder;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable, SoftDeletes, HasApiTokens;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role_id',
        'status',
        'approved_at',
        'approved_by',
        'medical_institution_id',
        'email_verified_at',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'status' => UserStatus::class,
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function role() {
        return $this->belongsTo(Role::class);
    }

    public function approvedBy() {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function medicalInstitution() {
        return $this->belongsTo(MedicalInstitution::class);
    }

    public function noticesCreated() {
        return $this->hasMany(Notice::class, 'created_by');
    }

    public function hasPermission(string $permissionName): bool {
        return $this->role
            ->permissions
            ->contains('name', $permissionName);
    }

    public function workshopsCreated() {
        return $this->hasMany(Workshop::class, 'created_by');
    }

    public function approvedUsers() {
        return $this->hasMany(User::class, 'approved_by');
    }

    public function groups() {
        return $this->belongsToMany(Group::class, 'group_user');
    }

    public function sendEmailVerificationNotification() {
        $this->notify(new VerifyEmail);
    }

    public function sendPasswordResetNotification($token) {
        $this->notify(new ResetPasswordNotification($token));
    }

    public function scopeVisibleTo(Builder $query, User $user): Builder
    {
        $role = optional($user->role)->name;

        // admin / staff
        if (in_array($role, config('auth.super_roles', []), true)) {
            return $query;
        }

        // roleなし
        if (!$user->role_id) {
            return $query->whereRaw('1 = 0');
        }

        // member / director
        if (in_array($role, ['member', 'director'], true)) {
            if (!$user->medical_institution_id) {
                return $query->whereRaw('1 = 0');
            }

            return $query->where('medical_institution_id', $user->medical_institution_id);
        }

        // medical_staff
        if ($role === 'medical_staff') {
            return $query->where('id', $user->id);
        }

        return $query->whereRaw('1 = 0');
    }
}
