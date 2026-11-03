IQT-FSD-2026

# Task Manager

A small task manager I built for the I.Q. Technologies full stack assessment. You can add, edit, delete and complete tasks, and there's a little weather card at the top that pulls from the OpenWeather API.

It's built with Laravel and MySQL on the back end. The front end is a single Blade page using plain JavaScript and Bootstrap 5, so there's no npm install or build step to deal with.

I haven't deployed it, so to see it running, follow the setup steps below. They take about five minutes.

## What you need

- PHP 8.2 or newer
- Composer
- MySQL (or MariaDB)
- A free OpenWeather API key from https://openweathermap.org/api (new keys can take a little while to activate)

## Running it locally

Clone the repo and install the dependencies:

```bash
git clone https://github.com/mohsinahmed-dev/iq_technologies_task.git
cd iq_technologies_task
composer install
cp .env.example .env
php artisan key:generate
```

Create an empty MySQL database called `iqt_tasks`, then open `.env` and fill in your details:

```
DB_DATABASE=iqt_tasks
DB_USERNAME=root
DB_PASSWORD=

OPENWEATHER_API_KEY=your_key_here
```

Run the migrations and start the server:

```bash
php artisan migrate
php artisan serve
```

Then open http://127.0.0.1:8000.

## Database schema

There's one table, `tasks`:

| Column         | Type         | Notes                  |
|----------------|--------------|------------------------|
| id             | bigint       | primary key            |
| title          | varchar(255) | required               |
| description    | text         | optional               |
| is_completed   | tinyint(1)   | defaults to 0          |
| created_at     | timestamp    |                        |
| updated_at     | timestamp    |                        |

Or as SQL, if you'd rather create it by hand:

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

## API

Send `Accept: application/json` with your requests, otherwise Laravel will redirect on validation errors instead of returning JSON.

| Method | Endpoint             | What it does           |
|--------|----------------------|------------------------|
| GET    | /api/tasks           | List all tasks         |
| POST   | /api/tasks           | Create a task          |
| PUT    | /api/tasks/{id}      | Update a task          |
| DELETE | /api/tasks/{id}      | Delete a task          |
| GET    | /api/weather?city=   | Current weather        |

Creating a task:

```json
POST /api/tasks
{ "title": "Write README", "description": "Setup steps and schema" }
```

Marking one as done (you only need to send the field you're changing):

```json
PUT /api/tasks/1
{ "is_completed": true }
```

Invalid input returns a 422 with the validation errors, and a task that doesn't exist returns a 404.

## The API integration (step 3)

I used the OpenWeather current weather API. The browser never talks to OpenWeather directly. It calls `/api/weather?city=Lahore` on this app, and the Laravel controller makes the actual request. That keeps the API key in `.env` instead of exposing it in the front-end code. The controller validates the city, handles errors like an unknown city or OpenWeather being down, and only passes back what the page needs: temperature, feels-like, humidity, description and icon.

The screenshot is in `docs/weather-integration.png`.

## Where things live

- `app/Http/Controllers/Api/TaskController.php` for the task CRUD
- `app/Http/Controllers/Api/WeatherController.php` for the weather call
- `app/Models/Task.php`
- `database/migrations/` for the tasks table
- `resources/views/tasks.blade.php` for the whole UI
- `routes/api.php` and `routes/web.php`

## Author

Mohsin Ahmed
https://github.com/mohsinahmed-dev