<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Category extends Model
{
    use HasFactory;

    protected $primaryKey = 'id_kategori';
    protected $fillable = ['nama_kategori'];

    public function products()
    {
        return $this->belongsToMany(Product::class, 'category_details', 'id_kategori', 'id_produk');
    }

    public function categoryDetail()
    {
        return $this->hasMany(CategoryDetail::class, 'id_kategori', 'id_kategori');
    }
}
