<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Move created default events (those with dates and school_year set) 
     * from default_events table to default_event_dates table.
     */
    public function up(): void
    {
        // First, ensure default_event_dates table has semester column
        if (!Schema::hasColumn('default_event_dates', 'semester')) {
            Schema::table('default_event_dates', function (Blueprint $table) {
                $table->integer('semester')->after('school_year')->nullable();
            });
        }

        // Get all default_events that have dates and school_year set (these are created events)
        $createdEvents = DB::table('default_events')
            ->whereNotNull('date')
            ->whereNotNull('school_year')
            ->get();

        echo "Found " . $createdEvents->count() . " created events to migrate\n";

        foreach ($createdEvents as $event) {
            // Determine semester from month
            $semester = $this->getSemesterFromMonth($event->month);
            
            // Check if this event already exists in default_event_dates
            $exists = DB::table('default_event_dates')
                ->where('default_event_id', $event->id)
                ->where('school_year', $event->school_year)
                ->exists();

            if (!$exists) {
                // Insert into default_event_dates
                DB::table('default_event_dates')->insert([
                    'default_event_id' => $event->id,
                    'school_year' => $event->school_year,
                    'semester' => $semester,
                    'date' => $event->date,
                    'end_date' => $event->end_date,
                    'month' => $event->month,
                    'created_by' => null, // We don't have this info in old structure
                    'created_at' => $event->created_at ?? now(),
                    'updated_at' => $event->updated_at ?? now(),
                ]);

                echo "Migrated: {$event->name} ({$event->school_year})\n";
            }
        }

        // Now remove the date, end_date, and school_year from default_events
        // to convert them back to templates
        DB::table('default_events')
            ->whereNotNull('date')
            ->update([
                'date' => null,
                'end_date' => null,
                'school_year' => null,
            ]);

        echo "Migration complete!\n";
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Move data back from default_event_dates to default_events
        $eventDates = DB::table('default_event_dates')->get();

        foreach ($eventDates as $eventDate) {
            DB::table('default_events')
                ->where('id', $eventDate->default_event_id)
                ->update([
                    'date' => $eventDate->date,
                    'end_date' => $eventDate->end_date,
                    'school_year' => $eventDate->school_year,
                ]);
        }

        // Delete from default_event_dates
        DB::table('default_event_dates')->truncate();
    }

    /**
     * Determine semester from month
     */
    private function getSemesterFromMonth(int $month): int
    {
        if (in_array($month, [9, 10, 11, 12, 1])) {
            return 1; // First Semester
        }
        if (in_array($month, [2, 3, 4, 5, 6])) {
            return 2; // Second Semester
        }
        return 3; // Mid-Year (July, August)
    }
};
