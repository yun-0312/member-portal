<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ScheduleOccurrence extends Model
{
    use HasFactory;

    protected $casts = [
        'start_at' => 'datetime',
        'end_at' => 'datetime',
    ];

    protected $fillable = [
        'schedule_id',
        'recurrence_id',
        'start_at',
        'end_at',
        'type',
    ];

    public function schedule() {
        return $this->belongsTo(Schedule::class);
    }

    public function recurrence() {
        return $this->belongsTo(ScheduleRecurrence::class);
    }
}
