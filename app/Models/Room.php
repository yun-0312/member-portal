<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Room extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'name',
        'sort_order'
    ];

    public function schedules() {
        return $this->hasMany(Schedule::class, 'room_id');
    }
}
