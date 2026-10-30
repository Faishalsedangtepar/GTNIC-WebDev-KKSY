<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Produk extends Model
{
    protected $table = 'produk';
    public $timestamps = false;
    protected $fillable = ['nama', 'kategori_id', 'satuan_id', 'deskripsi', 'harga_jual', 'stok'];
}
