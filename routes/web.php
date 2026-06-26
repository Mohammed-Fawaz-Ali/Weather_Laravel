<?php

use App\Http\Controllers\WeatherController;
use Illuminate\Support\Facades\Route;

Route::get('/', [WeatherController::class, 'index'])->name('weather.index');
Route::post('/search', [WeatherController::class, 'search'])->name('weather.search');
Route::post('/search-locations', [WeatherController::class, 'searchLocations'])->name('weather.search-locations');
Route::get('/weather/{city}', [WeatherController::class, 'show'])->name('weather.show');
Route::get('/api/weather', [WeatherController::class, 'legacyWeatherApi'])->name('weather.api');