<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Nota extends Model
{
    protected $table = 'nota';
    public $timestamps = false;
    protected $fillable = ['kasir_id', 'total', 'dibayar'];
}
