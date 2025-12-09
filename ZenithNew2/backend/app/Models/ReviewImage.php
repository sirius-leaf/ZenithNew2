<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReviewImage extends Model
{
    use HasFactory;

    protected $fillable = ['id_review', 'image_path'];

    public function review()
    {
        return $this->belongsTo(Review::class, 'id_review');
    }
}
