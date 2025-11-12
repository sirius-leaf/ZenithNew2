<?php

namespace App\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class PcBuild extends Model
{
    use HasFactory;

    protected $primaryKey = 'id_build';
    protected $fillable = ['id_user', 'nama_build'];

    public function user()
    {
        return $this->belongsTo(User::class, 'id_user');
    }

    public function buildDetail()
    {
        return $this->hasMany(BuildDetail::class, 'id_build', 'id_build');
    }
}
