<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM(
            'Admin',
            'Dean',
            'CEIT Official',
            'Chairperson',
            'Research Coordinator',
            'Extension Coordinator',
            'Department Research Coordinator',
            'Department Extension Coordinator',
            'Faculty Member',
            'Staff'
        ) NOT NULL DEFAULT 'Faculty Member'");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM(
            'Admin',
            'Dean',
            'CEIT Official',
            'Chairperson',
            'Research Coordinator',
            'Extension Coordinator',
            'Faculty Member',
            'Staff'
        ) NOT NULL DEFAULT 'Faculty Member'");
    }
};
