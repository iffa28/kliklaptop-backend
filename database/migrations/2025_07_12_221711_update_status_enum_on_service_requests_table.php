<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('service_by_admin', function (Blueprint $table) {
            $table->integer('total_bayar')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('service_by_admin', function (Blueprint $table) {
            $table->integer('total_bayar')->nullable(false)->change(); // Balik lagi tidak nullable
        });
    }
};
