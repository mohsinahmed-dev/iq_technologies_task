<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class WeatherController extends Controller
{
    public function show(Request $request): JsonResponse
    {
        $data = $request->validate([
            'city' => 'required|string|max:100',
        ]);

        $response = Http::timeout(10)->get('https://api.openweathermap.org/data/2.5/weather', [
            'q'     => $data['city'],
            'appid' => config('services.openweather.key'),
            'units' => 'metric',
        ]);

        if ($response->failed()) {
            return response()->json(
                ['message' => $response->json('message') ?? 'Unable to fetch weather.'],
                $response->status() === 404 ? 404 : 502
            );
        }

        $w = $response->json();

        return response()->json([
            'city'        => $w['name'],
            'country'     => $w['sys']['country'] ?? null,
            'temp'        => round($w['main']['temp']),
            'feels_like'  => round($w['main']['feels_like']),
            'humidity'    => $w['main']['humidity'],
            'description' => $w['weather'][0]['description'] ?? '',
            'icon'        => $w['weather'][0]['icon'] ?? null,
        ]);
    }
}