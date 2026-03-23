# Calendar Event Types - Visual Guide

## What You'll See on the Dashboard Calendar

### 🔴 Hosting Event (Red)
```
┌─────────────────┐
│ 15              │
│ ┌─────────────┐ │
│ │ Team Meeting│ │ ← Red background
│ └─────────────┘ │
└─────────────────┘
```
Events where you are the host/organizer

### 🟢 Invited Event (Green)
```
┌─────────────────┐
│ 16              │
│ ┌─────────────┐ │
│ │ Workshop    │ │ ← Green background
│ └─────────────┘ │
└─────────────────┘
```
Events where you are invited as a member

### 🟠 Hosting Meeting (Amber/Dark Orange)
```
┌─────────────────┐
│ 17              │
│ ┌─────────────┐ │
│ │ Client Call │ │ ← Amber background
│ └─────────────┘ │
└─────────────────┘
```
Meetings where you are the host

### 🟡 Invited Meeting (Yellow)
```
┌─────────────────┐
│ 18              │
│ ┌─────────────┐ │
│ │ Standup     │ │ ← Yellow background
│ └─────────────┘ │
└─────────────────┘
```
Meetings where you are invited

### 🟣 Personal Event (Purple) ✅ NOW VISIBLE
```
┌─────────────────┐
│ 19              │
│ ┌─────────────┐ │
│ │ Dentist Appt│ │ ← Purple background
│ └─────────────┘ │
└─────────────────┘
```
Your personal calendar items (only you can see these)

### 🔵 Academic Event (Blue)
```
┌─────────────────┐
│ 20              │
│ ┌─────────────┐ │
│ │ Midterm Exam│ │ ← Blue background
│ └─────────────┘ │
└─────────────────┘
```
Academic calendar events (set by admins)

### 📚 Class Schedule Day (Green Border)
```
┌─────────────────┐
│ 21              │ ← Green border around entire day
│                 │
│                 │
└─────────────────┘
```
Days when you have classes scheduled (based on your weekly schedule)

### ⚠️ Schedule Conflict (Red Warning Icon)
```
┌─────────────────┐
│ 22  ⚠️          │ ← Red warning triangle
│ ┌─────────────┐ │
│ │ Meeting     │ │
│ └─────────────┘ │
│ ┌─────────────┐ │
│ │ Class       │ │
│ └─────────────┘ │
└─────────────────┘
```
Indicates overlapping time slots on the same day

## Event Display Priority

When multiple events exist on the same day, they are sorted by priority:

1. **Hosting Event** (Red) - Highest priority
2. **Invited Event** (Green)
3. **Hosting Meeting** (Amber)
4. **Invited Meeting** (Yellow)
5. **Personal Event** (Purple)
6. **Academic Event** (Blue)
7. **Class Schedule** (Green border) - Lowest priority

Only the first event is shown in the calendar cell. Click "View All" to see all events for that day.

## Legend (Bottom of Calendar)

The calendar includes a legend showing:
- Class Day (green tinted background with border)
- Hosting Event (red square)
- Invited Event (green square)
- Hosting Meeting (amber square)
- Invited Meeting (yellow square)
- Personal Event (purple square) ✅
- Academic Event (blue square)
- Schedule Conflict (red warning triangle)

## Creating Events

### From Dashboard:
- **Personal Button** → Create personal events (purple)
- **Add Event Button** → Create regular events/meetings (red/green/amber/yellow)
- **Academic Button** (Admin only) → Manage academic calendar (blue)

### Event Types:
1. **Event** - General events with members
2. **Meeting** - Specific meeting type
3. **Personal Event** - Private calendar items
4. **Academic Event** - School-wide events
5. **Weekly Schedule** - Recurring class schedule

## What Was Fixed

Before the fix:
- ❌ Personal events were created but not displayed
- ❌ Dashboard API excluded `is_personal = true` events

After the fix:
- ✅ Personal events now appear in purple
- ✅ All event types display correctly
- ✅ Calendar shows complete schedule

## Testing Your Calendar

1. Create a personal event (purple)
2. Create a regular event (red if hosting)
3. Set up your weekly schedule (green borders)
4. Check that all appear on the calendar
5. Click on a date to see all events for that day
