<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Traits\HasAvailableSortOrder;
class Room extends Model
{
    use HasFactory, HasAvailableSortOrder;

    public $timestamps = false;

    protected $fillable = [
        'name',
        'sort_order'
    ];

    public function schedules() {
        return $this->hasMany(Schedule::class, 'room_id');
    }
}
