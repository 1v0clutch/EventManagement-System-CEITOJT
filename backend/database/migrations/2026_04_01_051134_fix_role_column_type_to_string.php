<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::statement("ALTER TABLE users MODIFY role VARCHAR(255) DEFAULT 'Faculty Member'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert back to the exact ENUM definition it previously had
        DB::statement("ALTER TABLE users MODIFY role ENUM('Admin','Dean','CEIT Official','Chairperson','Program Coordinator','Research Coordinator','Extension Coordinator','GAD Coordinator','Faculty Member') DEFAULT 'Faculty Member'");
    }
};
