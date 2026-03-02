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
            // Track individual approvals from Dean and Chairperson
            $table->foreignId('dean_approved_by')->nullable()->after('reviewed_at')->constrained('users')->onDelete('set null');
            $table->timestamp('dean_approved_at')->nullable()->after('dean_approved_by');
            $table->foreignId('chair_approved_by')->nullable()->after('dean_approved_at')->constrained('users')->onDelete('set null');
            $table->timestamp('chair_approved_at')->nullable()->after('chair_approved_by');
            
            // Track required approvers (JSON array of user IDs)
            $table->json('required_approvers')->nullable()->after('chair_approved_at');
            
            // Track if all required approvals are received
            $table->boolean('all_approvals_received')->default(false)->after('required_approvers');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('event_requests', function (Blueprint $table) {
            $table->dropForeign(['dean_approved_by']);
            $table->dropForeign(['chair_approved_by']);
            $table->dropColumn([
                'dean_approved_by',
                'dean_approved_at',
                'chair_approved_by',
                'chair_approved_at',
                'required_approvers',
                'all_approvals_received'
            ]);
        });
    }
};
