<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Skema role baru (perintah Nandz 31 Okt 2026):
     * admin -> super_admin, staf -> team_leader,
     * yogie@bankjakarta.co.id -> department_head.
     */
    public function up(): void
    {
        // 1) Lebarkan enum: nilai lama & baru sama-sama sah sementara.
        DB::statement("ALTER TABLE users MODIFY role ENUM('admin','staf','super_admin','department_head','team_leader') NOT NULL DEFAULT 'staf'");

        // 2) Petakan data lama ke role baru.
        DB::table('users')->where('role', 'admin')->update(['role' => 'super_admin']);
        DB::table('users')->where('role', 'staf')->update(['role' => 'team_leader']);
        DB::table('users')->where('email', 'yogie@bankjakarta.co.id')->update(['role' => 'department_head']);

        // 3) Persempit ke tiga role final.
        DB::statement("ALTER TABLE users MODIFY role ENUM('super_admin','department_head','team_leader') NOT NULL DEFAULT 'team_leader'");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE users MODIFY role ENUM('admin','staf','super_admin','department_head','team_leader') NOT NULL DEFAULT 'staf'");

        DB::table('users')->where('role', 'super_admin')->update(['role' => 'admin']);
        DB::table('users')->whereIn('role', ['department_head', 'team_leader'])->update(['role' => 'staf']);

        DB::statement("ALTER TABLE users MODIFY role ENUM('admin','staf') NOT NULL DEFAULT 'staf'");
    }
};
