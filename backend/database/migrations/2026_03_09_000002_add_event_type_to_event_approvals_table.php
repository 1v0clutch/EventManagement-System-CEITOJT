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
        Schema::table('event_approvals', function (Blueprint $table) {
<<<<<<< HEAD
            $table->string('event_type')->default('event')->after('location');
=======
            $table->enum('event_type', ['event', 'meeting'])->default('event')->after('location');
>>>>>>> 1369ecc084243a8b0b992cae321ce869b016898d
        });
    }

    public function down(): void
    {
        Schema::table('event_approvals', function (Blueprint $table) {
            $table->dropColumn('event_type');
        });
    }
};
