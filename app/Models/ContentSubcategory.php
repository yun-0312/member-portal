<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ContentSubcategory extends Model
{
    use HasFactory;

    protected $fillable = [
        'category_id',
        'name',
        'sort_order',
    ];

    public function category() {
        return $this->belongsTo(ContentCategory::class);
    }

    public function contents() {
        return $this->hasMany(Content::class, 'subcategory_id');
    }

}
