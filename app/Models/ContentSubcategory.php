<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Traits\HasAvailableSortOrder;

class ContentSubcategory extends Model
{
    use HasFactory, HasAvailableSortOrder;

    protected $fillable = [
        'category_id',
        'name',
        'sort_order',
    ];

    public function category() {
        return $this->belongsTo(ContentCategory::class, 'category_id');
    }

    public function contents() {
        return $this->hasMany(Content::class, 'subcategory_id');
    }

}
