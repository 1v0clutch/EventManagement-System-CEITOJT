<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Railway DB uses 'designation' column — handle both column names gracefully
        $columns = DB::select("SHOW COLUMNS FROM users");
        $colNames = array_map(fn($c) => $c->Field, $columns);

        $col = in_array('designation', $colNames) ? 'designation' : 'role';

        // Migrate old values to new ones
        DB::statement("UPDATE users SET `{$col}` = 'Department Research Coordinator' WHERE `{$col}` IN ('Research Coordinator', 'Program Coordinator', 'Coordinator')");
        DB::statement("UPDATE users SET `{$col}` = 'Department Extension Coordinator' WHERE `{$col}` IN ('Extension Coordinator', 'GAD Coordinator')");
        DB::statement("UPDATE users SET `{$col}` = 'Faculty Member' WHERE `{$col}` = 'Staff'");

        // Widen the column to TEXT first so we can safely change the ENUM
        DB::statement("ALTER TABLE users MODIFY COLUMN `{$col}` TEXT NOT NULL");

        // Re-apply as a clean ENUM with only valid values
        DB::statement("ALTER TABLE users MODIFY COLUMN `{$col}` ENUM(
            'Admin',
            'Dean',
            'CEIT Official',
            'Chairperson',
            'Department Research Coordinator',
            'Department Extension Coordinator',
            'Faculty Member'
        ) NOT NULL DEFAULT 'Faculty Member'");
    }

    public function down(): void
    {
        $columns = DB::select("SHOW COLUMNS FROM users");
        $colNames = array_map(fn($c) => $c->Field, $columns);
        $col = in_array('designation', $colNames) ? 'designation' : 'role';

        DB::statement("ALTER TABLE users MODIFY COLUMN `{$col}` ENUM(
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
};
