<?php

namespace App\Models;

use App\Models\User;
use App\Models\Pesanan;
use App\Models\Product;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Toko extends Model
{
    use HasFactory;

    protected $fillable = ['toko_name', 'deskripsi', 'user_id', 'is_frozen', 'frozen_reason', 'appeal_reason'];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function products()
    {
        return $this->hasMany(Product::class, 'toko_id');
    }

    public function pesanans()
    {
        return $this->hasMany(Pesanan::class);
    }
}
