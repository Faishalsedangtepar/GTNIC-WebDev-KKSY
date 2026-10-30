<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class KeranjangItem extends Model
{
    protected $table = 'keranjang_item';
    public $timestamps = false;
    protected $fillable = ['produk_id', 'qty'];
}
