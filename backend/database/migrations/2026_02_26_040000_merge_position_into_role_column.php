<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

<<<<<<< HEAD
return new class extends Migration 
=======
return new class extends Migration
>>>>>>> 1369ecc084243a8b0b992cae321ce869b016898d
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Check if position column exists before trying to merge
        if (Schema::hasColumn('users', 'position')) {
            // First, update the role column to use the position values where position is not null
            DB::statement("UPDATE users SET role = position WHERE position IS NOT NULL");
<<<<<<< HEAD

=======
            
>>>>>>> 1369ecc084243a8b0b992cae321ce869b016898d
            // Drop the position column
            Schema::table('users', function (Blueprint $table) {
                $table->dropColumn('position');
            });
        }
<<<<<<< HEAD

        // Now modify the role column to include all the position enum values
        Schema::table('users', function (Blueprint $table) {
            // Change role column to string to support all position values (compatible with pgsql)
            $table->string('role')
                ->default('Faculty Member')
                ->change();
=======
        
        // Now modify the role column to include all the position enum values
        Schema::table('users', function (Blueprint $table) {
            // Change role column to include all position values
            $table->enum('role', ['Admin', 'Dean', 'Chairperson', 'Coordinator', 'Faculty Member'])
                  ->default('Faculty Member')
                  ->change();
>>>>>>> 1369ecc084243a8b0b992cae321ce869b016898d
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Add back the position column
        Schema::table('users', function (Blueprint $table) {
            $table->enum('position', ['Admin', 'Dean', 'Chairperson', 'Coordinator', 'Faculty Member'])
<<<<<<< HEAD
                ->nullable()
                ->after('role');
        });

        // Copy role values to position column
        DB::statement("UPDATE users SET position = role WHERE role IN ('Admin', 'Dean', 'Chairperson', 'Coordinator', 'Faculty Member')");

        // Revert role column to original values
        Schema::table('users', function (Blueprint $table) {
            $table->string('role')
                ->default('teacher')
                ->change();
        });

=======
                  ->nullable()
                  ->after('role');
        });
        
        // Copy role values to position column
        DB::statement("UPDATE users SET position = role WHERE role IN ('Admin', 'Dean', 'Chairperson', 'Coordinator', 'Faculty Member')");
        
        // Revert role column to original values
        Schema::table('users', function (Blueprint $table) {
            $table->enum('role', ['admin', 'teacher'])
                  ->default('teacher')
                  ->change();
        });
        
>>>>>>> 1369ecc084243a8b0b992cae321ce869b016898d
        // Set role back to 'admin' for Admin position, 'teacher' for others
        DB::statement("UPDATE users SET role = 'admin' WHERE position = 'Admin'");
        DB::statement("UPDATE users SET role = 'teacher' WHERE position != 'Admin'");
    }
};