<?php

namespace App\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class Toko extends Model
{
    protected $fillable = ['toko_name','deskripsi', 'id_user'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
