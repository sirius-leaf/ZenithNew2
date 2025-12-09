<?php

namespace App\Models;

use App\Models\Toko;
use App\Models\User;
use App\Models\DetailPesanan;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Pesanan extends Model
{
    use HasFactory;
    protected $fillable = [
        'user_id',
        'toko_id',
        'total_harga',
        'status',
        'alamat_pengiriman',
        'alasan_pembatalan',
        'previous_status',
        'is_cancellation_rejected',
        'payment_method',
    ];

    // Relasi: Pesanan ini milik siapa
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Relasi: Pesanan ini ditujukan ke toko mana
    public function toko()
    {
        return $this->belongsTo(Toko::class);
    }

    // Relasi: Pesanan ini punya item apa saja
    public function detailPesanans()
    {
        return $this->hasMany(DetailPesanan::class);
    }

    public function reviews()
    {
        return $this->hasMany(Review::class, 'id_pesanan');
    }
}
