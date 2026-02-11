<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\User;
use Illuminate\Http\Request;

class EventController extends Controller
{
    public function index()
    {
        $events = Event::with(['host', 'members', 'images'])->orderBy('date')->orderBy('time')->get();

        return response()->json([
            'events' => $events->map(function ($event) {
                return [
                    'id' => $event->id,
                    'title' => $event->title,
                    'description' => $event->description,
                    'location' => $event->location,
                    'images' => $event->images->map(fn($img) => asset('storage/' . $img->image_path)),
                    'date' => $event->date,
                    'time' => $event->time,
                    'is_open' => $event->is_open,
                    'host' => [
                        'id' => $event->host->id,
                        'username' => $event->host->name,
                        'email' => $event->host->email,
                    ],
                    'members' => $event->members->map(fn($m) => [
                        'id' => $m->id,
                        'username' => $m->name,
                        'email' => $m->email,
                    ]),
                ];
            }),
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'location' => 'required|string|max:255',
            'images.*' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'date' => 'required|date',
            'time' => 'required',
            'member_ids' => 'nullable|array',
            'is_open' => 'boolean',
        ]);

        $event = Event::create([
            'title' => $request->title,
            'description' => $request->description,
            'location' => $request->location,
            'date' => $request->date,
            'time' => $request->time,
            'is_open' => $request->is_open ?? false,
            'host_id' => $request->user()->id,
        ]);

        // Handle multiple images
        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $index => $image) {
                $imagePath = $image->store('events', 'public');
                $event->images()->create([
                    'image_path' => $imagePath,
                    'order' => $index,
                ]);
            }
        }

        if ($request->member_ids) {
            $event->members()->attach($request->member_ids);
        }

        $event->load(['host', 'members', 'images']);

        return response()->json([
            'message' => 'Event created successfully',
            'event' => $event,
        ], 201);
    }

    public function update(Request $request, Event $event)
    {
        if ($event->host_id !== $request->user()->id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $request->validate([
            'title' => 'sometimes|required|string|max:255',
            'description' => 'nullable|string',
            'location' => 'sometimes|required|string|max:255',
            'images.*' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'date' => 'sometimes|required|date',
            'time' => 'sometimes|required',
            'member_ids' => 'nullable|array',
            'is_open' => 'boolean',
        ]);

        $event->update($request->only(['title', 'description', 'location', 'date', 'time', 'is_open']));

        // Handle new images
        if ($request->hasFile('images')) {
            // Delete old images
            foreach ($event->images as $oldImage) {
                \Storage::disk('public')->delete($oldImage->image_path);
                $oldImage->delete();
            }

            // Add new images
            foreach ($request->file('images') as $index => $image) {
                $imagePath = $image->store('events', 'public');
                $event->images()->create([
                    'image_path' => $imagePath,
                    'order' => $index,
                ]);
            }
        }

        if ($request->has('member_ids')) {
            $event->members()->sync($request->member_ids);
        }

        return response()->json(['message' => 'Event updated successfully', 'event' => $event->load(['host', 'members', 'images'])]);
    }

    public function destroy(Request $request, Event $event)
    {
        if ($event->host_id !== $request->user()->id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $event->delete();
        return response()->json(['message' => 'Event deleted successfully']);
    }

    public function availability(Event $event, User $user)
    {
        return response()->json(['available' => true]);
    }
}
