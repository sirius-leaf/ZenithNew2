<?php

namespace App\Models;

use App\Models\Variant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class BuildDetail extends Model
{
    use HasFactory;

    protected $fillable = ['build_id', 'bagian_komponen', 'variant_id'];

    public function variant()
    {
        return $this->belongsTo(Variant::class, 'variant_id', 'id');
    }

    public function pcBuild()
    {
        return $this->belongsTo(PcBuild::class, 'build_id');
    }
}
