IQT-FSD-2026

# Task Manager: Laravel + MySQL

A simple full-stack task management application built for the I.Q. Technologies Full Stack Developer assessment.

- **Live Demo:** Not
- **Repository:** https://github.com/mohsinahmed-dev/iq_technologies_task

## Features

- Responsive task list (Bootstrap 5, works on mobile and desktop)
- Add, edit, delete, and mark tasks as completed
- REST API for all task operations, with validation and proper status codes
- Public API integration: current weather via OpenWeather (server-side proxy)

## Tech Stack

| Layer     | Technology                          |
|-----------|-------------------------------------|
| Back end  | Laravel (PHP), REST API             |
| Front end | Blade, vanilla JavaScript, Bootstrap 5 |
| Database  | MySQL                               |
| Public API| OpenWeather Current Weather API     |

## Requirements

- PHP 8.2 or higher
- Composer
- MySQL 5.7+ / MariaDB 10.3+
- An OpenWeather API key (free): https://openweathermap.org/api

## Setup Instructions

```bash
# 1. Clone the repository
git clone https://github.com/mohsinahmed-dev/iq_technologies_task.git
cd iq_technologies_task

# 2. Install dependencies
composer install

# 3. Create the environment file and app key
cp .env.example .env
php artisan key:generate
```

4. Create an empty MySQL database named `iqt_tasks`.

5. Open `.env` and set your database and API credentials:

```
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=iqt_tasks
DB_USERNAME=root
DB_PASSWORD=

OPENWEATHER_API_KEY=your_openweather_api_key
```

6. Run the migrations and start the server:

```bash
php artisan migrate
php artisan serve
```

7. Open http://127.0.0.1:8000 in your browser.

> No `npm install` or build step is needed. The front end uses Bootstrap from a CDN.

## Database Schema

**Table: `tasks`**

| Column         | Type            | Notes                      |
|----------------|-----------------|----------------------------|
| `id`           | BIGINT UNSIGNED | Primary key, auto-increment|
| `title`        | VARCHAR(255)    | Required                   |
| `description`  | TEXT            | Nullable                   |
| `is_completed` | TINYINT(1)      | Default `0` (false)        |
| `created_at`   | TIMESTAMP       | Nullable                   |
| `updated_at`   | TIMESTAMP       | Nullable                   |

SQL equivalent:

```sql
CREATE TABLE `tasks` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `title` VARCHAR(255) NOT NULL,
  `description` TEXT NULL,
  `is_completed` TINYINT(1) NOT NULL DEFAULT 0,
  `created_at` TIMESTAMP NULL DEFAULT NULL,
  `updated_at` TIMESTAMP NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
```

## API Endpoints

Send the header `Accept: application/json` with all requests.

| Method | Endpoint            | Description        | Success |
|--------|---------------------|--------------------|---------|
| GET    | `/api/tasks`        | Get all tasks      | 200     |
| POST   | `/api/tasks`        | Create a task      | 201     |
| PUT    | `/api/tasks/{id}`   | Update a task      | 200     |
| DELETE | `/api/tasks/{id}`   | Delete a task      | 204     |
| GET    | `/api/weather?city=`| Current weather    | 200     |

**Create a task**

```http
POST /api/tasks
Content-Type: application/json

{ "title": "Write README", "description": "Document setup and schema" }
```

**Mark as completed**

```http
PUT /api/tasks/1
Content-Type: application/json

{ "is_completed": true }
```

Validation errors return `422` with a JSON error body; a missing task returns `404`.

## API Integration (Step 3)

- **API used:** OpenWeather Current Weather Data API
- **Implementation:** The browser calls the Laravel endpoint `/api/weather?city=`. The controller validates the input and calls OpenWeather server-side using Laravel's HTTP client, so the API key stays in `.env` and is never exposed to the client. Only the fields the UI needs are returned (city, temperature, feels-like, humidity, description, icon), and errors such as an unknown city or an upstream failure are handled and shown in the UI.
- **Screenshot:** see `docs/weather-integration.png`

## Project Structure (key files)

```
app/Http/Controllers/Api/TaskController.php     # Task CRUD API
app/Http/Controllers/Api/WeatherController.php  # OpenWeather integration
app/Models/Task.php
database/migrations/*_create_tasks_table.php
resources/views/tasks.blade.php                 # Responsive UI
routes/api.php
routes/web.php
```

## Author

Mohsin Ahmed: Full Stack Developer
GitHub: https://github.com/mohsinahmed-dev