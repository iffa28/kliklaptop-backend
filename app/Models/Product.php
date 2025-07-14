<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    //ini nama tabel di db
    protected $table = 'products';

    protected $fillable = [
        'nama_produk',
        'deskripsi',
        'harga',
        'stok',
        'foto_produk',
    ];

    //
}
