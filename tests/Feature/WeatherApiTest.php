<?php

namespace Tests\Feature;

use Tests\TestCase;

class WeatherApiTest extends TestCase
{
    public function test_legacy_weather_endpoint_returns_json(): void
    {
        $response = $this->get('/api/weather?location=London');

        $response->assertOk();
        $response->assertHeader('content-type', 'application/json');
    }

    public function test_weather_search_post_works_without_csrf(): void
    {
        $this->artisan('migrate', ['--database' => 'sqlite']);

        $response = $this->postJson('/search', [
            'city' => 'London',
            'units' => 'metric',
        ]);

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'success',
            'weather',
            'forecast',
            'alerts',
        ]);
    }
}
