// Fetch the newest figures without reloading the whole dashboard.
const statusBox = document.getElementById('overview-status');
const refreshButton = document.getElementById('refresh-overview');
let loading = false;
let stopped = false;

function cell(value) {
    const td = document.createElement('td');
    td.textContent = value;
    return td;
}

function emptyRow(body, columns, message) {
    const row = document.createElement('tr');
    const td = cell(message);
    td.colSpan = columns;
    row.append(td);
    body.append(row);
}

async function refreshOverview() {
    if (loading || stopped || document.hidden) return;
    loading = true;
    refreshButton.disabled = true;
    const controller = new AbortController();
    const timeout = setTimeout(() => controller.abort(), 8000);
    try {
        const response = await fetch('api/operations_summary.php', {cache: 'no-store', signal: controller.signal});
        const data = await response.json();
        if (!response.ok) {
            if (response.status === 401 || response.status === 403) {
                stopped = true;
                ['overview-stats', 'overview-schedules', 'overview-hotels'].forEach(id => document.getElementById(id).replaceChildren());
            }
            throw new Error(data.error || 'Could not refresh.');
        }
        const stats = document.getElementById('overview-stats');
        stats.replaceChildren();
        const labels = {
            confirmed_tickets: 'Confirmed tickets',
            cancelled_tickets: 'Cancelled tickets',
            available_seats: 'Available future seats',
            confirmed_stays: 'Confirmed hotel stays',
            open_conversations: 'Open conversations'
        };
        Object.entries(labels).forEach(([key, label]) => {
            const card = document.createElement('article');
            card.className = 'card';
            const name = document.createElement('span');
            name.textContent = label;
            const value = document.createElement('h2');
            value.textContent = data.summary[key];
            card.append(name, value);
            stats.append(card);
        });
        const schedules = document.getElementById('overview-schedules');
        schedules.replaceChildren();
        data.schedules.forEach(schedule => {
            const row = document.createElement('tr');
            row.append(cell(schedule.source + ' → ' + schedule.destination), cell(schedule.departure_time),
                cell(schedule.schedule_status), cell(schedule.confirmed), cell(schedule.blocked),
                cell(schedule.available_seats + ' / ' + schedule.total_seats));
            const action = document.createElement('td');
            const link = document.createElement('a');
            link.href = 'manage_seats.php?id=' + Number(schedule.schedule_id);
            link.textContent = 'View seats';
            action.append(link);
            row.append(action);
            schedules.append(row);
        });
        if (!data.schedules.length) emptyRow(schedules, 7, 'No transport schedules yet.');
        const hotels = document.getElementById('overview-hotels');
        hotels.replaceChildren();
        data.hotels.forEach(hotel => {
            const row = document.createElement('tr');
            row.append(cell(hotel.hotel_name), cell(hotel.location), cell(hotel.available_rooms + ' / ' + hotel.total_rooms));
            hotels.append(row);
        });
        if (!data.hotels.length) emptyRow(hotels, 3, 'No hotel listings yet.');
        statusBox.textContent = 'Last updated: ' + data.updated_at;
    } catch (error) {
        statusBox.textContent = (error.name === 'AbortError' ? 'Refresh timed out.' : error.message) +
            (stopped ? ' Log in again to continue.' : ' Displayed values may be outdated. Retrying on the next refresh.');
    } finally {
        clearTimeout(timeout);
        loading = false;
        refreshButton.disabled = stopped;
    }
}
refreshButton.addEventListener('click', refreshOverview);
document.addEventListener('visibilitychange', () => { if (!document.hidden) refreshOverview(); });
setInterval(refreshOverview, 10000);
refreshOverview();
