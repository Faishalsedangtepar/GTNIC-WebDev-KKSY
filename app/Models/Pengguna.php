<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;

class Pengguna extends Authenticatable
{
    protected $table = 'pengguna';
    public $timestamps = false;
    protected $fillable = ['username', 'password_hash', 'role'];
    protected $hidden = ['password_hash'];

    // Kolom password kita bernama password_hash, bukan password
    public function getAuthPassword()
    {
        return $this->password_hash;
    }

    // Tabel tidak punya kolom remember_token
    public function getRememberTokenName()
    {
        return '';
    }
}
