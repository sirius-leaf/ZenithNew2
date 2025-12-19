<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Category extends Model
{
    use HasFactory;

    protected $fillable = ['nama_kategori'];

    public function products()
    {
        return $this->belongsToMany(Product::class, 'category_details', 'category_id', 'product_id');
    }

    public function categoryDetail()
    {
        return $this->hasMany(CategoryDetail::class, 'category_id', 'id');
    }
}
