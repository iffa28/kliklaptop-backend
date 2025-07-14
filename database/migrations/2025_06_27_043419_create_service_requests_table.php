<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('service_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->string('jenis_laptop');
            $table->text('deskripsi_keluhan');
            $table->binary('photo')->nullable();
            $table->enum('status', [
                'menunggu konfirmasi',
                'dikonfirmasi',
                'sedang diperbaiki',
                'perbaikan selesai'
            ])->default('menunggu konfirmasi');
            $table->date('tanggal_selesai')->nullable();
            $table->timestamps();
        });

        DB::statement("ALTER TABLE service_requests MODIFY photo LONGBLOB");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('service_requests');
    }
};
