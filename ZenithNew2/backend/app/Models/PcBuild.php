<?php

namespace App\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class PcBuild extends Model
{
    use HasFactory;

    protected $fillable = ['user_id', 'nama_build'];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function buildDetail()
    {
        return $this->hasMany(BuildDetail::class, 'build_id', 'id');
    }
}
