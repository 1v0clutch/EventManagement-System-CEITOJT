<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

<<<<<<< HEAD
return new class extends Migration 
=======
return new class extends Migration
>>>>>>> 1369ecc084243a8b0b992cae321ce869b016898d
{
    public function up(): void
    {
        if (!Schema::hasColumn('event_user', 'status')) {
            Schema::table('event_user', function (Blueprint $table) {
<<<<<<< HEAD
                $table->string('status')->default('pending')->after('user_id');
=======
                $table->enum('status', ['pending', 'accepted', 'declined'])->default('pending')->after('user_id');
>>>>>>> 1369ecc084243a8b0b992cae321ce869b016898d
            });
        }
    }

    public function down(): void
    {
        Schema::table('event_user', function (Blueprint $table) {
            $table->dropColumn('status');
        });
    }
};
