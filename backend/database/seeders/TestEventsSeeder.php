<?php

namespace Database\Seeders;

use App\Models\Event;
use App\Models\User;
use Illuminate\Database\Seeder;

class TestEventsSeeder extends Seeder
{
    public function run(): void
    {
        // Get a host user (admin or first available user)
        $host = User::where('role', 'Admin')->first()
            ?? User::first();

        if (!$host) {
            $this->command->error('No users found. Run TestUsersSeeder first.');
            return;
        }

        $schoolYears = ['2025-2026', '2026-2027'];
        $locations = [
            'CEIT Auditorium',
            'Main Gymnasium',
            'Conference Room A',
            'Conference Room B',
            'DIT Laboratory',
            'DCEA Building',
            'DAFE Hall',
            'DIET Room 101',
            'DCEEE Seminar Room',
            'Open Grounds',
        ];
        $eventTypes = ['event', 'meeting'];

        $events = [
            // September 2025
            ['title' => 'Orientation for New Students', 'description' => 'Welcome orientation for incoming freshmen.', 'date' => '2025-09-05', 'time' => '08:00:00'],
            ['title' => 'Faculty Assembly', 'description' => 'Semestral faculty assembly meeting.', 'date' => '2025-09-10', 'time' => '09:00:00'],
            ['title' => 'Research Colloquium', 'description' => 'Presentation of ongoing research projects.', 'date' => '2025-09-15', 'time' => '13:00:00'],
            ['title' => 'IT Skills Workshop', 'description' => 'Hands-on workshop on modern IT skills.', 'date' => '2025-09-20', 'time' => '10:00:00'],
            ['title' => 'Department Meeting - DIT', 'description' => 'Monthly department meeting for DIT.', 'date' => '2025-09-25', 'time' => '14:00:00'],

            // October 2025
            ['title' => 'Midterm Exam Week Kickoff', 'description' => 'Briefing before midterm examinations.', 'date' => '2025-10-06', 'time' => '08:00:00'],
            ['title' => 'Engineering Week Celebration', 'description' => 'Annual engineering week activities.', 'date' => '2025-10-13', 'time' => '08:00:00'],
            ['title' => 'Seminar on Cybersecurity', 'description' => 'Guest lecture on cybersecurity trends.', 'date' => '2025-10-17', 'time' => '13:00:00'],
            ['title' => 'Sports Fest Opening', 'description' => 'Opening ceremony of the college sports fest.', 'date' => '2025-10-22', 'time' => '07:00:00'],
            ['title' => 'Alumni Homecoming', 'description' => 'Annual alumni homecoming event.', 'date' => '2025-10-29', 'time' => '09:00:00'],

            // November 2025
            ['title' => 'Student Leadership Summit', 'description' => 'Leadership training for student officers.', 'date' => '2025-11-05', 'time' => '08:00:00'],
            ['title' => 'Tech Talk: AI in Engineering', 'description' => 'Talk on artificial intelligence applications.', 'date' => '2025-11-12', 'time' => '13:00:00'],
            ['title' => 'Thesis Defense - Batch 1', 'description' => 'First batch of thesis defenses.', 'date' => '2025-11-18', 'time' => '08:00:00'],
            ['title' => 'Cultural Night', 'description' => 'Annual cultural night celebration.', 'date' => '2025-11-21', 'time' => '17:00:00'],
            ['title' => 'Department Chairpersons Meeting', 'description' => 'Monthly meeting of all department chairs.', 'date' => '2025-11-27', 'time' => '10:00:00'],

            // December 2025
            ['title' => 'Christmas Party - CEIT', 'description' => 'Annual Christmas celebration for CEIT.', 'date' => '2025-12-05', 'time' => '14:00:00'],
            ['title' => 'Thesis Defense - Batch 2', 'description' => 'Second batch of thesis defenses.', 'date' => '2025-12-10', 'time' => '08:00:00'],
            ['title' => 'Year-End Faculty Meeting', 'description' => 'End of year faculty general assembly.', 'date' => '2025-12-15', 'time' => '09:00:00'],

            // January 2026
            ['title' => 'Final Exam Orientation', 'description' => 'Briefing before final examinations.', 'date' => '2026-01-07', 'time' => '08:00:00'],
            ['title' => 'Graduation Rehearsal', 'description' => 'Rehearsal for graduating students.', 'date' => '2026-01-14', 'time' => '08:00:00'],
            ['title' => 'Semestral Break Activities', 'description' => 'Activities during semestral break.', 'date' => '2026-01-21', 'time' => '09:00:00'],
            ['title' => 'Research Proposal Defense', 'description' => 'Defense of research proposals.', 'date' => '2026-01-28', 'time' => '08:00:00'],

            // February 2026
            ['title' => 'Enrollment Assistance Drive', 'description' => 'Faculty assistance during enrollment.', 'date' => '2026-02-04', 'time' => '08:00:00'],
            ['title' => 'Seminar on Sustainable Engineering', 'description' => 'Talk on sustainable engineering practices.', 'date' => '2026-02-11', 'time' => '13:00:00'],
            ['title' => 'Valentine Sports Event', 'description' => 'Fun sports activities for Valentine\'s week.', 'date' => '2026-02-13', 'time' => '07:00:00'],
            ['title' => 'Department Meeting - DCEA', 'description' => 'Monthly meeting for DCEA department.', 'date' => '2026-02-20', 'time' => '14:00:00'],
            ['title' => 'Workshop on Technical Writing', 'description' => 'Workshop for faculty on technical writing.', 'date' => '2026-02-25', 'time' => '09:00:00'],

            // March 2026
            ['title' => 'College Academic Council Meeting', 'description' => 'Quarterly academic council meeting.', 'date' => '2026-03-04', 'time' => '09:00:00'],
            ['title' => 'Capstone Project Exhibit', 'description' => 'Exhibition of student capstone projects.', 'date' => '2026-03-11', 'time' => '08:00:00'],
            ['title' => 'Faculty Development Training', 'description' => 'Training program for faculty development.', 'date' => '2026-03-18', 'time' => '08:00:00'],
            ['title' => 'Inter-Department Quiz Bee', 'description' => 'Academic quiz competition between departments.', 'date' => '2026-03-25', 'time' => '13:00:00'],

            // April 2026
            ['title' => 'Midterm Exam Briefing', 'description' => 'Briefing before 2nd semester midterms.', 'date' => '2026-04-01', 'time' => '08:00:00'],
            ['title' => 'U-Games Preparation', 'description' => 'Preparation activities for U-Games.', 'date' => '2026-04-08', 'time' => '07:00:00'],
            ['title' => 'Seminar on IoT Applications', 'description' => 'Seminar on Internet of Things.', 'date' => '2026-04-15', 'time' => '13:00:00'],
            ['title' => 'Student Council Elections', 'description' => 'Annual student council elections.', 'date' => '2026-04-22', 'time' => '08:00:00'],
            ['title' => 'Earth Day Activities', 'description' => 'Environmental awareness activities.', 'date' => '2026-04-22', 'time' => '07:00:00'],

            // May 2026
            ['title' => 'Thesis Defense - Final Batch', 'description' => 'Final batch of thesis defenses.', 'date' => '2026-05-06', 'time' => '08:00:00'],
            ['title' => 'Graduation Ceremony', 'description' => 'Commencement exercises for graduating students.', 'date' => '2026-05-13', 'time' => '08:00:00'],
            ['title' => 'Faculty End-of-Year Celebration', 'description' => 'End of academic year celebration.', 'date' => '2026-05-20', 'time' => '14:00:00'],
            ['title' => 'Summer Program Kickoff', 'description' => 'Opening of summer academic programs.', 'date' => '2026-05-27', 'time' => '08:00:00'],

            // June 2026
            ['title' => 'Final Exam Week - 2nd Sem', 'description' => 'Final examinations for 2nd semester.', 'date' => '2026-06-03', 'time' => '08:00:00'],
            ['title' => 'Grade Submission Deadline Briefing', 'description' => 'Reminder for grade submission.', 'date' => '2026-06-10', 'time' => '09:00:00'],
            ['title' => 'Vacation Kickoff Party', 'description' => 'Celebration for start of summer vacation.', 'date' => '2026-06-17', 'time' => '14:00:00'],

            // July 2026
            ['title' => 'Mid-Year Enrollment', 'description' => 'Enrollment for mid-year semester.', 'date' => '2026-07-01', 'time' => '08:00:00'],
            ['title' => 'Mid-Year Orientation', 'description' => 'Orientation for mid-year students.', 'date' => '2026-07-08', 'time' => '08:00:00'],
            ['title' => 'Workshop on Data Science', 'description' => 'Hands-on data science workshop.', 'date' => '2026-07-15', 'time' => '13:00:00'],
            ['title' => 'Mid-Year Sports Day', 'description' => 'Sports activities for mid-year students.', 'date' => '2026-07-22', 'time' => '07:00:00'],

            // August 2026
            ['title' => 'Mid-Year Final Exams', 'description' => 'Final examinations for mid-year semester.', 'date' => '2026-08-05', 'time' => '08:00:00'],
            ['title' => 'National Heroes Day Program', 'description' => 'Program for National Heroes Day.', 'date' => '2026-08-31', 'time' => '08:00:00'],
        ];

        $created = 0;

        foreach ($events as $index => $eventData) {
            $date = $eventData['date'];
            $year = (int) substr($date, 0, 4);
            $month = (int) substr($date, 5, 2);

            // Determine school year based on date
            $schoolYear = ($month >= 9) ? "{$year}-" . ($year + 1) : ($year - 1) . "-{$year}";

            Event::create([
                'title' => $eventData['title'],
                'description' => $eventData['description'],
                'location' => $locations[$index % count($locations)],
                'date' => $date,
                'time' => $eventData['time'],
                'school_year' => $schoolYear,
                'host_id' => $host->id,
                'event_type' => $eventTypes[$index % count($eventTypes)],
                'is_personal' => false,
            ]);

            $created++;
        }

        $this->command->info("\n✅ TestEventsSeeder: {$created} events created successfully!\n");
    }
}
