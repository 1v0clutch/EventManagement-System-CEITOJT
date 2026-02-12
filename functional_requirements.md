# Event Management System - Functional Requirements

This document outlines the functional requirements for the Event Management System, detailing the features and capabilities available to users.

## 1. Authentication & Security
- **User Registration**: New users can create an account using a username, email, and password.
- **User Login**: Existing users can securely log in to access the system using their credentials.
- **Session Management**: Secure session handling using JWT (JSON Web Tokens) to maintain user login state.
- **Protected Routing**: Unauthorized users are redirected to the login page when attempting to access internal features.
- **Account Management**: Users can view and manage their profile information.

## 2. Event Management
- **Create Event**: Users (Hosts) can create new events by providing a title, description, location, date, time, and images.
- **Edit Event**: Only the original host of an event can update its details.
- **Delete Event**: Only the original host of an event can permanently remove the event from the system.
- **Image Support**: Events support multiple images for visual representation.
- **Member Invitations**: Hosts can invite specific members to their events.
- **Visibility Control**: 
    - **Private Events**: Visible only to the host and invited members.
    - **Open Events**: Visible to all registered users.

## 3. Dashboard and Visualization
- **Calendar View**: An interactive calendar displaying event indicators on scheduled dates.
- **Date Selection**: Users can click on any date to view a list of events scheduled for that day.
- **Event Details Panel**: A side panel summarizing events for the selected date.
- **Event Modal**: A detailed popup modal for a focused view of event information, including:
    - Event images (full-size or gallery).
    - Title, Date, Time, and Location.
    - Description.
    - Host details and contact information.
    - Participation status (Open/Guest list).
- **Statistics Summary**: Real-time stats on the dashboard showing:
    - Total events available to the user.
    - Number of upcoming events.
    - Total library of system members.

## 4. Availability and Interaction
- **Member Availability**: Real-time status checking showing if invited members are "Available" or "Busy" based on their existing event schedule.
- **Event Participation**: Users can see if they are on the guest list for an event.
- **Navigation**: Seamless navigation between the Dashboard, Add Event, and Account pages.
