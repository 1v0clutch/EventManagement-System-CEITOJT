import { useState, useEffect } from 'react';
import api from '../services/api';

export default function EventForm({ members, onEventCreated, editingEvent, onCancelEdit, defaultDate }) {
  const [title, setTitle] = useState('');
  const [description, setDescription] = useState('');
  const [location, setLocation] = useState('');
  const [images, setImages] = useState([]);
  const [imagePreviews, setImagePreviews] = useState([]);
  const [date, setDate] = useState('');
  const [time, setTime] = useState('');
  const [selectedMembers, setSelectedMembers] = useState([]);
  const [isOpen, setIsOpen] = useState(false);
  const [error, setError] = useState('');
  const [success, setSuccess] = useState('');
  const [loading, setLoading] = useState(false);
  const [filterDepartment, setFilterDepartment] = useState('all');
  const [isDragging, setIsDragging] = useState(false);

  useEffect(() => {
    const now = new Date();
    const today = now.toISOString().split('T')[0];
    const currentTime = now.toTimeString().slice(0, 5);
    
    if (!date) setDate(defaultDate || today);
    if (!time) setTime(currentTime);
  }, [defaultDate]);

  useEffect(() => {
    if (editingEvent) {
      setTitle(editingEvent.title);
      setDescription(editingEvent.description || '');
      setLocation(editingEvent.location || '');
      setImagePreviews(editingEvent.images || []);
      setDate(editingEvent.date);
      setTime(editingEvent.time);
      setSelectedMembers(editingEvent.members.map(m => m.id));
      setIsOpen(editingEvent.is_open);
    }
  }, [editingEvent]);

  const handleSubmit = async (e) => {
    e.preventDefault();
    setError('');
    setSuccess('');
    setLoading(true);

    try {
      const formData = new FormData();
      formData.append('title', title);
      formData.append('description', description);
      formData.append('location', location);
      formData.append('date', date);
      formData.append('time', time);
      formData.append('is_open', isOpen ? '1' : '0');
      
      images.forEach((image) => {
        formData.append('images[]', image);
      });
      
      selectedMembers.forEach(id => {
        formData.append('member_ids[]', id);
      });

      if (editingEvent) {
        await api.post(`/events/${editingEvent.id}`, formData, {
          headers: { 'Content-Type': 'multipart/form-data' },
          params: { _method: 'PUT' }
        });
        setSuccess('Event updated successfully');
      } else {
        await api.post('/events', formData, {
          headers: { 'Content-Type': 'multipart/form-data' }
        });
        setSuccess('Event created successfully');
        resetForm();
      }

      onEventCreated();
    } catch (err) {
      setError(err.response?.data?.error || err.response?.data?.message || 'Operation failed');
    } finally {
      setLoading(false);
    }
  };

  const resetForm = () => {
    setTitle('');
    setDescription('');
    setLocation('');
    setImages([]);
    setImagePreviews([]);
    const now = new Date();
    setDate(now.toISOString().split('T')[0]);
    setTime(now.toTimeString().slice(0, 5));
    setSelectedMembers([]);
    setIsOpen(false);
  };

  const handleCancel = () => {
    resetForm();
    onCancelEdit();
    setError('');
    setSuccess('');
  };

  const toggleMember = (memberId) => {
    setSelectedMembers(prev =>
      prev.includes(memberId)
        ? prev.filter(id => id !== memberId)
        : [...prev, memberId]
    );
  };

  const handleImageChange = (e) => {
    const files = Array.from(e.target.files);
    addImages(files);
  };

  const addImages = (files) => {
    if (files.length > 0) {
      setImages(prev => [...prev, ...files]);
      
      files.forEach(file => {
        const reader = new FileReader();
        reader.onloadend = () => {
          setImagePreviews(prev => [...prev, reader.result]);
        };
        reader.readAsDataURL(file);
      });
    }
  };

  const handleDragOver = (e) => {
    e.preventDefault();
    setIsDragging(true);
  };

  const handleDragLeave = (e) => {
    e.preventDefault();
    setIsDragging(false);
  };

  const handleDrop = (e) => {
    e.preventDefault();
    setIsDragging(false);
    
    const files = Array.from(e.dataTransfer.files).filter(file => 
      file.type.startsWith('image/')
    );
    addImages(files);
  };

  const removeImage = (index) => {
    setImages(prev => prev.filter((_, i) => i !== index));
    setImagePreviews(prev => prev.filter((_, i) => i !== index));
  };

  const filteredMembers = filterDepartment === 'all' 
    ? members 
    : members.filter(member => member.department === filterDepartment);

  const availableDepartments = [...new Set(members.map(m => m.department).filter(Boolean))];

  return (
    <div>

      {error && (
        <div className="mb-4 rounded-md bg-red-50 p-4">
          <p className="text-sm text-red-800">{error}</p>
        </div>
      )}

      {success && (
        <div className="mb-4 rounded-md bg-green-50 p-4">
          <p className="text-sm text-green-800">{success}</p>
        </div>
      )}

      <form onSubmit={handleSubmit}>
        <div className="grid grid-cols-1 lg:grid-cols-3 gap-6">
          {/* Left Column - Event Details Box (1/3 width) */}
          <div className="lg:col-span-1 bg-white border-2 border-gray-300 rounded-lg p-6 shadow-md">
            <h3 className="text-lg font-semibold text-gray-900 mb-4">Event Details</h3>
            <div className="space-y-4">
              <div>
                <label className="block text-sm font-medium text-gray-700">Title</label>
                <input
                  type="text"
                  required
                  value={title}
                  onChange={(e) => setTitle(e.target.value)}
                  className="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                />
              </div>

              <div>
                <label className="block text-sm font-medium text-gray-700">Description</label>
                <textarea
                  rows="4"
                  value={description}
                  onChange={(e) => setDescription(e.target.value)}
                  placeholder="Event details..."
                  className="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                />
              </div>

              <div>
                <label className="block text-sm font-medium text-gray-700">Location</label>
                <input
                  type="text"
                  required
                  value={location}
                  onChange={(e) => setLocation(e.target.value)}
                  placeholder="e.g., Conference Room A, Building 1"
                  className="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                />
              </div>

              <div className="grid grid-cols-2 gap-4">
                <div>
                  <label className="block text-sm font-medium text-gray-700">Date</label>
                  <input
                    type="date"
                    required
                    value={date}
                    onChange={(e) => setDate(e.target.value)}
                    className="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                  />
                </div>
                <div>
                  <label className="block text-sm font-medium text-gray-700">Time</label>
                  <input
                    type="time"
                    required
                    value={time}
                    onChange={(e) => setTime(e.target.value)}
                    className="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                  />
                </div>
              </div>

              <div>
                <label className="flex items-center">
                  <input
                    type="checkbox"
                    checked={isOpen}
                    onChange={(e) => setIsOpen(e.target.checked)}
                    className="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded"
                  />
                  <span className="ml-2 text-sm text-gray-700">
                    Open Event (everyone can join)
                  </span>
                </label>
              </div>
            </div>
          </div>

          {/* Right Column - Members and Images (2/3 width) */}
          <div className="lg:col-span-2 space-y-6">
            {/* Members List Box (Red) - Scrollable Vertically */}
            <div className="bg-white border-2 border-gray-300 rounded-lg p-6 shadow-md">
              <h3 className="text-lg font-semibold text-gray-900 mb-4">Invite Members</h3>
              
              <div className="mb-3">
                <select
                  value={filterDepartment}
                  onChange={(e) => setFilterDepartment(e.target.value)}
                  className="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                >
                  <option value="all">All Departments</option>
                  {availableDepartments.map(dept => (
                    <option key={dept} value={dept}>{dept}</option>
                  ))}
                </select>
              </div>

              <div className="border border-gray-300 rounded-md p-3 bg-gray-50 h-48 overflow-y-auto">
                {filteredMembers.length === 0 ? (
                  <p className="text-sm text-gray-500">No members available</p>
                ) : (
                  <div className="space-y-2">
                    {filteredMembers.map(member => (
                      <label
                        key={member.id}
                        className="flex items-center p-2 hover:bg-blue-50 rounded cursor-pointer"
                      >
                        <input
                          type="checkbox"
                          checked={selectedMembers.includes(member.id)}
                          onChange={() => toggleMember(member.id)}
                          className="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded"
                        />
                        <span className="ml-2 text-sm text-gray-700">
                          {member.username}
                          {member.department && (
                            <span className="ml-2 text-xs text-gray-500">({member.department})</span>
                          )}
                        </span>
                      </label>
                    ))}
                  </div>
                )}
              </div>
              
              {selectedMembers.length > 0 && (
                <p className="mt-2 text-xs text-gray-600">
                  {selectedMembers.length} member(s) selected
                </p>
              )}
            </div>

            {/* Event Images Box (Blue) - Scrollable Horizontally */}
            <div className="bg-white border-2 border-gray-300 rounded-lg p-6 shadow-md">
              <h3 className="text-lg font-semibold text-gray-900 mb-4">Event Images</h3>
              
              <div
                onDragOver={handleDragOver}
                onDragLeave={handleDragLeave}
                onDrop={handleDrop}
                className={`border-2 border-dashed rounded-lg p-4 transition-colors ${
                  isDragging 
                    ? 'border-blue-500 bg-blue-50' 
                    : 'border-gray-300 bg-gray-50 hover:border-gray-400'
                }`}
              >
                <input
                  type="file"
                  accept="image/*"
                  multiple
                  onChange={handleImageChange}
                  className="hidden"
                  id="image-upload"
                />
                
                <div className="flex gap-3 overflow-x-auto pb-2">
                  {/* Add Image Button */}
                  <label
                    htmlFor="image-upload"
                    className="flex-shrink-0 w-32 h-32 border-2 border-dashed border-gray-300 rounded-md cursor-pointer hover:border-blue-500 hover:bg-blue-50 transition-colors flex items-center justify-center"
                  >
                    <div className="text-center">
                      <svg className="mx-auto h-8 w-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M12 4v16m8-8H4" />
                      </svg>
                      <span className="mt-1 text-xs text-gray-500">Add Image</span>
                    </div>
                  </label>

                  {/* Image Previews */}
                  {imagePreviews.map((preview, index) => (
                    <div key={index} className="relative flex-shrink-0 group">
                      <img
                        src={preview}
                        alt={`Preview ${index + 1}`}
                        className="w-32 h-32 object-cover rounded-md border border-gray-300"
                      />
                      <button
                        type="button"
                        onClick={() => removeImage(index)}
                        className="absolute top-1 right-1 bg-red-500 text-white rounded-full p-1 opacity-0 group-hover:opacity-100 transition-opacity hover:bg-red-600"
                      >
                        <svg className="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                      </button>
                    </div>
                  ))}
                </div>
                
                {imagePreviews.length === 0 && (
                  <p className="text-center text-xs text-gray-500 mt-2">
                    Click + or drag images here
                  </p>
                )}
              </div>
              
              <p className="mt-2 text-xs text-gray-500">
                {imagePreviews.length > 0 
                  ? `${imagePreviews.length} image(s) selected` 
                  : 'Drag and drop or click to add images'}
              </p>
            </div>
          </div>
        </div>

        {/* Submit Buttons */}
        <div className="flex space-x-3 mt-6">
          <button
            type="submit"
            disabled={loading}
            className="flex-1 px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 disabled:opacity-50"
          >
            {loading ? 'Saving...' : editingEvent ? 'Save Changes' : 'Create Event'}
          </button>
          {editingEvent && (
            <button
              type="button"
              onClick={handleCancel}
              className="px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
            >
              Cancel
            </button>
          )}
        </div>
      </form>
    </div>
  );
}
