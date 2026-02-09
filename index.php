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

    <section>
        <h2>Create Event / Invite Members</h2>
        <form id="eventForm">
            <label>Title: <input type="text" name="title" required></label><br>
            <label>Date: <input type="date" name="date" required></label><br>
            <label>Time: <input type="time" name="time" required></label><br>
            <label>Members (comma-separated names or emails):<br>
                <textarea name="members" rows="2" cols="40" placeholder="alice@example.com, bob@example.com"></textarea>
            </label><br>
            <button type="submit" class="btn">Create Event & Invite</button>
        </form>
        <div id="createResult"></div>
    </section>

    <hr>

    <section>
        <h2>Events / Calendar</h2>
        <button id="refreshEvents" class="btn">Refresh Events</button>
        <div id="eventsList"></div>
    </section>

    <hr>

    <section>
        <h2>Member Availability (mark busy)</h2>
        <form id="availForm">
            <label>Member name/email: <input type="text" name="member" required></label><br>
            <label>Date: <input type="date" name="date" required></label><br>
            <label>Start time: <input type="time" name="start" required></label><br>
            <label>End time: <input type="time" name="end" required></label><br>
            <button type="submit" class="btn">Mark Busy</button>
        </form>
        <div id="availResult"></div>
    </section>

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

    // Create event
    document.getElementById('eventForm').addEventListener('submit', async e => {
        e.preventDefault();
        const fd = new FormData(e.target);
        const members = (fd.get('members')||'').split(',').map(s=>s.trim()).filter(Boolean);
        const data = {action:'create_event', title:fd.get('title'), date:fd.get('date'), time:fd.get('time'), members};
        const res = await postAction(data);
        document.getElementById('createResult').textContent = res.message || JSON.stringify(res);
        refreshEvents();
    });

    // Mark availability (busy)
    document.getElementById('availForm').addEventListener('submit', async e => {
        e.preventDefault();
        const fd = new FormData(e.target);
        const data = {action:'set_availability', member:fd.get('member'), date:fd.get('date'), start:fd.get('start'), end:fd.get('end')};
        const res = await postAction(data);
        document.getElementById('availResult').textContent = res.message || JSON.stringify(res);
        refreshEvents();
    });

    document.getElementById('refreshEvents').addEventListener('click', refreshEvents);

    async function refreshEvents(){
        const res = await postAction({action:'list_events'});
        const container = document.getElementById('eventsList');
        if(!res.events) { container.textContent = JSON.stringify(res); return; }
        container.innerHTML = '';
        if(res.events.length===0){ container.textContent = 'No events yet.'; return; }
        for(const ev of res.events){
            const div = document.createElement('div');
            div.className = 'event-card';
            const h = document.createElement('h3');
            h.textContent = ev.title + ' — ' + ev.date + ' ' + ev.time;
            div.appendChild(h);
            const ul = document.createElement('ul');
            for(const m of ev.members){
                const li = document.createElement('li');
                li.textContent = m + ' — checking...';
                li.className = 'checking';
                ul.appendChild(li);
                // check availability per member
                (async ()=>{
                    const av = await postAction({action:'get_member_availability_for_event', event_id:ev.id, member:m});
                    li.textContent = m + ' — ' + (av.available? 'Available' : ('Busy at ' + av.busy_reason));
                    li.className = av.available ? 'member-available' : 'member-busy';
                })();
            }
            div.appendChild(ul);
            container.appendChild(div);
        }
    }

    // initial load
    refreshEvents();
    </script>

</body>
</html>