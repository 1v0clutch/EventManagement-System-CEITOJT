<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * This migration creates a dedicated table for user-created academic events.
     * These events are different from default_events (system templates) and should
     * be isolated to specific academic years and semesters.
     */
    public function up(): void
    {
        Schema::create('created_academic_events', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->integer('month'); // 1-12
            $table->integer('semester'); // 1 (First), 2 (Second), 3 (Mid-Year)
            $table->string('school_year'); // e.g., "2025-2026"
            $table->date('date')->nullable();
            $table->date('end_date')->nullable();
            $table->foreignId('created_by')->constrained('users')->onDelete('cascade');
            $table->integer('order')->default(0); // For ordering within the month
            $table->timestamps();

            // Ensure unique event names per month, semester, and school year
            $table->unique(['name', 'month', 'semester', 'school_year'], 'unique_created_academic_event');
            
            // Index for faster queries
            $table->index(['school_year', 'semester', 'month']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('created_academic_events');
    }
};
