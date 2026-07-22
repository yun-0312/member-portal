<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Traits\HasAvailableSortOrder;

class GroupCategory extends Model
{
    use HasFactory, HasAvailableSortOrder;

    public $timestamps = false;

    protected $fillable = [
        'name',
        'sort_order'
    ];

    public function groups() {
        return $this->hasMany(Group::class);
    }

}
