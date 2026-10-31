<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bank_accounts', function (Blueprint $table) {
            // Relasi ke tabel rm_managers (opsional/nullable)
            $table->foreignId('rm_manager_id')
                  ->nullable()
                  ->after('segment')
                  ->constrained('rm_managers')
                  ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('bank_accounts', function (Blueprint $table) {
            $table->dropForeign(['rm_manager_id']);
            $table->dropColumn('rm_manager_id');
        });
    }
};