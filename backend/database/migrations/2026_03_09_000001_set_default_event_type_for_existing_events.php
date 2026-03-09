<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Update all existing events that have NULL event_type to 'event'
        DB::table('events')
            ->whereNull('event_type')
            ->update(['event_type' => 'event']);
    }

    public function down(): void
    {
        // No need to revert as we're just setting default values
    }
};
