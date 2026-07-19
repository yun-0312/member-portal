<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Faq extends Model
{
    use HasFactory;

    protected $fillable = [
        'question',
        'answer',
        'category_id',
        'created_by',
        'created_at',
    ];

    public function category() {
        return $this->belongsTo(FaqCategory::class, 'category_id');
    }

    public function creator() {
        return $this->belongsTo(User::class, 'created_by');
    }
}
