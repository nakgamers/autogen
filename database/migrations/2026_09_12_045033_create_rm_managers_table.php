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
        Schema::create('rm_managers', function (Blueprint $table) {
            $table->id(); // ID (Primary Key)
            $table->string('name', 150); // Nama RM Pengelola
            $table->string('phone_number', 20)->nullable(); // Nomor Telepon / WA
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rm_managers');
    }
};