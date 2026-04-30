<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->json('designations')->nullable()->after('designation');
        });

        // Migrate existing single designation into the new JSON array column
        DB::statement("UPDATE users SET designations = JSON_ARRAY(designation) WHERE designation IS NOT NULL AND designation != ''");
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('designations');
        });
    }
};
