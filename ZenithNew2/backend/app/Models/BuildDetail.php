<?php

namespace App\Models;

use App\Models\Variant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class BuildDetail extends Model
{
    use HasFactory;

    protected $fillable = ['id_build', 'bagian_komponen', 'id_varian'];

    public function variant()
    {
        return $this->belongsTo(Variant::class, 'id_varian', 'id_varian');
    }

    public function pcBuild()
    {
        return $this->belongsTo(PcBuild::class, 'id_build');
    }
}
