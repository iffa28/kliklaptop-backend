<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TransaksiPembelian extends Model
{
    use HasFactory;

    protected $table = 'transaksi_pembelian';

    protected $fillable = [
        'user_id',
        'product_id',
        'metode_pembayaran',
        'status',
        'bukti_pembayaran',
        'created_at',

    ];

    protected $casts = [
        'product_id' => 'integer',
        'user_id' => 'integer',
        'id' => 'integer',
    ];

    /**
     * Relasi ke produk
     */
    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
