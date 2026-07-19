<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use App\Enums\UserStatus;

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
}
