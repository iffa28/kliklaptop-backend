<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    
    public function up(): void
    {
        Schema::create('profiles', function (Blueprint $table) {
            $table->id();

            // Relasi ke users
            $table->unsignedBigInteger('user_id')->unique(); 
            $table->string('phone')->nullable();
            $table->string('photo')->nullable(); 
            $table->timestamps();

            // Foreign key constraint
            $table->foreign('user_id')
                ->references('id')->on('users')
                ->onDelete('cascade'); 
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('profiles');
    }
};
