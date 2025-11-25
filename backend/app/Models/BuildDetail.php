<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class BuildDetail extends Model
{
    use HasFactory;

    protected $fillable = ['id_build', 'bagian_komponen', 'id_varian'];

    public function variant()
    {
        return $this->belongsTo(Product::class, 'id_varian');
    }

    public function pcBuild()
    {
        return $this->belongsTo(PcBuild::class, 'id_build');
    }
}
