<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Tabel Jenis Surat
        Schema::create('letter_types', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique(); // cth: SK, ST, SP
            $table->string('name');           // cth: Surat Keterangan
            $table->string('format_number'); // cth: {AUTO}/ORG/{MONTH}/{YEAR}
            $table->timestamps();
        });

        // Tabel Template Surat
        Schema::create('letter_templates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('letter_type_id')->constrained()->onDelete('cascade');
            $table->string('title');
            $table->longText('content'); 
            $table->timestamps();
        });

        // Tabel Utama Surat
        Schema::create('letters', function (Blueprint $table) {
            $table->id();
            $table->string('letter_number')->unique();
            $table->foreignId('letter_type_id')->constrained();
            $table->foreignId('user_id')->constrained(); // Pembuat surat
            $table->string('recipient')->nullable();                  // Tujuan/Penerima
            $table->string('subject');                    // Perihal
            $table->date('letter_date');                  // Tanggal Surat
            $table->longText('content_data');                // Data dinamis surat
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('letters');
        Schema::dropIfExists('letter_templates');
        Schema::dropIfExists('letter_types');
    }
};