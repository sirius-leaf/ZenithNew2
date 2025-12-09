<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Review extends Model
{
    use HasFactory;

    protected $primaryKey = 'id_ulasan';
    protected $fillable = ['komentar', 'rating', 'id_user', 'id_produk', 'id_variant', 'id_pesanan'];

    public function user()
    {
        return $this->belongsTo(User::class, 'id_user');
    }

    public function product()
    {
        return $this->belongsTo(Product::class, 'id_produk');
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
