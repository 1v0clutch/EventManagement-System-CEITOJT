<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Migrate existing single string values to JSON arrays
        DB::statement("UPDATE users SET ceit_officer_type = JSON_ARRAY(ceit_officer_type) WHERE ceit_officer_type IS NOT NULL AND ceit_officer_type != '' AND ceit_officer_type NOT LIKE '[%'");

        Schema::table('users', function (Blueprint $table) {
            $table->json('ceit_officer_type')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('ceit_officer_type')->nullable()->change();
        });
    }
};
