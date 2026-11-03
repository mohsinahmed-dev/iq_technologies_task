<?php

use App\Http\Controllers\Api\TaskController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\WeatherController;

Route::apiResource('tasks', TaskController::class)->except('show');

Route::get('weather', [WeatherController::class, 'show']);