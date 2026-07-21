<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class File extends Model
{
    use HasFactory;

    protected $fillable = [
        'fileable_id',
        'fileable_type',
        'path',
        'name',
        'type',
    ];

    public function fileable() {
        return $this->morphTo();
    }

    public function getUrlAttribute() {
        return asset('storage/' . $this->path);
    }
}
