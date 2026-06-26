<?php

namespace App\Http\Controllers;

use App\Services\WeatherService;
use App\Models\SearchHistory;
use Illuminate\Http\Request;

class WeatherController extends Controller
{
    public function __construct(private WeatherService $weatherService)
    {}

    /**
     * Show weather search page
     */
    public function index()
    {
        $recentSearches = SearchHistory::latest()->limit(8)->get();
        return view('weather.index', compact('recentSearches'));
    }

    /**
     * API endpoint to search weather (AJAX)
     */
    public function search(Request $request)
    {
        $validated = $request->validate([
            'city' => 'required|string|min:2|max:100',
            'units' => 'sometimes|in:metric,imperial',
        ]);

        $units = $validated['units'] ?? 'metric';
        $city = $validated['city'];

        // Get current weather
        $weather = $this->weatherService->getCurrentWeather($city, $units);

        if (!$weather['success']) {
            return response()->json([
                'success' => false,
                'message' => $weather['message'] ?? 'City not found',
            ], 404);
        }

        // Get forecast
        $forecast = $this->weatherService->getForecast($city, 5, $units);

        // Get alerts if available
        $alerts = $this->weatherService->getAlerts($city);

        // Store search in database
        SearchHistory::create([
            'city' => $weather['city'],
            'country' => $weather['country'],
            'ip_address' => $request->ip(),
        ]);

        return response()->json([
            'success' => true,
            'weather' => $weather,
            'forecast' => $forecast,
            'alerts' => $alerts,
        ]);
    }

    /**
     * Search locations (autocomplete)
     */
    public function searchLocations(Request $request)
    {
        $validated = $request->validate([
            'q' => 'required|string|min:1|max:100',
        ]);

        $locations = $this->weatherService->searchLocations($validated['q']);

        return response()->json([
            'success' => true,
            'locations' => $locations,
        ]);
    }

    /**
     * Show weather for specific city
     */
    public function show(string $city)
    {
        $weather = $this->weatherService->getCurrentWeather($city);

        if (!$weather['success']) {
            return redirect()->route('weather.index')
                ->withErrors("Weather not found for '{$city}'");
        }

        $forecast = $this->weatherService->getForecast($city, 7);
        $alerts = $this->weatherService->getAlerts($city);

        return view('weather.show', compact('weather', 'forecast', 'alerts'));
    }

    /**
     * Legacy JSON endpoint used by the older weather page.
     */
    public function legacyWeatherApi(Request $request)
    {
        $location = $request->query('location', 'Hyderabad');
        $weather = $this->weatherService->getCurrentWeather($location);

        if (!$weather['success']) {
            return response()->json([
                'error' => $weather['message'] ?? 'Weather unavailable',
            ], 404);
        }

        $forecast = $this->weatherService->getForecast($location, 7);

        return response()->json([
            'resolvedAddress' => $weather['city'] . ', ' . $weather['country'],
            'address' => $weather['city'] . ', ' . $weather['country'],
            'currentConditions' => [
                'temp' => $weather['temperature'],
                'feelslike' => $weather['feels_like'],
                'humidity' => $weather['humidity'],
                'windspeed' => $weather['wind_speed'],
                'conditions' => $weather['description'],
                'icon' => $weather['icon'],
            ],
            'days' => array_map(function (array $day) use ($weather): array {
                return [
                    'datetime' => $day['date'],
                    'temp' => $day['avg_temp'],
                    'tempmax' => $day['max_temp'],
                    'tempmin' => $day['min_temp'],
                    'conditions' => $day['description'],
                    'icon' => $day['icon'],
                    'humidity' => $day['humidity'],
                    'windspeed' => $day['max_wind'],
                ];
            }, $forecast),
            'windunit' => 'km/h',
        ]);
    }
}