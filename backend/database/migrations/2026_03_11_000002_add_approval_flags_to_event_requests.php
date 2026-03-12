<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('event_requests', function (Blueprint $table) {
            // Add flags to indicate which approvals are required
            $table->boolean('requires_dean_approval')->default(false)->after('all_approvals_received');
            $table->boolean('requires_chair_approval')->default(false)->after('requires_dean_approval');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('event_requests', function (Blueprint $table) {
            $table->dropColumn([
                'requires_dean_approval',
                'requires_chair_approval',
            ]);
        });
    }
};
