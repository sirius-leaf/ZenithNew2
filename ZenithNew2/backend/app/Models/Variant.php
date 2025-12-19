<?php

namespace App\Models;

use App\Models\Product;
use App\Models\DetailPesanan;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Variant extends Model
{
    use HasFactory;

    protected $fillable = ['product_id', 'nama_varian', 'harga', 'stok', 'gambar_varian'];

    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id', 'id');
    }

    public function detailPesanans()
    {
        return $this->hasMany(DetailPesanan::class, 'variant_id', 'id');
    }

    public function buildDetail()
    {
        return $this->hasMany(BuildDetail::class, 'variant_id', 'id');
    }
}
