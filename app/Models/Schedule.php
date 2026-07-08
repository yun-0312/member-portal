<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Schedule extends Model
{
    use HasFactory;

    protected $fillable = [
        'room_id',
        'title',
        'schedule_category_id',
        'location',
        'url',
        'start_at',
        'end_at',
        'created_by',
    ];

    public function room() {
        return $this->belongsTo(Room::class);
    }

    public function category() {
        return $this->belongsTo(ScheduleCategory::class);
    }

    public function creator() {
        return $this->belongsTo(User::class, 'created_by');
    }
}
