<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
       Schema::table('service_by_admin', function (Blueprint $table) {
            $table->binary('bukti_pembayaran')->nullable()->after('total_bayar');
        });

        // Ubah tipe ke LONGBLOB jika dibutuhkan
        \Illuminate\Support\Facades\DB::statement("ALTER TABLE service_by_admin MODIFY bukti_pembayaran LONGBLOB");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('service_by_admin', function (Blueprint $table) {
            //
        });
    }
};
