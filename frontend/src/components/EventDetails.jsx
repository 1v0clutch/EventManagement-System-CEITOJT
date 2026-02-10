export default function EventDetails({ date, events, members, currentUser, onEdit }) {
  const formatDate = (dateStr) => {
    const d = new Date(dateStr + 'T00:00:00');
    return d.toLocaleDateString('en-US', { 
      month: '2-digit', 
      day: '2-digit', 
      year: 'numeric' 
    });
  };

  if (!date || events.length === 0) {
    return (
      <div className="bg-white rounded-lg shadow-lg p-6">
        <h2 className="text-2xl font-bold mb-4">Event</h2>
        <p className="text-gray-500 text-center py-8">
          Select a date on the calendar to view events
        </p>
      </div>
    );
  }

  return (
    <div className="bg-white rounded-lg shadow-lg p-6">
      <h2 className="text-2xl font-bold mb-2">Event</h2>
      <p className="text-gray-600 mb-6">{formatDate(date)}</p>

      <div className="space-y-2">
        {events.map((event, idx) => (
          <div 
            key={event.id || idx} 
            className="flex items-center justify-between p-3 border border-gray-200 rounded-lg hover:bg-gray-50 transition-colors"
          >
            <div className="flex-1">
              <p className="font-medium text-gray-900">
                {idx + 1}. {event.title}
              </p>
              <p className="text-sm text-gray-500">at {event.time}</p>
            </div>
            {currentUser && event.host === currentUser.email && (
              <button
                onClick={() => onEdit(event)}
                className="ml-3 px-3 py-1 text-sm bg-blue-600 text-white rounded hover:bg-blue-700 transition-colors flex-shrink-0"
              >
                Edit
              </button>
            )}
          </div>
        ))}
      </div>
    </div>
  );
}
