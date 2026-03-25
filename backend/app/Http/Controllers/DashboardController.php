<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\DefaultEvent;
use App\Models\User;
use App\Models\UserSchedule;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class DashboardController extends Controller
{
    /**
     * Determine the current semester based on the date
<<<<<<< HEAD
     */
    private function getCurrentSemester(\DateTime $date): ?string
    {
        $month = (int)$date->format('m');

        if ($month >= 9 || $month <= 1) return 'first';
        if ($month >= 2 && $month <= 6)  return 'second';
        if ($month >= 7 && $month <= 8)  return 'midyear';

=======
     * 
     * @param \DateTime $date
     * @return string|null
     */
    private function getCurrentSemester(\DateTime $date)
    {
        $month = (int)$date->format('m');
        
        // First Semester: September (9) to January (1)
        if ($month >= 9 || $month <= 1) {
            return 'first';
        }
        
        // Second Semester: February (2) to June (6)
        if ($month >= 2 && $month <= 6) {
            return 'second';
        }
        
        // Mid-Year/Summer: July (7) to August (8)
        if ($month >= 7 && $month <= 8) {
            return 'midyear';
        }
        
>>>>>>> 1369ecc084243a8b0b992cae321ce869b016898d
        return null;
    }

    /**
<<<<<<< HEAD
=======
     * Check if a given date falls within the current semester
     * 
     * @param \DateTime $checkDate
     * @param string $currentSemester
     * @return bool
     */
    private function isDateInCurrentSemester(\DateTime $checkDate, string $currentSemester)
    {
        $month = (int)$checkDate->format('m');
        
        switch ($currentSemester) {
            case 'first':
                return $month >= 9 || $month <= 1;
            case 'second':
                return $month >= 2 && $month <= 6;
            case 'midyear':
                return $month >= 7 && $month <= 8;
            default:
                return false;
        }
    }

    /**
>>>>>>> 1369ecc084243a8b0b992cae321ce869b016898d
     * Get all dashboard data in a single optimized request
     */
    public function index(Request $request)
    {
        $user = $request->user();
<<<<<<< HEAD

        // School year calculation
        $now          = new \DateTime();
        $currentYear  = (int)$now->format('Y');
        $currentMonth = (int)$now->format('m');

        $schoolYear = $currentMonth >= 9
            ? "{$currentYear}-" . ($currentYear + 1)
            : ($currentYear - 1) . "-{$currentYear}";

=======
        
        // Get current and next school year
        $now = new \DateTime();
        $currentYear = $now->format('Y');
        $currentMonth = (int)$now->format('m');
        $schoolYear = $currentMonth >= 9 
            ? "{$currentYear}-" . ($currentYear + 1)
            : ($currentYear - 1) . "-{$currentYear}";
>>>>>>> 1369ecc084243a8b0b992cae321ce869b016898d
        $nextSchoolYear = $currentMonth >= 9
            ? ($currentYear + 1) . "-" . ($currentYear + 2)
            : "{$currentYear}-" . ($currentYear + 1);

<<<<<<< HEAD
        // ── Events hosted by the user ──────────────────────────────────────
        $events = Event::with([
                'host:id,name,email',
                'members:id,name,email',
                'images:id,event_id,image_path,original_filename,order',
            ])
            ->where('host_id', $user->id)
            ->where('date', '>=', now()->subMonths(3)->format('Y-m-d'))
=======
        // Fetch events with optimized eager loading - use index on host_id
        // Limit to recent events to avoid huge result sets
        $events = Event::with([
                'host:id,name,email',
                'members:id,name,email',
                'images:id,event_id,image_path,original_filename,order'
            ])
            ->where('host_id', $user->id)
            ->where('is_personal', false)
            ->where('date', '>=', now()->subMonths(3)->format('Y-m-d')) // Only last 3 months
>>>>>>> 1369ecc084243a8b0b992cae321ce869b016898d
            ->orderBy('date', 'desc')
            ->orderBy('time', 'desc')
            ->limit(100)
            ->get();

<<<<<<< HEAD
        // ── Events where the user is a member (non-personal only) ──────────
        $memberEvents = Event::with([
                'host:id,name,email',
                'members:id,name,email',
                'images:id,event_id,image_path,original_filename,order',
            ])
            ->whereHas('members', fn($q) => $q->where('users.id', $user->id))
            ->where('is_personal', false)
            ->where('date', '>=', now()->subMonths(3)->format('Y-m-d'))
=======
        // Get member events separately to avoid expensive orWhereHas
        $memberEvents = Event::with([
                'host:id,name,email',
                'members:id,name,email',
                'images:id,event_id,image_path,original_filename,order'
            ])
            ->whereHas('members', function ($q) use ($user) {
                $q->where('users.id', $user->id);
            })
            ->where('is_personal', false)
            ->where('date', '>=', now()->subMonths(3)->format('Y-m-d')) // Only last 3 months
>>>>>>> 1369ecc084243a8b0b992cae321ce869b016898d
            ->orderBy('date', 'desc')
            ->orderBy('time', 'desc')
            ->limit(100)
            ->get();

<<<<<<< HEAD
        $allEvents = $events->merge($memberEvents)->unique('id');

        // ── Members list (cached) ──────────────────────────────────────────
=======
        // Merge and deduplicate
        $allEvents = $events->merge($memberEvents)->unique('id');

        // Cache members list for 10 minutes
>>>>>>> 1369ecc084243a8b0b992cae321ce869b016898d
        $members = Cache::remember('users_list', 600, function () {
            return User::select('id', 'name', 'email', 'role', 'department')
                ->where('is_validated', true)
                ->orderBy('name')
                ->limit(500)
                ->get();
        });

<<<<<<< HEAD
        // ── Default / academic events ──────────────────────────────────────
        $defaultEvents = DefaultEvent::whereNotNull('date')
            ->where(function ($q) use ($schoolYear, $nextSchoolYear) {
                $q->whereIn('school_year', [$schoolYear, $nextSchoolYear])
                  ->orWhereNull('school_year');
=======
        // Fetch default events for both school years
        $defaultEvents = DefaultEvent::whereNotNull('date')
            ->where(function ($query) use ($schoolYear, $nextSchoolYear) {
                $query->whereIn('school_year', [$schoolYear, $nextSchoolYear])
                    ->orWhereNull('school_year');
>>>>>>> 1369ecc084243a8b0b992cae321ce869b016898d
            })
            ->orderBy('date')
            ->limit(100)
            ->get();

<<<<<<< HEAD
        // ── Transform regular events ───────────────────────────────────────
=======
        // Transform events — ensure date is always Y-m-d string
>>>>>>> 1369ecc084243a8b0b992cae321ce869b016898d
        $transformedEvents = $allEvents->map(function ($event) {
            $date = $event->date;
            if ($date instanceof \DateTime) {
                $date = $date->format('Y-m-d');
            } elseif (is_string($date) && strlen($date) > 10) {
                $date = substr($date, 0, 10);
            }

            return [
<<<<<<< HEAD
                'id'          => $event->id,
                'title'       => $event->title,
                'description' => $event->description,
                'location'    => $event->location,
                'event_type'  => $event->event_type ?? 'event',
                'images'      => $event->images->map(fn($img) => [
                    'url'               => asset('storage/' . $img->image_path),
                    'original_filename' => $img->original_filename,
                ]),
                'date'        => $date,
                'time'        => $event->time,
                'school_year' => $event->school_year,
                'host'        => [
                    'id'       => $event->host->id,
                    'username' => $event->host->name,
                    'email'    => $event->host->email,
                ],
                'members'          => $event->members->map(fn($m) => [
                    'id'       => $m->id,
                    'username' => $m->name,
                    'email'    => $m->email,
                    'status'   => $m->pivot->status,
                ]),
                'is_default_event' => false,
                'is_personal'      => $event->is_personal ?? false,
                'personal_color'   => $event->personal_color,
            ];
        });

        // ── Transform default events ───────────────────────────────────────
        $transformedDefaultEvents = $defaultEvents->map(fn($event) => [
            'id'          => 'default-' . $event->id,
            'name'        => $event->name,
            'date'        => $event->date ? $event->date->format('Y-m-d') : null,
            'end_date'    => $event->end_date ? $event->end_date->format('Y-m-d') : null,
            'school_year' => $event->school_year,
        ]);

        // ── User schedules (current + next school year) ────────────────────
        $currentSemester = $this->getCurrentSemester($now);

        $userSchedules = UserSchedule::where('user_id', $user->id)
            ->whereIn('school_year', [$schoolYear, $nextSchoolYear])
            ->select('id', 'day', 'start_time', 'end_time', 'description', 'color', 'semester', 'school_year')
            ->orderBy('semester')
=======
                'id' => $event->id,
                'title' => $event->title,
                'description' => $event->description,
                'location' => $event->location,
                'event_type' => $event->event_type ?? 'event',
                'images' => $event->images->map(fn($img) => [
                    'url' => asset('storage/' . $img->image_path),
                    'original_filename' => $img->original_filename,
                ]),
                'date' => $date,
                'time' => $event->time,
                'school_year' => $event->school_year,
                'host' => [
                    'id' => $event->host->id,
                    'username' => $event->host->name,
                    'email' => $event->host->email,
                ],
                'members' => $event->members->map(fn($m) => [
                    'id' => $m->id,
                    'username' => $m->name,
                    'email' => $m->email,
                    'status' => $m->pivot->status,
                ]),
                'is_default_event' => false,
                'is_personal' => $event->is_personal ?? false,
                'personal_color' => $event->personal_color,
            ];
        });

        // Transform default events
        $transformedDefaultEvents = $defaultEvents->map(function ($event) {
            return [
                'id' => 'default-' . $event->id,
                'name' => $event->name,
                'date' => $event->date ? $event->date->format('Y-m-d') : null,
                'end_date' => $event->end_date ? $event->end_date->format('Y-m-d') : null,
                'school_year' => $event->school_year,
            ];
        });

        // Get current semester
        $currentSemester = $this->getCurrentSemester($now);

        // Fetch user schedules
        $userSchedules = UserSchedule::where('user_id', $user->id)
            ->select('id', 'day', 'start_time', 'end_time', 'description', 'color')
>>>>>>> 1369ecc084243a8b0b992cae321ce869b016898d
            ->orderBy('day')
            ->orderBy('start_time')
            ->get();

<<<<<<< HEAD
        $transformedSchedules = $userSchedules->map(function ($schedule) use ($currentSemester) {
            $startTime = substr($schedule->start_time, 0, 5);
            $endTime   = substr($schedule->end_time,   0, 5);

            return [
                'id'          => 'schedule-' . $schedule->id,
                'title'       => $schedule->description ?: 'Class',
                'description' => $schedule->description,
                'day'         => $schedule->day,
                'start_time'  => $startTime,
                'end_time'    => $endTime,
                'time'        => $startTime . ' - ' . $endTime,
                'color'       => $schedule->color,
                'is_schedule' => true,
                'type'        => 'schedule',
                'semester'    => $schedule->semester,
                'school_year' => $schedule->school_year,
=======
        // Transform user schedules for calendar display with semester filtering
        $transformedSchedules = $userSchedules->map(function ($schedule) use ($currentSemester) {
            // Format time to HH:MM (remove seconds if present)
            $startTime = substr($schedule->start_time, 0, 5);
            $endTime = substr($schedule->end_time, 0, 5);
            
            return [
                'id' => 'schedule-' . $schedule->id,
                'title' => $schedule->description ?: 'Class',
                'description' => $schedule->description,
                'day' => $schedule->day,
                'start_time' => $startTime,
                'end_time' => $endTime,
                'time' => $startTime . ' - ' . $endTime,
                'color' => $schedule->color,
                'is_schedule' => true,
                'type' => 'schedule',
                'semester' => $currentSemester
>>>>>>> 1369ecc084243a8b0b992cae321ce869b016898d
            ];
        });

        return response()->json([
<<<<<<< HEAD
            'events'        => $transformedEvents,
            'defaultEvents' => $transformedDefaultEvents,
            'userSchedules' => $transformedSchedules,
            'members'       => $members,
            'schoolYear'    => $schoolYear,
=======
            'events' => $transformedEvents,
            'defaultEvents' => $transformedDefaultEvents,
            'userSchedules' => $transformedSchedules,
            'members' => $members,
            'schoolYear' => $schoolYear,
>>>>>>> 1369ecc084243a8b0b992cae321ce869b016898d
            'nextSchoolYear' => $nextSchoolYear,
        ]);
    }
}
