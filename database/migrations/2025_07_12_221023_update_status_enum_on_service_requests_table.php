<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE service_requests 
            MODIFY status ENUM(
                'menunggu konfirmasi',
                'dikonfirmasi',
                'sedang diperbaiki',
                'perbaikan selesai',
                'berhasil'
            ) DEFAULT 'menunggu konfirmasi'");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE service_requests 
            MODIFY status ENUM(
                'menunggu konfirmasi',
                'dikonfirmasi',
                'sedang diperbaiki',
                'perbaikan selesai'
            ) DEFAULT 'menunggu konfirmasi'");
    }
};
