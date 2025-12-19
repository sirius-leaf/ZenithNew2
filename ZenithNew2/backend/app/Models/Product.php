<?php

namespace App\Models;

use App\Models\Toko;
use App\Models\BuildDetail;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Product extends Model
{
    use HasFactory;

    protected $fillable = ['toko_id', 'deskripsi', 'merek', 'nama_produk'];

    public function variant()
    {
        return $this->hasMany(Variant::class, 'product_id', 'id');
    }

    public function categories()
    {
        return $this->belongsToMany(Category::class, 'category_details', 'product_id', 'id_kategori');
    }

    public function categoryDetail()
    {
        return $this->hasMany(CategoryDetail::class, 'product_id', 'id');
    }

    public function toko()
    {
        return $this->belongsTo(Toko::class, 'toko_id');
    }

    public function reviews()
    {
        return $this->hasMany(Review::class, 'product_id', 'id');
    }
}
