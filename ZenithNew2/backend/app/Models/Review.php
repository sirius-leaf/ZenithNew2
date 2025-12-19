<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Review extends Model
{
    use HasFactory;

    protected $fillable = ['komentar', 'rating', 'user_id', 'product_id', 'variant_id', 'id_pesanan'];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id');
    }

    public function variant()
    {
        return $this->belongsTo(Variant::class, 'id_variant');
    }

    public function order()
    {
        return $this->belongsTo(Pesanan::class, 'id_pesanan');
    }

    public function images()
    {
        return $this->hasMany(ReviewImage::class, 'id_review');
    }
}
