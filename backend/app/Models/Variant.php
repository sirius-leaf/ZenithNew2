<?php

namespace App\Models;

use App\Models\Product;
use App\Models\DetailPesanan;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Variant extends Model
{
    use HasFactory;

    protected $primaryKey = 'id_varian';
    protected $fillable = ['id_produk', 'nama_varian', 'harga', 'stok', 'gambar_varian'];

    public function product()
    {
        return $this->belongsTo(Product::class, 'id_produk', 'id_produk');
    }

    public function detailPesanans()
    {
        return $this->hasMany(DetailPesanan::class, 'id_varian', 'id_varian');
    }

    public function buildDetail()
    {
        return $this->hasMany(BuildDetail::class, 'id_varian', 'id_varian');
    }

    public function reviews()
    {
        return $this->hasMany(Review::class, 'id_variant', 'id_varian');
    }
}
