<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Models\Toko;
use App\Models\Pesanan;
use App\Models\PcBuild;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Sanctum\HasApiTokens;
use App\Notifications\VerifyEmailSpaNotification;

class User extends Authenticatable implements MustVerifyEmail
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'no_telpon',
        'alamat',
        'role',
        'is_banned',
        'profile_photo',
        'store_name',
        'address',
        'description',
        'ktp_path',
        'npwp_path',
        'store_photo',
        'verification_code',
        'verification_code_expires_at',
    ];

    public function toko()
    {
        return $this->hasOne(Toko::class, 'user_id');
    }

    public function pesanans()
    {
        return $this->hasMany(Pesanan::class);
    }

    public function pcBuild()
    {
        return $this->hasMany(PcBuild::class, 'id_user');
    }

    public function reviews()
    {
        return $this->hasMany(Review::class, 'id_user');
    }


    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function sendEmailVerificationNotification()
    {
        $this->notify(new VerifyEmailSpaNotification());
    }
}
