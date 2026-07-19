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
        'created_by',
    ];

    public function recurrences() {
        return $this->hasMany(ScheduleRecurrence::class);
    }

    public function occurrences() {
        return $this->hasMany(ScheduleOccurrence::class);
    }

    public function room() {
        return $this->belongsTo(Room::class, 'room_id');
    }

    public function category() {
        return $this->belongsTo(ScheduleCategory::class, 'schedule_category_id');
    }

    public function creator() {
        return $this->belongsTo(User::class, 'created_by');
    }
}
