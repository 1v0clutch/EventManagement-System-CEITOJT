<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Simple Event Management</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <h1>Simple Event Management</h1>

    <div id="authSection">
        <section id="loginSection">
            <h2>Login</h2>
            <form id="loginForm">
                <label>Email: <input type="email" name="email" required></label><br>
                <label>Password: <input type="password" name="password" required></label><br>
                <button type="submit" class="btn">Login</button>
                <button type="button" class="btn" id="toggleToRegister">Don't have an account? Register</button>
            </form>
            <div id="loginResult"></div>
        </section>

        <section id="registerSection" style="display:none;">
            <h2>Register</h2>
            <form id="registerForm">
                <label>Username: <input type="text" name="username" required></label><br>
                <label>Email: <input type="email" name="email" required></label><br>
                <label>Password: <input type="password" name="password" required></label><br>
                <label>Confirm Password: <input type="password" name="confirmPassword" required></label><br>
                <button type="submit" class="btn">Register</button>
                <button type="button" class="btn" id="toggleToLogin">Already have an account? Login</button>
            </form>
            <div id="registerResult"></div>
        </section>
    </div>

    <div id="appSection" style="display:none;">
        <div id="userInfo" style="text-align:right; margin-bottom:12px;">
            <span id="currentUser"></span>
            <button id="logoutBtn" class="btn">Logout</button>
        </div>

        <section>
            <h2>Create Event / Invite Members</h2>
            <form id="eventForm">
                <label>Title: <input type="text" name="title" required></label><br>
                <label>Description:<br>
                    <textarea name="description" rows="3" placeholder="Event details..."></textarea>
                </label><br>
                <label>Department:<br>
                    <select name="department">
                        <option value="">-- Select Department --</option>
                        <option value="HR">HR</option>
                        <option value="IT">IT</option>
                        <option value="Finance">Finance</option>
                        <option value="Operations">Operations</option>
                        <option value="Marketing">Marketing</option>
                    </select>
                </label><br>
                <label>Date: <input type="date" id="eventDate" name="date" required></label><br>
                <label>Time: <input type="time" id="eventTime" name="time" required></label><br>
                <label>Members:<br>
                    <div id="membersCheckboxes" style="border:1px solid #d0d5db; padding:8px; border-radius:4px; background:#fafbfc; max-height:150px; overflow-y:auto;">
                        <p style="margin:0 0 6px 0; color:#6b7280;">Loading members...</p>
                    </div>
                </label><br>
                <label>
                    <input type="checkbox" name="isOpen"> Open Event (everyone can join)
                </label><br>
                <button type="submit" id="eventSubmitBtn" class="btn">Create Event</button>
                <button type="button" id="cancelEditBtn" class="btn" style="display:none;margin-left:8px;">Cancel Edit</button>
            </form>
            <div id="createResult"></div>
        </section>

        <hr>

        <section>
            <h2>Events / Calendar</h2>
            <div style="margin-bottom: 16px;">
                <button id="toggleView" class="btn">Switch to List View</button>
                <button id="refreshEvents" class="btn">Refresh Events</button>
            </div>
            
            <div id="calendarView">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px;">
                    <button id="prevMonth" class="btn">&lt; Previous</button>
                    <h3 id="currentMonth" style="margin: 0;"></h3>
                    <button id="nextMonth" class="btn">Next &gt;</button>
                </div>
                <div id="calendar"></div>
            </div>
            
            <div id="eventsList" style="display: none;"></div>
        </section>

        <hr>
        <!--  
        <section>
            <h2>Member Availability (mark busy)</h2>
            <form id="availForm">
                <label>Member name:<br>
                    <select id="memberAvailList" name="member" required>
                        <option value="">Loading members...</option>
                    </select>
                </label><br>
                <label>Date: <input type="date" name="date" required></label><br>
                <label>Start time: <input type="time" name="start" required></label><br>
                <label>End time: <input type="time" name="end" required></label><br>
                <button type="submit" class="btn">Mark Busy</button>
            </form>
            <div id="availResult"></div>
        </section>
       -->
    </div>

    <script>
    // Helper: POST form data to api.php
    async function postAction(data){
        const resp = await fetch('api.php', {
            method: 'POST',
            headers: {'Content-Type':'application/json'},
            body: JSON.stringify(data)
        });
        return resp.json();
    }

    // state
    let currentUser = null;
    let editingEventId = null;

    // Auth form handlers
    document.getElementById('loginForm').addEventListener('submit', async e => {
        e.preventDefault();
        const fd = new FormData(e.target);
        const data = {action:'login', email:fd.get('email'), password:fd.get('password')};
        const res = await postAction(data);
        if(res.error){ document.getElementById('loginResult').textContent = 'Error: ' + res.error; }
        else { document.getElementById('loginResult').textContent = res.message; checkAuth(); }
    });

    document.getElementById('registerForm').addEventListener('submit', async e => {
        e.preventDefault();
        const fd = new FormData(e.target);
        if(fd.get('password') !== fd.get('confirmPassword')){ document.getElementById('registerResult').textContent = 'Passwords do not match'; return; }
        const data = {action:'register', username:fd.get('username'), email:fd.get('email'), password:fd.get('password')};
        const res = await postAction(data);
        if(res.error){ document.getElementById('registerResult').textContent = 'Error: ' + res.error; }
        else { 
            document.getElementById('registerResult').textContent = res.message + ' Please login with your credentials.';
            document.getElementById('registerForm').reset();
            document.getElementById('loginSection').style.display = 'block';
            document.getElementById('registerSection').style.display = 'none';
        }
    });

    document.getElementById('toggleToRegister').addEventListener('click', ()=>{
        document.getElementById('loginSection').style.display = 'none';
        document.getElementById('registerSection').style.display = 'block';
    });

    document.getElementById('toggleToLogin').addEventListener('click', ()=>{
        document.getElementById('loginSection').style.display = 'block';
        document.getElementById('registerSection').style.display = 'none';
    });

    document.getElementById('logoutBtn').addEventListener('click', async ()=>{
        await postAction({action:'logout'});
        checkAuth();
    });

    // Load members from users via get_registered_members
    async function loadMembers(){
        const res = await postAction({action:'get_registered_members'});
        const container = document.getElementById('membersCheckboxes');
        container.innerHTML = '';
        if(res.members && res.members.length > 0){
            const ul = document.createElement('ul');
            ul.style.margin = '0';
            ul.style.padding = '0';
            res.members.forEach(m => {
                const li = document.createElement('li');
                li.style.listStyle = 'none';
                li.style.display = 'flex';
                li.style.alignItems = 'center';
                li.style.padding = '6px 8px';
                li.style.borderRadius = '4px';
                li.style.cursor = 'pointer';
                li.style.whiteSpace = 'nowrap';
                li.className = 'member-checkbox-item';
                
                const checkbox = document.createElement('input');
                checkbox.type = 'checkbox';
                checkbox.name = 'memberCheckbox';
                checkbox.value = m.email;
                checkbox.id = 'member_' + m.email;
                checkbox.style.marginRight = '8px';
                checkbox.style.cursor = 'pointer';
                
                const label = document.createElement('label');
                label.htmlFor = checkbox.id;
                label.style.margin = '0';
                label.style.cursor = 'pointer';
                label.textContent = m.username + ' (' + m.email + ')';
                
                li.appendChild(checkbox);
                li.appendChild(label);
                ul.appendChild(li);
            });
            container.appendChild(ul);
        } else {
            const p = document.createElement('p');
            p.textContent = 'No members available';
            p.style.margin = '0';
            p.style.color = '#6b7280';
            container.appendChild(p);
        }
    }

    // Load members for availability form (commented out for now)
    // async function loadMembersForAvailability(){
    //     const res = await postAction({action:'list_members'});
    //     const select = document.getElementById('memberAvailList');
    //     select.innerHTML = '';
    // }

    // Set date input constraints (today to 1 year from today)
    function setDateConstraints(){
        const dateInput = document.getElementById('eventDate');
        const today = new Date();
        const minDate = today.toISOString().split('T')[0];
        const maxDate = new Date(today.getFullYear() + 1, today.getMonth(), today.getDate()).toISOString().split('T')[0];
        if(!dateInput) return;
        dateInput.min = minDate;
        dateInput.max = maxDate;
        // default to today so user can quickly create today's events but still edit
        if(!dateInput.value) dateInput.value = minDate;
        // default time to current time (HH:MM) if not set
        const timeInput = document.getElementById('eventTime');
        if(timeInput){
            const hh = String(today.getHours()).padStart(2,'0');
            const mm = String(today.getMinutes()).padStart(2,'0');
            const nowTime = hh + ':' + mm;
            if(!timeInput.value) timeInput.value = nowTime;
        }
    }

    // Cache for email to username mapping
    let membersCache = null;

    // Check auth and show appropriate section
    async function checkAuth(){
        const res = await postAction({action:'get_current_user'});
        const user = res.user;
            if(user){
                currentUser = user;
                document.getElementById('authSection').style.display = 'none';
                document.getElementById('appSection').style.display = 'block';
                document.getElementById('currentUser').textContent = 'Logged in as: ' + (user.username || user.email);
                setDateConstraints();
                await loadMembers();
                // loadMembersForAvailability(); // commented out - availability form not in use
                await renderCalendar();
            } else {
                currentUser = null;
                document.getElementById('authSection').style.display = 'block';
                document.getElementById('appSection').style.display = 'none';
            }
    }

    // Create or update event
    document.getElementById('eventForm').addEventListener('submit', async e => {
        e.preventDefault();
        const fd = new FormData(e.target);
        const date = fd.get('date');
        const time = fd.get('time');

        // Validate date within allowed range and time not in past (if today)
        const now = new Date();
        const today = now.toISOString().split('T')[0];
        const maxDateObj = new Date(now.getFullYear() + 1, now.getMonth(), now.getDate());
        const maxDate = maxDateObj.toISOString().split('T')[0];
        const currentTime = now.getHours().toString().padStart(2,'0') + ':' + now.getMinutes().toString().padStart(2,'0');

        if(date < today){
            document.getElementById('createResult').textContent = 'Error: Event date cannot be in the past. Select today or a future date.';
            return;
        }
        if(date > maxDate){
            document.getElementById('createResult').textContent = 'Error: Event date cannot be more than 1 year from today.';
            return;
        }
        if(date === today && time < currentTime){
            document.getElementById('createResult').textContent = 'Error: Event time cannot be in the past. Select a future time for today.';
            return;
        }

        const memberCheckboxes = document.querySelectorAll('input[name="memberCheckbox"]:checked');
        const members = Array.from(memberCheckboxes).map(cb => cb.value);
        const isOpen = fd.get('isOpen') ? true : false;

        const payload = {
            title:fd.get('title'),
            description:fd.get('description'),
            department:fd.get('department'),
            date:date,
            time:time,
            members:members,
            isOpen:isOpen
        };

        let res;
        if(editingEventId){
            payload.action = 'update_event';
            payload.id = editingEventId;
            res = await postAction(payload);
            if(res && !res.error){
                document.getElementById('createResult').textContent = res.message || 'Event updated';
                // exit edit mode
                cancelEdit();
            } else {
                document.getElementById('createResult').textContent = 'Error: ' + (res.error || JSON.stringify(res));
            }
        } else {
            payload.action = 'create_event';
            res = await postAction(payload);
            document.getElementById('createResult').textContent = res.message || JSON.stringify(res);
            if(res.message && res.message.includes('created')) e.target.reset();
        }
        if(isCalendarView){
            await renderCalendar();
        } else {
            await refreshEvents();
        }
    });

    // Mark availability (busy) - commented out for now
    // const availForm = document.getElementById('availForm');
    // if(availForm) {
    //     availForm.addEventListener('submit', async e => {
    //         e.preventDefault();
    //         const fd = new FormData(e.target);
    //         const data = {action:'set_availability', member:fd.get('member'), date:fd.get('date'), start:fd.get('start'), end:fd.get('end')};
    //         const res = await postAction(data);
    //         document.getElementById('availResult').textContent = res.message || JSON.stringify(res);
    //         refreshEvents();
    //     });
    // }

    document.getElementById('refreshEvents').addEventListener('click', refreshEvents);

    // Calendar state
    let currentCalendarDate = new Date();
    let calendarEvents = [];
    let isCalendarView = true;

    // Toggle between calendar and list view
    document.getElementById('toggleView').addEventListener('click', () => {
        isCalendarView = !isCalendarView;
        const calendarView = document.getElementById('calendarView');
        const listView = document.getElementById('eventsList');
        const toggleBtn = document.getElementById('toggleView');
        
        if(isCalendarView){
            calendarView.style.display = 'block';
            listView.style.display = 'none';
            toggleBtn.textContent = 'Switch to List View';
            renderCalendar();
        } else {
            calendarView.style.display = 'none';
            listView.style.display = 'block';
            toggleBtn.textContent = 'Switch to Calendar View';
            refreshEvents();
        }
    });

    // Calendar navigation
    document.getElementById('prevMonth').addEventListener('click', () => {
        currentCalendarDate.setMonth(currentCalendarDate.getMonth() - 1);
        renderCalendar();
    });

    document.getElementById('nextMonth').addEventListener('click', () => {
        currentCalendarDate.setMonth(currentCalendarDate.getMonth() + 1);
        renderCalendar();
    });

    // Render calendar
    async function renderCalendar(){
        const year = currentCalendarDate.getFullYear();
        const month = currentCalendarDate.getMonth();
        
        // Update month header
        const monthNames = ['January', 'February', 'March', 'April', 'May', 'June', 
                           'July', 'August', 'September', 'October', 'November', 'December'];
        document.getElementById('currentMonth').textContent = monthNames[month] + ' ' + year;
        
        // Get events for this month
        const res = await postAction({action:'list_events'});
        calendarEvents = res.events || [];
        
        // Build calendar grid
        const firstDay = new Date(year, month, 1).getDay();
        const daysInMonth = new Date(year, month + 1, 0).getDate();
        
        const calendar = document.getElementById('calendar');
        calendar.innerHTML = '';
        calendar.style.display = 'grid';
        calendar.style.gridTemplateColumns = 'repeat(7, 1fr)';
        calendar.style.gap = '4px';
        calendar.style.marginTop = '12px';
        
        // Day headers
        const dayNames = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];
        dayNames.forEach(day => {
            const header = document.createElement('div');
            header.textContent = day;
            header.style.fontWeight = 'bold';
            header.style.textAlign = 'center';
            header.style.padding = '8px';
            header.style.background = '#f3f4f6';
            header.style.borderRadius = '4px';
            calendar.appendChild(header);
        });
        
        // Empty cells before first day
        for(let i = 0; i < firstDay; i++){
            const empty = document.createElement('div');
            empty.style.minHeight = '80px';
            empty.style.background = '#fafbfc';
            empty.style.borderRadius = '4px';
            calendar.appendChild(empty);
        }
        
        // Days of month
        for(let day = 1; day <= daysInMonth; day++){
            const dateStr = year + '-' + String(month + 1).padStart(2, '0') + '-' + String(day).padStart(2, '0');
            const dayEvents = calendarEvents.filter(e => e.date === dateStr);
            
            const dayCell = document.createElement('div');
            dayCell.style.minHeight = '80px';
            dayCell.style.padding = '4px';
            dayCell.style.border = '1px solid #d0d5db';
            dayCell.style.borderRadius = '4px';
            dayCell.style.background = '#ffffff';
            dayCell.style.cursor = 'pointer';
            dayCell.style.position = 'relative';
            
            // Highlight today
            const today = new Date();
            if(year === today.getFullYear() && month === today.getMonth() && day === today.getDate()){
                dayCell.style.background = '#dbeafe';
                dayCell.style.borderColor = '#3b82f6';
                dayCell.style.borderWidth = '2px';
            }
            
            const dayNum = document.createElement('div');
            dayNum.textContent = day;
            dayNum.style.fontWeight = 'bold';
            dayNum.style.marginBottom = '4px';
            dayCell.appendChild(dayNum);
            
            // Show event indicators
            if(dayEvents.length > 0){
                dayEvents.slice(0, 3).forEach(ev => {
                    const eventDot = document.createElement('div');
                    eventDot.textContent = ev.time + ' ' + ev.title;
                    eventDot.style.fontSize = '0.7rem';
                    eventDot.style.background = '#3b82f6';
                    eventDot.style.color = 'white';
                    eventDot.style.padding = '2px 4px';
                    eventDot.style.borderRadius = '3px';
                    eventDot.style.marginBottom = '2px';
                    eventDot.style.overflow = 'hidden';
                    eventDot.style.textOverflow = 'ellipsis';
                    eventDot.style.whiteSpace = 'nowrap';
                    dayCell.appendChild(eventDot);
                });
                
                if(dayEvents.length > 3){
                    const more = document.createElement('div');
                    more.textContent = '+' + (dayEvents.length - 3) + ' more';
                    more.style.fontSize = '0.65rem';
                    more.style.color = '#6b7280';
                    more.style.fontStyle = 'italic';
                    dayCell.appendChild(more);
                }
            }
            
            // Click to show day details
            dayCell.addEventListener('click', () => showDayDetails(dateStr, dayEvents));
            
            calendar.appendChild(dayCell);
        }
    }

    // Show details for a specific day
    async function showDayDetails(dateStr, events){
        if(events.length === 0){
            alert('No events on ' + dateStr);
            return;
        }
        
        const emailToUsername = await getEmailToUsernameMap();
        let details = 'Events on ' + dateStr + ':\n\n';
        
        events.forEach((ev, idx) => {
            details += (idx + 1) + '. ' + ev.title + ' at ' + ev.time + '\n';
            if(ev.description) details += '   Description: ' + ev.description + '\n';
            if(ev.department) details += '   Department: ' + ev.department + '\n';
            const hostDisplay = ev.host ? (emailToUsername[ev.host] || ev.host) : 'Unknown';
            details += '   Host: ' + hostDisplay + '\n';
            details += '   Members: ' + ev.members.map(m => emailToUsername[m] || m).join(', ') + '\n\n';
        });
        
        alert(details);
    }

    // Helper to map email to username
    async function getEmailToUsernameMap(){
        if(!membersCache){
            const res = await postAction({action:'get_registered_members'});
            membersCache = {};
            if(res.members){
                res.members.forEach(m => {
                    membersCache[m.email] = m.username;
                });
            }
        }
        return membersCache;
    }

    async function enterEditMode(ev){
        editingEventId = ev.id;
        const form = document.getElementById('eventForm');
        form.querySelector('input[name="title"]').value = ev.title || '';
        form.querySelector('textarea[name="description"]').value = ev.description || '';
        form.querySelector('select[name="department"]').value = ev.department || '';
        const dateInput = document.getElementById('eventDate');
        const timeInput = document.getElementById('eventTime');
        if(dateInput) dateInput.value = ev.date || '';
        if(timeInput) timeInput.value = ev.time || '';
        // uncheck all then check members
        const checks = document.querySelectorAll('input[name="memberCheckbox"]');
        checks.forEach(cb => cb.checked = ev.members.includes(cb.value));
        const isOpenCb = form.querySelector('input[name="isOpen"]');
        if(isOpenCb) isOpenCb.checked = !!ev.isOpen;
        document.getElementById('eventSubmitBtn').textContent = 'Save Changes';
        document.getElementById('cancelEditBtn').style.display = 'inline-block';
        window.scrollTo({top:0, behavior:'smooth'});
    }

    function cancelEdit(){
        editingEventId = null;
        document.getElementById('eventForm').reset();
        document.getElementById('eventSubmitBtn').textContent = 'Create Event';
        document.getElementById('cancelEditBtn').style.display = 'none';
        setDateConstraints();
    }

    document.getElementById('cancelEditBtn').addEventListener('click', ()=>{
        cancelEdit();
        document.getElementById('createResult').textContent = '';
    });

    async function refreshEvents(){
        const res = await postAction({action:'list_events'});
        const container = document.getElementById('eventsList');
        if(!res.events) { container.textContent = JSON.stringify(res); return; }
        container.innerHTML = '';
        if(res.events.length===0){ container.textContent = 'No events yet.'; return; }
        const emailToUsername = await getEmailToUsernameMap();
        for(const ev of res.events){
            const div = document.createElement('div');
            div.className = 'event-card';
            const h = document.createElement('h3');
            h.textContent = ev.title + ' — ' + ev.date + ' ' + ev.time;
            div.appendChild(h);
            const info = document.createElement('div');
            info.style.fontSize = '0.9rem';
            info.style.marginBottom = '8px';
            let infoHtml = '';
            if(ev.description) infoHtml += '<p><strong>Description:</strong> ' + ev.description + '</p>';
            if(ev.department) infoHtml += '<p><strong>Department:</strong> ' + ev.department + '</p>';
            if(ev.isOpen) infoHtml += '<p style="color:green;"><strong>Open Event</strong> - Everyone can join</p>';
            const hostDisplay = ev.host ? (emailToUsername[ev.host] ? (emailToUsername[ev.host] + ' ('+ev.host+')') : ev.host) : 'Unknown';
            infoHtml += '<p><strong>Host:</strong> ' + hostDisplay + '</p>';
            info.innerHTML = infoHtml;
            div.appendChild(info);
            const ul = document.createElement('ul');
            ul.innerHTML = '<strong>Members:</strong>';
            for(const m of ev.members){
                const li = document.createElement('li');
                const username = emailToUsername[m] || m;
                li.textContent = username + ' — checking...';
                li.className = 'checking';
                ul.appendChild(li);
                // check availability per member
                (async ()=>{
                    const av = await postAction({action:'get_member_availability_for_event', event_id:ev.id, member:m});
                    li.textContent = (emailToUsername[m] || m) + ' — ' + (av.available? 'Available' : ('Busy at ' + av.busy_reason));
                    li.className = av.available ? 'member-available' : 'member-busy';
                })();
            }
            div.appendChild(ul);
            // edit button only for host
            if(currentUser && ev.host && currentUser.email === ev.host){
                const editBtn = document.createElement('button');
                editBtn.textContent = 'Edit';
                editBtn.className = 'btn';
                editBtn.style.marginTop = '8px';
                editBtn.addEventListener('click', ()=> enterEditMode(ev));
                div.appendChild(editBtn);
            }
            container.appendChild(div);
        }
    }

    // Check auth on page load
    checkAuth();
    </script>

</body>
</html>