<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ScheduleRecurrence extends Model
{
    use HasFactory;

    protected $casts = [
        'byweekday' => 'array',
        'until' => 'datetime',
        'start_after' => 'datetime',
    ];

    protected $fillable = [
        'schedule_id',
        'frequency',
        'byweekday',
        'bysetpos',
        'interval',
        'until',
        'start_after'
    ];

    public function schedule() {
        return $this->belongsTo(Schedule::class);
    }

    public function occurrences() {
        return $this->hasMany(ScheduleOccurrence::class, 'recurrence_id');
    }
}
