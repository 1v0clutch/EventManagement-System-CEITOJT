<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Keeping Coordinator in the enum — the rest of the codebase still uses it.
     */
    public function up(): void
    {
        // no-op: Coordinator role is still used throughout the codebase
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // no-op
    }
};
