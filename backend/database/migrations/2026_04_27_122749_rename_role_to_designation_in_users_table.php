<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (DB::getDriverName() !== 'mysql') {
            // For non-MySQL (e.g. SQLite in tests), use schema builder
            \Illuminate\Support\Facades\Schema::table('users', function ($table) {
                $table->renameColumn('role', 'designation');
            });
            return;
        }

        // Get current column definition and rename via raw SQL to avoid Doctrine DBAL double-quote bug
        DB::statement("ALTER TABLE users CHANGE `role` `designation` VARCHAR(255) NOT NULL DEFAULT 'Faculty Member'");
    }

    public function down(): void
    {
        if (DB::getDriverName() !== 'mysql') {
            \Illuminate\Support\Facades\Schema::table('users', function ($table) {
                $table->renameColumn('designation', 'role');
            });
            return;
        }

        DB::statement("ALTER TABLE users CHANGE `designation` `role` VARCHAR(255) NOT NULL DEFAULT 'Faculty Member'");
    }
};
