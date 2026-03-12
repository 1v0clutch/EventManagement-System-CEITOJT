<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\DefaultEvent;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class DashboardController extends Controller
{
    /**
     * Get all dashboard data in a single optimized request
     */
    public function index(Request $request)
    {
        $user = $request->user();
        
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

        // Fetch events with optimized eager loading
        $events = Event::with([
                'host:id,name,email',
                'members:id,name,email',
                'images:id,event_id,image_path,original_filename,order'
            ])
            ->withCount([
                'rescheduleRequests as has_pending_reschedule_requests' => function ($query) {
                    $query->where('status', 'pending');
                }
            ])
            ->where(function ($query) use ($user) {
                $query->where('host_id', $user->id)
                    ->orWhereHas('members', function ($q) use ($user) {
                        $q->where('users.id', $user->id);
                    });
            })
            ->where(function ($query) use ($user) {
                $query->where('is_personal', false)
                    ->orWhere(function ($q) use ($user) {
                        $q->where('is_personal', true)->where('host_id', $user->id);
                    });
            })
            ->orderBy('date')
            ->orderBy('time')
            ->get();

        // Cache members list for 5 minutes
        $members = Cache::remember('users_list', 300, function () {
            return User::select('id', 'name', 'email', 'role', 'department')
                ->where('is_validated', true)
                ->orderBy('name')
                ->get();
        });

        // Fetch default events for both school years
        $defaultEvents = DefaultEvent::whereNotNull('date')
            ->where(function ($query) use ($schoolYear, $nextSchoolYear) {
                $query->whereIn('school_year', [$schoolYear, $nextSchoolYear])
                    ->orWhereNull('school_year');
            })
            ->orderBy('date')
            ->get();

        // Transform events
        $transformedEvents = $events->map(function ($event) {
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
                'date' => $event->date,
                'time' => $event->time,
                'school_year' => $event->school_year,
                'has_pending_reschedule_requests' => $event->has_pending_reschedule_requests > 0,
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

        return response()->json([
            'events' => $transformedEvents,
            'defaultEvents' => $transformedDefaultEvents,
            'members' => $members,
            'schoolYear' => $schoolYear,
            'nextSchoolYear' => $nextSchoolYear,
        ]);
    }
}
