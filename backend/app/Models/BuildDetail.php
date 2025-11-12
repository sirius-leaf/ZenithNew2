<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class BuildDetail extends Model
{
    use HasFactory;

    protected $fillable = ['id_build', 'id_produk', 'bagian_komponen'];

    public function product()
    {
        return $this->belongsTo(Product::class, 'id_produk');
    }

    public function pcBuild()
    {
        return $this->belongsTo(PcBuild::class, 'id_build');
    }
}
