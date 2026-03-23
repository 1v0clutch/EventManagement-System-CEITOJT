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
        
        return null;
    }

    /**
     * Get all dashboard data in a single optimized request
     */
    public function index(Request $request)
    {
        try {
            $user = $request->user();
            
            if (!$user) {
                return response()->json(['error' => 'Unauthorized'], 401);
            }
            
            // Get current and next school year
            $now = new \DateTime();
            $currentYear = $now->format('Y');
            $currentMonth = (int)$now->format('m');
            $schoolYear = $currentMonth >= 9 
                ? "{$currentYear}-" . ($currentYear + 1)
                : ($currentYear - 1) . "-{$currentYear}";
            $nextSchoolYear = $currentMonth >= 9
                ? ($currentYear + 1) . "-" . ($currentYear + 2)
                : "{$currentYear}-" . ($currentYear + 1);

            // Fetch events with optimized eager loading - use index on host_id
            // Limit to recent events to avoid huge result sets
            // Include both regular events and personal events
            $events = Event::with([
                    'host:id,name,email',
                    'members:id,name,email',
                    'images:id,event_id,image_path,original_filename,order'
                ])
                ->where('host_id', $user->id)
                ->where('date', '>=', now()->subMonths(3)->format('Y-m-d')) // Only last 3 months
                ->orderBy('date', 'desc')
                ->orderBy('time', 'desc')
                ->limit(100)
                ->get();

            // Get member events separately to avoid expensive orWhereHas
            // Only get non-personal events for member invitations (personal events don't have members)
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
                ->orderBy('date', 'desc')
                ->orderBy('time', 'desc')
                ->limit(100)
                ->get();

            // Merge and deduplicate
            $allEvents = $events->merge($memberEvents)->unique('id');

            // Cache members list for 10 minutes
            $members = Cache::remember('users_list', 600, function () {
                return User::select('id', 'name', 'email', 'role', 'department')
                    ->where('is_validated', true)
                    ->orderBy('name')
                    ->limit(500)
                    ->get();
            });

            // Fetch default events for both school years from default_event_dates table
            $defaultEventDates = \App\Models\DefaultEventDate::with('defaultEvent')
                ->whereIn('school_year', [$schoolYear, $nextSchoolYear])
                ->orderBy('date')
                ->limit(100)
                ->get();

            // Transform events — ensure date is always Y-m-d string
            $transformedEvents = $allEvents->map(function ($event) {
                try {
                    $date = $event->date;
                    if ($date instanceof \DateTime) {
                        $date = $date->format('Y-m-d');
                    } elseif (is_string($date) && strlen($date) > 10) {
                        $date = substr($date, 0, 10);
                    }

                    return [
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
                } catch (\Exception $e) {
                    \Log::error('Error transforming event: ' . $e->getMessage(), ['event_id' => $event->id ?? 'unknown']);
                    return null;
                }
            })->filter(); // Remove null entries

            // Transform default events
            $transformedDefaultEvents = $defaultEventDates->map(function ($eventDate) {
                try {
                    return [
                        'id' => 'default-' . $eventDate->default_event_id,
                        'name' => $eventDate->defaultEvent->name,
                        'date' => $eventDate->date ? $eventDate->date->format('Y-m-d') : null,
                        'end_date' => $eventDate->end_date ? $eventDate->end_date->format('Y-m-d') : null,
                        'school_year' => $eventDate->school_year,
                        'semester' => $eventDate->semester,
                    ];
                } catch (\Exception $e) {
                    \Log::error('Error transforming default event: ' . $e->getMessage(), ['event_date_id' => $eventDate->id ?? 'unknown']);
                    return null;
                }
            })->filter(); // Remove null entries

            // Get current semester
            $currentSemester = $this->getCurrentSemester($now);

            // Fetch ALL user schedules for current and next school year
            // Frontend will filter by semester based on selected date
            $userSchedules = UserSchedule::where('user_id', $user->id)
                ->whereIn('school_year', [$schoolYear, $nextSchoolYear])
                ->select('id', 'day', 'start_time', 'end_time', 'description', 'color', 'semester', 'school_year')
                ->orderBy('semester')
                ->orderBy('day')
                ->orderBy('start_time')
                ->get();

            // Transform user schedules for calendar display with semester filtering
            $transformedSchedules = $userSchedules->map(function ($schedule) use ($currentSemester) {
                try {
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
                        'semester' => $schedule->semester,
                        'school_year' => $schedule->school_year
                    ];
                } catch (\Exception $e) {
                    \Log::error('Error transforming schedule: ' . $e->getMessage(), ['schedule_id' => $schedule->id ?? 'unknown']);
                    return null;
                }
            })->filter(); // Remove null entries

            return response()->json([
                'events' => $transformedEvents->values(),
                'defaultEvents' => $transformedDefaultEvents->values(),
                'userSchedules' => $transformedSchedules->values(),
                'members' => $members,
                'schoolYear' => $schoolYear,
                'nextSchoolYear' => $nextSchoolYear,
            ]);
        } catch (\Exception $e) {
            \Log::error('Dashboard error: ' . $e->getMessage(), [
                'user_id' => $request->user()->id ?? 'unknown',
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json([
                'error' => 'Failed to load dashboard data',
                'message' => config('app.debug') ? $e->getMessage() : 'An error occurred'
            ], 500);
        }
    }
}
