<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Exception;

class WeatherService
{
    private string $apiKey;
    private string $baseUrl = 'https://api.weatherapi.com/v1';

    public function __construct()
    {
        $this->apiKey = config('services.weather.api_key');
    }

    /**
     * Get current weather for a location using WeatherAPI
     */
    public function getCurrentWeather(string $city, string $units = 'metric'): array
    {
        $cacheKey = "weather:{$city}:{$units}";

        // Cache for 10 minutes to avoid API rate limits
        return Cache::remember($cacheKey, 600, function () use ($city, $units) {
            try {
                $response = Http::timeout(5)->get("{$this->baseUrl}/current.json", [
                    'key' => $this->apiKey,
                    'q' => $city,
                    'aqi' => 'yes',
                ]);

                if ($response->failed()) {
                    // Check if it's a 400 error (city not found)
                    if ($response->status() === 400) {
                        throw new Exception("City not found: {$city}");
                    }
                    throw new Exception("Weather API Error: {$response->status()}");
                }

                $data = $response->json();

                // Convert temperature based on units
                $temp = $data['current']['temp_c'];
                $feelsLike = $data['current']['feelslike_c'];
                $windSpeed = $data['current']['wind_kph'];
                $visibility = $data['current']['vis_km'];
                $tempUnit = '°C';
                $speedUnit = 'km/h';
                $visUnit = 'km';

                if ($units === 'imperial') {
                    $temp = $data['current']['temp_f'];
                    $feelsLike = $data['current']['feelslike_f'];
                    $windSpeed = $data['current']['wind_mph'];
                    $visibility = $data['current']['vis_miles'];
                    $tempUnit = '°F';
                    $speedUnit = 'mph';
                    $visUnit = 'mi';
                }

                return [
                    'success' => true,
                    'city' => $data['location']['name'],
                    'country' => $data['location']['country'],
                    'region' => $data['location']['region'],
                    'latitude' => $data['location']['lat'],
                    'longitude' => $data['location']['lon'],
                    'temperature' => round($temp),
                    'feels_like' => round($feelsLike),
                    'humidity' => $data['current']['humidity'],
                    'pressure' => round($data['current']['pressure_mb']),
                    'description' => $data['current']['condition']['text'],
                    'icon' => $data['current']['condition']['icon'],
                    'wind_speed' => round($windSpeed, 1),
                    'clouds' => $data['current']['cloud'],
                    'visibility' => round($visibility, 1),
                    'uv_index' => $data['current']['uv'],
                    'air_quality' => $this->getAirQuality($data['current']['air_quality'] ?? []),
                    'temp_unit' => $tempUnit,
                    'speed_unit' => $speedUnit,
                    'vis_unit' => $visUnit,
                ];
            } catch (Exception $e) {
                return [
                    'success' => false,
                    'message' => $e->getMessage(),
                ];
            }
        });
    }

    /**
     * Get air quality data
     */
    private function getAirQuality(array $aqData): array
    {
        if (empty($aqData)) {
            return ['status' => 'unavailable'];
        }

        $us_index = $aqData['us_epa_index'] ?? null;
        $uk_index = $aqData['gb_defra_index'] ?? null;

        $statusMap = [
            1 => 'Good',
            2 => 'Moderate',
            3 => 'Poor',
            4 => 'Very Poor',
            5 => 'Severe',
        ];

        return [
            'us_epa_index' => $us_index ? $statusMap[$us_index] : 'N/A',
            'uk_defra_index' => $uk_index ? $statusMap[$uk_index] : 'N/A',
            'co' => round($aqData['co'] ?? 0, 2),
            'no2' => round($aqData['no2'] ?? 0, 2),
            'o3' => round($aqData['o3'] ?? 0, 2),
            'pm2_5' => round($aqData['pm2_5'] ?? 0, 2),
            'pm10' => round($aqData['pm10'] ?? 0, 2),
        ];
    }

    /**
     * Get forecast using WeatherAPI
     */
    public function getForecast(string $city, int $days = 5, string $units = 'metric'): array
    {
        $cacheKey = "forecast:{$city}:{$days}:{$units}";

        return Cache::remember($cacheKey, 3600, function () use ($city, $days, $units) {
            try {
                $response = Http::timeout(5)->get("{$this->baseUrl}/forecast.json", [
                    'key' => $this->apiKey,
                    'q' => $city,
                    'days' => $days,
                    'aqi' => 'yes',
                ]);

                if ($response->failed()) {
                    throw new Exception("Forecast API Error: {$response->status()}");
                }

                $data = $response->json();
                return $this->parseForecast($data['forecast']['forecastday'], $units);
            } catch (Exception $e) {
                \Log::error('Forecast Error: ' . $e->getMessage());
                return [];
            }
        });
    }

    /**
     * Parse forecast data
     */
    private function parseForecast(array $forecastDays, string $units = 'metric'): array
    {
        $tempUnit = $units === 'metric' ? '°C' : '°F';
        $speedUnit = $units === 'metric' ? 'km/h' : 'mph';
        
        $forecast = [];
        foreach ($forecastDays as $day) {
            $tempKey = $units === 'metric' ? 'maxtemp_c' : 'maxtemp_f';
            $minTempKey = $units === 'metric' ? 'mintemp_c' : 'mintemp_f';
            $avgTempKey = $units === 'metric' ? 'avgtemp_c' : 'avgtemp_f';
            $windKey = $units === 'metric' ? 'maxwind_kph' : 'maxwind_mph';

            $date = $day['date'] ?? '';
            $day_data = $day['day'] ?? [];
            $condition = $day_data['condition'] ?? [];

            $forecast[] = [
                'date' => $date,
                'day' => $date ? date('D', strtotime($date)) : '',
                'day_name' => $date ? date('l', strtotime($date)) : '',
                'date_formatted' => $date ? date('M d', strtotime($date)) : '',
                'max_temp' => round($day_data[$tempKey] ?? 0),
                'min_temp' => round($day_data[$minTempKey] ?? 0),
                'avg_temp' => round($day_data[$avgTempKey] ?? 0),
                'max_wind' => round($day_data[$windKey] ?? 0, 1),
                'description' => $condition['text'] ?? 'Unknown',
                'icon' => $condition['icon'] ?? '',
                'chance_of_rain' => $day_data['daily_chance_of_rain'] ?? 0,
                'chance_of_snow' => $day_data['daily_chance_of_snow'] ?? 0,
                'humidity' => $day_data['avg_humidity'] ?? $day_data['avghumidity'] ?? 0,
                'uv_index' => $day_data['uv'] ?? 0,
                'visibility' => round($day_data[$units === 'metric' ? 'avgvis_km' : 'avgvis_miles'] ?? 0, 1),
                'temp_unit' => $tempUnit,
                'speed_unit' => $speedUnit,
            ];
        }

        return $forecast;
    }

    /**
     * Search for locations (autocomplete)
     */
    public function searchLocations(string $query): array
    {
        $cacheKey = "search:{$query}";

        return Cache::remember($cacheKey, 3600, function () use ($query) {
            try {
                $response = Http::timeout(5)->get("{$this->baseUrl}/search.json", [
                    'key' => $this->apiKey,
                    'q' => $query,
                ]);

                if ($response->failed()) {
                    return [];
                }

                $data = $response->json();
                $locations = [];

                foreach ($data as $location) {
                    $locations[] = [
                        'name' => $location['name'],
                        'region' => $location['region'] ?? '',
                        'country' => $location['country'],
                        'lat' => $location['lat'],
                        'lon' => $location['lon'],
                        'url' => $location['url'],
                    ];
                }

                return $locations;
            } catch (Exception $e) {
                \Log::error('Search Error: ' . $e->getMessage());
                return [];
            }
        });
    }

    /**
     * Get alerts (if available for location)
     */
    public function getAlerts(string $city): array
    {
        $cacheKey = "alerts:{$city}";

        return Cache::remember($cacheKey, 600, function () use ($city) {
            try {
                $response = Http::timeout(5)->get("{$this->baseUrl}/current.json", [
                    'key' => $this->apiKey,
                    'q' => $city,
                ]);

                if ($response->failed()) {
                    return [];
                }

                $data = $response->json();
                $alerts = [];

                if (isset($data['alerts']['alert']) && is_array($data['alerts']['alert'])) {
                    foreach ($data['alerts']['alert'] as $alert) {
                        $alerts[] = [
                            'headline' => $alert['headline'],
                            'description' => $alert['desc'],
                            'severity' => $alert['severity'],
                            'effective' => $alert['effective'],
                            'expires' => $alert['expires'],
                        ];
                    }
                }

                return $alerts;
            } catch (Exception $e) {
                return [];
            }
        });
    }
}