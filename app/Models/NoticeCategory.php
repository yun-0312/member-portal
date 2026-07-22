<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Traits\HasAvailableSortOrder;

class NoticeCategory extends Model
{
    use HasFactory, HasAvailableSortOrder;

    public $timestamps = false;

    protected $fillable = [
        'name',
        'slug',
        'sort_order',
    ];

    public function notices() {
        return $this->hasMany(Notice::class, 'category_id');
    }
}
