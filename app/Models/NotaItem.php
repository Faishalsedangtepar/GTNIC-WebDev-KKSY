<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NotaItem extends Model
{
    protected $table = 'nota_item';
    public $timestamps = false;
    protected $fillable = ['nota_id', 'produk_id', 'harga_satuan', 'qty'];
}
