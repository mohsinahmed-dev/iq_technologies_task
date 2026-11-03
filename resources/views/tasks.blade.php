<!-- IQT-FSD-2026 -->
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Task Manager | IQT-FSD-2026</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background: #f4f6f9; }
        .task-done .task-title { text-decoration: line-through; color: #6c757d; }
    </style>
</head>
<body>
<div class="container py-4" style="max-width: 720px;">
    <h1 class="h3 mb-1">Task Manager</h1>
    <p class="text-muted">IQT-FSD-2026</p>

    <!-- Weather widget -->
    <div class="card card-body mb-4 shadow-sm">
        <form id="weather-form" class="input-group mb-2">
            <input type="text" id="city" class="form-control" value="Lahore" placeholder="City" required>
            <button class="btn btn-outline-primary" type="submit">Get Weather</button>
        </form>
        <div id="weather-result" class="text-muted small">Loading weather...</div>
    </div>

    <div id="alert" class="alert alert-danger d-none"></div>

    <!-- Add / Edit form -->
    <form id="task-form" class="card card-body mb-4 shadow-sm">
        <input type="hidden" id="task-id">
        <div class="mb-2">
            <input type="text" id="title" class="form-control" placeholder="Task title" maxlength="255" required>
        </div>
        <div class="mb-3">
            <textarea id="description" class="form-control" rows="2" placeholder="Description (optional)"></textarea>
        </div>
        <div class="d-flex gap-2">
            <button type="submit" id="submit-btn" class="btn btn-primary">Add Task</button>
            <button type="button" id="cancel-btn" class="btn btn-outline-secondary d-none">Cancel</button>
        </div>
    </form>

    <!-- Task list -->
    <div id="task-list" class="list-group shadow-sm"></div>
    <p id="empty" class="text-center text-muted mt-3 d-none">No tasks yet. Add your first one above.</p>
</div>

<script>
const API = '/api/tasks';
const headers = { 'Content-Type': 'application/json', 'Accept': 'application/json' };

const form = document.getElementById('task-form');
const idInput = document.getElementById('task-id');
const titleInput = document.getElementById('title');
const descInput = document.getElementById('description');
const submitBtn = document.getElementById('submit-btn');
const cancelBtn = document.getElementById('cancel-btn');
const listEl = document.getElementById('task-list');
const emptyEl = document.getElementById('empty');
const alertEl = document.getElementById('alert');

let tasks = [];

function showError(msg) {
    alertEl.textContent = msg;
    alertEl.classList.remove('d-none');
    setTimeout(() => alertEl.classList.add('d-none'), 4000);
}

function escapeHtml(str) {
    const div = document.createElement('div');
    div.textContent = str ?? '';
    return div.innerHTML;
}

async function request(url, options = {}) {
    const res = await fetch(url, { headers, ...options });
    if (!res.ok) {
        let msg = 'Something went wrong.';
        try {
            const data = await res.json();
            msg = data.errors ? Object.values(data.errors).flat().join(' ') : (data.message || msg);
        } catch (e) {}
        throw new Error(msg);
    }
    return res.status === 204 ? null : res.json();
}

async function loadTasks() {
    try {
        tasks = await request(API);
        render();
    } catch (e) { showError(e.message); }
}

function render() {
    listEl.innerHTML = '';
    emptyEl.classList.toggle('d-none', tasks.length > 0);

    tasks.forEach(task => {
        const item = document.createElement('div');
        item.className = 'list-group-item d-flex align-items-start gap-3' + (task.is_completed ? ' task-done' : '');
        item.innerHTML = `
            <input class="form-check-input mt-1" type="checkbox" ${task.is_completed ? 'checked' : ''} data-action="toggle" data-id="${task.id}">
            <div class="flex-grow-1">
                <div class="task-title fw-semibold">${escapeHtml(task.title)}</div>
                ${task.description ? `<div class="small text-muted">${escapeHtml(task.description)}</div>` : ''}
            </div>
            <div class="btn-group btn-group-sm">
                <button class="btn btn-outline-primary" data-action="edit" data-id="${task.id}">Edit</button>
                <button class="btn btn-outline-danger" data-action="delete" data-id="${task.id}">Delete</button>
            </div>`;
        listEl.appendChild(item);
    });
}

function resetForm() {
    form.reset();
    idInput.value = '';
    submitBtn.textContent = 'Add Task';
    cancelBtn.classList.add('d-none');
}

form.addEventListener('submit', async (e) => {
    e.preventDefault();
    const payload = JSON.stringify({ title: titleInput.value.trim(), description: descInput.value.trim() || null });
    try {
        if (idInput.value) {
            await request(`${API}/${idInput.value}`, { method: 'PUT', body: payload });
        } else {
            await request(API, { method: 'POST', body: payload });
        }
        resetForm();
        loadTasks();
    } catch (err) { showError(err.message); }
});

cancelBtn.addEventListener('click', resetForm);

listEl.addEventListener('click', async (e) => {
    const el = e.target.closest('[data-action]');
    if (!el) return;
    const id = el.dataset.id;
    const task = tasks.find(t => t.id == id);

    try {
        if (el.dataset.action === 'delete') {
            if (!confirm('Delete this task?')) return;
            await request(`${API}/${id}`, { method: 'DELETE' });
            loadTasks();
        } else if (el.dataset.action === 'edit') {
            idInput.value = task.id;
            titleInput.value = task.title;
            descInput.value = task.description ?? '';
            submitBtn.textContent = 'Update Task';
            cancelBtn.classList.remove('d-none');
            titleInput.focus();
            window.scrollTo({ top: 0, behavior: 'smooth' });
        }
    } catch (err) { showError(err.message); }
});

listEl.addEventListener('change', async (e) => {
    if (e.target.dataset.action !== 'toggle') return;
    try {
        await request(`${API}/${e.target.dataset.id}`, {
            method: 'PUT',
            body: JSON.stringify({ is_completed: e.target.checked }),
        });
        loadTasks();
    } catch (err) { showError(err.message); loadTasks(); }
});

const weatherForm = document.getElementById('weather-form');
const weatherResult = document.getElementById('weather-result');

async function loadWeather(city) {
    weatherResult.textContent = 'Loading weather...';
    try {
        const w = await request(`/api/weather?city=${encodeURIComponent(city)}`);
        weatherResult.innerHTML = `
            <div class="d-flex align-items-center gap-2">
                ${w.icon ? `<img src="https://openweathermap.org/img/wn/${w.icon}.png" alt="" width="50" height="50">` : ''}
                <div>
                    <div class="fs-5 fw-semibold text-body">${escapeHtml(w.city)}, ${escapeHtml(w.country)}: ${w.temp}°C</div>
                    <div class="text-capitalize">${escapeHtml(w.description)} · feels like ${w.feels_like}°C · humidity ${w.humidity}%</div>
                </div>
            </div>`;
    } catch (err) {
        weatherResult.textContent = err.message;
    }
}

weatherForm.addEventListener('submit', (e) => {
    e.preventDefault();
    loadWeather(document.getElementById('city').value.trim());
});

loadWeather('Lahore');

loadTasks();
</script>
</body>
</html>