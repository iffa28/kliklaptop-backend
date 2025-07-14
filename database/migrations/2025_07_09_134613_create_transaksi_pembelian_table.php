<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('transaksi_pembelian', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('product_id')->constrained('products')->onDelete('cascade');
            $table->string('metode_pembayaran');
            $table->enum('status', [
                'transaksi berhasil',
                'transaksi batal',
                'menunggu penjemputan'
            ])->default('menunggu penjemputan');
            $table->binary('bukti_pembayaran')->nullable();
            $table->timestamps();
        });

        // Ubah tipe bukti_pembayaran menjadi LONGBLOB
        DB::statement("ALTER TABLE transaksi_pembelian MODIFY bukti_pembayaran LONGBLOB");
    }

    public function down(): void
    {
        Schema::dropIfExists('transaksi_pembelian');
    }
};
