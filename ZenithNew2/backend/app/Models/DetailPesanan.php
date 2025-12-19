<?php

namespace App\Models;

use App\Models\Pesanan;
use App\Models\Variant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class DetailPesanan extends Model
{
    use HasFactory;
    // Matikan timestamps (created_at, updated_at)
    public $timestamps = false;

    protected $fillable = [
        'pesanan_id',
        'id_varian',
        'kuantitas',
        'harga'
    ];

    // Relasi: Detail ini milik pesanan mana
    public function pesanan()
    {
        return $this->belongsTo(Pesanan::class);
    }

    // Relasi: Detail ini merujuk ke varian produk mana
    public function variant()
    {
        return $this->belongsTo(Variant::class, 'variant_id');
    }
}
