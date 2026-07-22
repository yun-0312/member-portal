<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Traits\HasAvailableSortOrder;

class FaqCategory extends Model
{
    use HasFactory, HasAvailableSortOrder;

    public $timestamps = false;

    protected $fillable = [
        'name',
        'sort_order',
    ];

    public function faqs() {
        return $this->hasMany(Faq::class, 'category_id');
    }
}
