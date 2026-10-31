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
        Schema::create('bank_accounts', function (Blueprint $table) {
            // 1. NO REKENING
            $table->string('account_number')->primary();

            // 2. NAMA NASABAH
            $table->string('customer_name');

            // 3. NOA SAAT INI
            $table->integer('current_noa')->default(1);

            // 4. SEGMEN
            $table->string('segment', 100)->nullable();

            // 5. BANK
            $table->enum('bank_name', ['konvensional', 'syariah']);

            //6.periode hb
            $table->string('hb_period')->comment('Periode HB');

            // 7. RINCIAN TUNGGAKAN & DENDA (Penambahan Baru)
            $table->decimal('principal_arrears', 18, 2)->default(0)->comment('Tunggakan Pokok');
            $table->decimal('interest_arrears', 18, 2)->default(0)->comment('Tunggakan Bunga');
            $table->decimal('penalty_fee', 18, 2)->default(0)->comment('Denda Keterlambatan');

            // 8. AGUNAN FA
            $table->string('fa_collateral', 50);

            // Kolom Tambahan / Opsional
            $table->string('branch', 100)->nullable();
            $table->string('kcu_branch_name', 100)->nullable();
            $table->text('billing_address')->nullable();
            $table->string('billing_city', 255)->nullable();
            $table->text('collateral_details')->nullable();
            $table->string('collateral_village', 255)->nullable();
            $table->string('collateral_district', 255)->nullable();
            $table->string('collateral_city', 255)->nullable();
            $table->string('collateral_province', 255)->nullable();
            $table->text('notes')->nullable();
            
            $table->timestamps();

            // Indexing
            $table->index('customer_name');
            $table->index('segment');
            $table->index('bank_name');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bank_accounts');
    }
};