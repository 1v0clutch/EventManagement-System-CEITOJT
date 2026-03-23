<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Add semester field to default_event_dates table to track which semester
     * each created default academic event belongs to.
     */
    public function up(): void
    {
        Schema::table('default_event_dates', function (Blueprint $table) {
            // Add semester field (1 = First Semester, 2 = Second Semester, 3 = Mid-Year)
            $table->integer('semester')->after('school_year')->nullable();
            
            // Add index for quick filtering by semester
            $table->index(['school_year', 'semester']);
        });
        
        // Update existing records to set semester based on month
        DB::statement("
            UPDATE default_event_dates 
            SET semester = CASE 
                WHEN month IN (9, 10, 11, 12, 1) THEN 1
                WHEN month IN (2, 3, 4, 5, 6) THEN 2
                WHEN month IN (7, 8) THEN 3
                ELSE 1
            END
        ");
        
        // Make semester required after populating existing data
        Schema::table('default_event_dates', function (Blueprint $table) {
            $table->integer('semester')->nullable(false)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('default_event_dates', function (Blueprint $table) {
            $table->dropIndex(['school_year', 'semester']);
            $table->dropColumn('semester');
        });
    }
};
