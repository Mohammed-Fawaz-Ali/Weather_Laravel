@extends('layouts.app')

@section('content')
<div class="weather-app">
    <!-- Navigation Bar -->
    <nav class="navbar">
        <div class="navbar-container">
            <div class="navbar-brand">
                <h1>🌍 WeatherHub</h1>
            </div>
            <div class="navbar-right">
                <select id="unitSelect" class="unit-selector">
                    <option value="metric">°C Metric</option>
                    <option value="imperial">°F Imperial</option>
                </select>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="container-main">
        <!-- Search Section -->
        <div class="search-hero">
            <div class="hero-content">
                <h2>Get Weather for Any Location</h2>
                <p>Real-time forecasts powered by WeatherAPI</p>
                
                <div class="search-container">
                    <div class="search-wrapper">
                        <input 
                            type="text" 
                            id="cityInput" 
                            placeholder="🔍 Search city, region, or coordinates..." 
                            class="search-input-large"
                            autocomplete="off"
                        >
                        <div id="suggestionsDropdown" class="suggestions-dropdown" style="display: none;"></div>
                    </div>
                    <button id="searchBtn" class="btn-search-large">
                        <span>Search Weather</span>
                        <span class="spinner" style="display: none;"></span>
                    </button>
                </div>
            </div>
        </div>

        <!-- Error Alert -->
        <div id="errorMessage" class="alert-error" style="display: none;">
            <span class="error-icon">⚠️</span>
            <div>
                <strong>Oops!</strong>
                <p id="errorText"></p>
            </div>
            <button onclick="this.parentElement.style.display='none'" class="close-btn">✕</button>
        </div>

        <!-- Main Weather Display -->
        <div id="weatherContent" class="weather-content" style="display: none;">
            
            <!-- Current Weather Card -->
            <div class="current-weather-section">
                <div class="weather-card-current">
                    <div class="card-header">
                        <div class="location-info">
                            <h2 id="cityName">City Name</h2>
                            <p id="locationDetails" class="location-details">Region, Country</p>
                        </div>
                        <select id="unitSelectCard" class="unit-selector-card">
                            <option value="metric">°C</option>
                            <option value="imperial">°F</option>
                        </select>
                    </div>

                    <div class="card-body">
                        <div class="temperature-display">
                            <img id="weatherIcon" class="weather-icon-large" src="" alt="Weather">
                            <div class="temp-info">
                                <div class="temp-value" id="temperature">32°C</div>
                                <div class="weather-desc" id="weatherDesc">Partly Cloudy</div>
                            </div>
                        </div>

                        <!-- Key Stats Grid -->
                        <div class="stats-grid">
                            <div class="stat-box">
                                <div class="stat-icon">🌡️</div>
                                <div class="stat-content">
                                    <div class="stat-label">Feels Like</div>
                                    <div class="stat-value" id="feelsLike">35°</div>
                                </div>
                            </div>
                            <div class="stat-box">
                                <div class="stat-icon">💧</div>
                                <div class="stat-content">
                                    <div class="stat-label">Humidity</div>
                                    <div class="stat-value" id="humidity">65%</div>
                                </div>
                            </div>
                            <div class="stat-box">
                                <div class="stat-icon">💨</div>
                                <div class="stat-content">
                                    <div class="stat-label">Wind Speed</div>
                                    <div class="stat-value" id="windSpeed">15 km/h</div>
                                </div>
                            </div>
                            <div class="stat-box">
                                <div class="stat-icon">🌫️</div>
                                <div class="stat-content">
                                    <div class="stat-label">Visibility</div>
                                    <div class="stat-value" id="visibility">10 km</div>
                                </div>
                            </div>
                            <div class="stat-box">
                                <div class="stat-icon">🔽</div>
                                <div class="stat-content">
                                    <div class="stat-label">Pressure</div>
                                    <div class="stat-value" id="pressure">1013 mb</div>
                                </div>
                            </div>
                            <div class="stat-box">
                                <div class="stat-icon">☁️</div>
                                <div class="stat-content">
                                    <div class="stat-label">Cloud Cover</div>
                                    <div class="stat-value" id="clouds">45%</div>
                                </div>
                            </div>
                            <div class="stat-box">
                                <div class="stat-icon">☀️</div>
                                <div class="stat-content">
                                    <div class="stat-label">UV Index</div>
                                    <div class="stat-value" id="uvIndex">6</div>
                                </div>
                            </div>
                            <div class="stat-box">
                                <div class="stat-icon">📍</div>
                                <div class="stat-content">
                                    <div class="stat-label">Coordinates</div>
                                    <div class="stat-value" id="coordinates">--</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Air Quality Section -->
            <div id="airQualitySection" class="air-quality-section" style="display: none;">
                <div class="section-title">
                    <h3>🌫️ Air Quality Index</h3>
                </div>
                <div class="aq-cards">
                    <div class="aq-card">
                        <div class="aq-label">EPA Rating</div>
                        <div class="aq-value" id="epaIndex">Good</div>
                    </div>
                    <div class="aq-card">
                        <div class="aq-label">PM2.5</div>
                        <div class="aq-value" id="pm25">--</div>
                    </div>
                    <div class="aq-card">
                        <div class="aq-label">O₃ (Ozone)</div>
                        <div class="aq-value" id="o3">--</div>
                    </div>
                    <div class="aq-card">
                        <div class="aq-label">NO₂</div>
                        <div class="aq-value" id="no2">--</div>
                    </div>
                </div>
            </div>

            <!-- Weather Alerts -->
            <div id="alertsContainer" style="display: none;">
                <div class="section-title">
                    <h3>⚠️ Weather Alerts</h3>
                </div>
                <div id="alertsList" class="alerts-list"></div>
            </div>

            <!-- 5-Day Forecast -->
            <div class="forecast-section">
                <div class="section-title">
                    <h3>📅 5-Day Forecast</h3>
                </div>
                <div class="forecast-cards" id="forecastContainer"></div>
            </div>
        </div>

        <!-- Recent Searches -->
        @if($recentSearches->count())
        <div class="recent-searches-section">
            <div class="section-title">
                <h3>🕐 Recent Searches</h3>
            </div>
            <div class="recent-searches-grid">
                @foreach($recentSearches as $search)
                <div class="recent-search-card" onclick="searchCity('{{ $search->city }}')">
                    <div class="recent-search-icon">📍</div>
                    <div class="recent-search-info">
                        <div class="recent-search-name">{{ $search->city }}</div>
                        <div class="recent-search-country">{{ $search->country }}</div>
                        <div class="recent-search-time">{{ $search->created_at->diffForHumans() }}</div>
                    </div>
                    <div class="recent-search-arrow">→</div>
                </div>
                @endforeach
            </div>
        </div>
        @endif
    </div>
</div>

<script>
const cityInput = document.getElementById('cityInput');
const searchBtn = document.getElementById('searchBtn');
const unitSelect = document.getElementById('unitSelect');
const weatherContent = document.getElementById('weatherContent');
const errorMessage = document.getElementById('errorMessage');
const suggestionsDropdown = document.getElementById('suggestionsDropdown');

searchBtn.addEventListener('click', performSearch);
cityInput.addEventListener('keypress', (e) => {
    if (e.key === 'Enter') performSearch();
});

cityInput.addEventListener('input', debounce(showSuggestions, 300));

document.addEventListener('click', (e) => {
    if (!e.target.closest('.search-wrapper')) {
        suggestionsDropdown.style.display = 'none';
    }
});

function searchCity(city) {
    cityInput.value = city;
    performSearch();
}

function showSuggestions() {
    const query = cityInput.value.trim();
    if (query.length < 2) {
        suggestionsDropdown.style.display = 'none';
        return;
    }

    fetch('{{ route("weather.search-locations") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
        },
        body: JSON.stringify({ q: query }),
    })
    .then(r => r.json())
    .then(data => {
        if (data.success && data.locations.length > 0) {
            const html = data.locations.slice(0, 8).map(loc => `
                <div class="suggestion-item" onclick="searchCity('${loc.name}')">
                    <span class="suggestion-icon">📍</span>
                    <div class="suggestion-text">
                        <div class="suggestion-name">${loc.name}</div>
                        <div class="suggestion-region">${loc.region ? loc.region + ', ' : ''}${loc.country}</div>
                    </div>
                </div>
            `).join('');
            suggestionsDropdown.innerHTML = html;
            suggestionsDropdown.style.display = 'block';
        } else {
            suggestionsDropdown.style.display = 'none';
        }
    });
}

function performSearch() {
    const city = cityInput.value.trim();
    const units = unitSelect.value;

    if (!city) {
        showError('Please enter a city name');
        return;
    }

    searchBtn.disabled = true;
    searchBtn.querySelector('span:first-child').style.display = 'none';
    searchBtn.querySelector('.spinner').style.display = 'inline-block';
    errorMessage.style.display = 'none';
    suggestionsDropdown.style.display = 'none';

    fetch('{{ route("weather.search") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
        },
        body: JSON.stringify({ city, units }),
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            displayWeather(data.weather, data.forecast, data.alerts || [], units);
            weatherContent.style.display = 'block';
            window.scrollTo({ top: weatherContent.offsetTop - 100, behavior: 'smooth' });
        } else {
            showError(data.message || 'Weather data not available');
        }
    })
    .catch(err => {
        console.error(err);
        showError('Failed to fetch weather data. Please try again.');
    })
    .finally(() => {
        searchBtn.disabled = false;
        searchBtn.querySelector('span:first-child').style.display = 'inline';
        searchBtn.querySelector('.spinner').style.display = 'none';
    });
}

function displayWeather(weather, forecast, alerts, units) {
    // Update current weather
    document.getElementById('cityName').textContent = `${weather.city}`;
    document.getElementById('locationDetails').textContent = `${weather.region || ''}, ${weather.country}`;
    document.getElementById('weatherDesc').textContent = weather.description;
    document.getElementById('temperature').textContent = `${weather.temperature}${weather.temp_unit}`;
    document.getElementById('feelsLike').textContent = `${weather.feels_like}${weather.temp_unit}`;
    document.getElementById('humidity').textContent = `${weather.humidity}%`;
    document.getElementById('windSpeed').textContent = `${weather.wind_speed} ${weather.speed_unit}`;
    document.getElementById('visibility').textContent = `${weather.visibility} ${weather.vis_unit}`;
    document.getElementById('pressure').textContent = `${weather.pressure} mb`;
    document.getElementById('clouds').textContent = `${weather.clouds}%`;
    document.getElementById('uvIndex').textContent = weather.uv_index;
    document.getElementById('coordinates').textContent = `${weather.latitude.toFixed(2)}°, ${weather.longitude.toFixed(2)}°`;

    const iconUrl = weather.icon.startsWith('http') ? weather.icon : 'https:' + weather.icon;
    document.getElementById('weatherIcon').src = iconUrl;

    // Air Quality
    if (weather.air_quality && weather.air_quality.status !== 'unavailable') {
        document.getElementById('airQualitySection').style.display = 'block';
        document.getElementById('epaIndex').textContent = weather.air_quality.us_epa_index;
        document.getElementById('pm25').textContent = weather.air_quality.pm2_5 + ' μg/m³';
        document.getElementById('o3').textContent = weather.air_quality.o3.toFixed(2);
        document.getElementById('no2').textContent = weather.air_quality.no2.toFixed(2);
    }

    // Alerts
    if (alerts && alerts.length > 0) {
        document.getElementById('alertsContainer').style.display = 'block';
        const alertsHTML = alerts.map(alert => `
            <div class="alert-box">
                <div class="alert-icon">⚠️</div>
                <div class="alert-content">
                    <div class="alert-title">${alert.headline}</div>
                    <div class="alert-description">${alert.description}</div>
                    <div class="alert-time">Expires: ${new Date(alert.expires).toLocaleString()}</div>
                </div>
            </div>
        `).join('');
        document.getElementById('alertsList').innerHTML = alertsHTML;
    } else {
        document.getElementById('alertsContainer').style.display = 'none';
    }

    // Forecast
    const forecastHTML = forecast.map(day => `
        <div class="forecast-card">
            <div class="forecast-date">${day.day_name}</div>
            <div class="forecast-date-small">${day.date_formatted}</div>
            <img src="${day.icon.startsWith('http') ? day.icon : 'https:' + day.icon}" class="forecast-icon" alt="icon">
            <div class="forecast-temp">
                <span class="forecast-max">${day.max_temp}${day.temp_unit}</span>
                <span class="forecast-min">${day.min_temp}${day.temp_unit}</span>
            </div>
            <div class="forecast-desc">${day.description}</div>
            <div class="forecast-details">
                <span>🌧️ ${day.chance_of_rain}%</span>
                <span>💨 ${day.max_wind}</span>
            </div>
        </div>
    `).join('');
    document.getElementById('forecastContainer').innerHTML = forecastHTML;
}

function showError(message) {
    document.getElementById('errorText').textContent = message;
    errorMessage.style.display = 'flex';
    weatherContent.style.display = 'none';
}

function debounce(fn, delay) {
    let timeout;
    return function (...args) {
        clearTimeout(timeout);
        timeout = setTimeout(() => fn(...args), delay);
    };
}
</script>

<style>
* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}

body {
    font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
    background:
        linear-gradient(135deg, rgba(7, 28, 58, 0.82), rgba(57, 122, 194, 0.74)),
        url('/images/weather-bg.svg') center/cover no-repeat;
    min-height: 100vh;
    color: #12304a;
}

.weather-app {
    display: flex;
    flex-direction: column;
    min-height: 100vh;
}

/* Navbar */
.navbar {
    background: rgba(255, 255, 255, 0.85);
    backdrop-filter: blur(16px);
    box-shadow: 0 8px 24px rgba(10, 36, 68, 0.12);
    padding: 1rem 0;
    position: sticky;
    top: 0;
    z-index: 100;
    border-bottom: 1px solid rgba(255, 255, 255, 0.4);
}

.navbar-container {
    max-width: 1200px;
    margin: 0 auto;
    padding: 0 20px;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.navbar-brand h1 {
    font-size: 1.8em;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
}

.unit-selector {
    padding: 8px 16px;
    border: 1px solid #ddd;
    border-radius: 8px;
    cursor: pointer;
    background: white;
    font-weight: 500;
}

/* Main Container */
.container-main {
    flex: 1;
    max-width: 1200px;
    margin: 0 auto;
    width: 100%;
    padding: 40px 20px;
}

/* Hero Search Section */
.search-hero {
    margin-bottom: 50px;
    padding: 32px 28px;
    border-radius: 28px;
    background: linear-gradient(135deg, rgba(255,255,255,0.19), rgba(255,255,255,0.10));
    backdrop-filter: blur(18px);
    box-shadow: 0 20px 50px rgba(7, 24, 44, 0.2);
    border: 1px solid rgba(255, 255, 255, 0.22);
}

.hero-content {
    text-align: center;
    color: white;
    margin-bottom: 30px;
}

.hero-content h2 {
    font-size: 2.7em;
    margin-bottom: 10px;
    font-weight: 800;
    letter-spacing: -0.02em;
    text-shadow: 0 2px 10px rgba(0,0,0,0.18);
}

.hero-content p {
    font-size: 1.08em;
    opacity: 0.95;
    margin-top: 6px;
}

.search-container {
    display: flex;
    gap: 12px;
    max-width: 700px;
    margin: 30px auto 0;
}

.search-wrapper {
    flex: 1;
    position: relative;
}

.search-input-large {
    width: 100%;
    padding: 16px 24px;
    font-size: 1.05em;
    border: 1px solid rgba(18, 48, 74, 0.08);
    border-radius: 14px;
    background: rgba(255,255,255,0.96);
    box-shadow: 0 10px 24px rgba(10, 36, 68, 0.11);
    transition: all 0.25s ease;
}

.search-input-large:focus {
    outline: none;
    box-shadow: 0 8px 30px rgba(0, 0, 0, 0.2);
}

.suggestions-dropdown {
    position: absolute;
    top: 100%;
    left: 0;
    right: 0;
    background: white;
    border-radius: 12px;
    max-height: 400px;
    overflow-y: auto;
    box-shadow: 0 8px 30px rgba(0, 0, 0, 0.15);
    z-index: 100;
    margin-top: 8px;
}

.suggestion-item {
    padding: 12px 20px;
    border-bottom: 1px solid #f0f0f0;
    cursor: pointer;
    display: flex;
    align-items: center;
    gap: 12px;
    transition: all 0.2s;
}

.suggestion-item:hover {
    background: #f8f9fa;
}

.suggestion-icon {
    font-size: 1.2em;
}

.suggestion-text {
    flex: 1;
}

.suggestion-name {
    font-weight: 600;
    color: #333;
}

.suggestion-region {
    font-size: 0.85em;
    color: #999;
}

.btn-search-large {
    padding: 16px 40px;
    background: linear-gradient(135deg, #2c7be5 0%, #1d4ed8 100%);
    color: white;
    border: none;
    border-radius: 14px;
    font-size: 1.02em;
    font-weight: 700;
    cursor: pointer;
    transition: all 0.3s;
    box-shadow: 0 12px 24px rgba(29, 78, 216, 0.24);
    white-space: nowrap;
}

.btn-search-large:hover:not(:disabled) {
    transform: translateY(-3px);
    box-shadow: 0 8px 30px rgba(255, 107, 107, 0.4);
}

.btn-search-large:disabled {
    opacity: 0.7;
    cursor: not-allowed;
}

.spinner {
    display: inline-block;
    width: 16px;
    height: 16px;
    border: 3px solid rgba(255, 255, 255, 0.3);
    border-radius: 50%;
    border-top-color: white;
    animation: spin 1s linear infinite;
    margin-left: 8px;
}

@keyframes spin {
    to { transform: rotate(360deg); }
}

/* Error Alert */
.alert-error {
    background: #ff6b6b;
    color: white;
    padding: 20px;
    border-radius: 12px;
    margin-bottom: 30px;
    display: flex;
    gap: 15px;
    align-items: flex-start;
    box-shadow: 0 4px 20px rgba(255, 107, 107, 0.3);
}

.error-icon {
    font-size: 1.5em;
    flex-shrink: 0;
}

.alert-error strong {
    display: block;
    font-size: 1.1em;
    margin-bottom: 5px;
}

.alert-error p {
    margin: 0;
}

.close-btn {
    background: none;
    border: none;
    color: white;
    font-size: 1.3em;
    cursor: pointer;
    padding: 0;
    flex-shrink: 0;
}

/* Weather Content */
.weather-content {
    animation: fadeIn 0.5s ease-in;
}

@keyframes fadeIn {
    from {
        opacity: 0;
        transform: translateY(20px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

/* Current Weather Card */
.current-weather-section {
    margin-bottom: 40px;
}

.weather-card-current {
    background: linear-gradient(135deg, rgba(255,255,255,0.95), rgba(245,250,255,0.95));
    border-radius: 24px;
    overflow: hidden;
    box-shadow: 0 16px 46px rgba(8, 30, 56, 0.16);
    border: 1px solid rgba(255, 255, 255, 0.45);
}

.card-header {
    background: linear-gradient(135deg, #214d7b 0%, #3d83cb 60%, #6fb4ff 100%);
    color: white;
    padding: 30px;
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
}

.location-info h2 {
    font-size: 2.2em;
    margin-bottom: 5px;
}

.location-details {
    font-size: 0.95em;
    opacity: 0.9;
}

.unit-selector-card {
    background: rgba(255, 255, 255, 0.2);
    color: white;
    border: 1px solid rgba(255, 255, 255, 0.3);
    padding: 8px 12px;
    border-radius: 8px;
    cursor: pointer;
}

.unit-selector-card option {
    background: #667eea;
    color: white;
}

.card-body {
    padding: 40px 30px;
}

.temperature-display {
    display: flex;
    align-items: center;
    gap: 30px;
    margin-bottom: 40px;
    border-bottom: 1px solid #f0f0f0;
    padding-bottom: 40px;
}

.weather-icon-large {
    width: 120px;
    height: 120px;
}

.temp-info {
    flex: 1;
}

.temp-value {
    font-size: 3.5em;
    font-weight: 700;
    color: #667eea;
    line-height: 1;
}

.weather-desc {
    font-size: 1.2em;
    color: #999;
    text-transform: capitalize;
    margin-top: 10px;
}

.stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
    gap: 15px;
}

.stat-box {
    background: linear-gradient(135deg, #f7fbff 0%, #eef6ff 100%);
    padding: 20px;
    border-radius: 14px;
    display: flex;
    gap: 12px;
    align-items: center;
    transition: all 0.3s;
    border: 1px solid rgba(45, 122, 224, 0.08);
}

.stat-box:hover {
    background: #f0f0f0;
    transform: translateY(-2px);
}

.stat-icon {
    font-size: 1.8em;
}

.stat-label {
    font-size: 0.85em;
    color: #999;
    text-transform: uppercase;
    font-weight: 600;
}

.stat-value {
    font-size: 1.3em;
    font-weight: 700;
    color: #333;
}

/* Air Quality Section */
.air-quality-section {
    background: rgba(255, 255, 255, 0.92);
    border-radius: 20px;
    padding: 30px;
    margin-bottom: 40px;
    box-shadow: 0 10px 40px rgba(0, 0, 0, 0.1);
}

.section-title h3 {
    font-size: 1.5em;
    color: #333;
    margin-bottom: 20px;
}

.aq-cards {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(140px, 1fr));
    gap: 15px;
}

.aq-card {
    background: linear-gradient(135deg, #f8f9fa 0%, #f0f0f0 100%);
    padding: 20px;
    border-radius: 12px;
    text-align: center;
    border: 1px solid #e0e0e0;
}

.aq-label {
    font-size: 0.85em;
    color: #999;
    margin-bottom: 10px;
    font-weight: 600;
}

.aq-value {
    font-size: 1.3em;
    font-weight: 700;
    color: #667eea;
}

/* Alerts */
#alertsContainer {
    background: white;
    border-radius: 20px;
    padding: 30px;
    margin-bottom: 40px;
    box-shadow: 0 10px 40px rgba(0, 0, 0, 0.1);
}

.alerts-list {
    display: grid;
    gap: 15px;
}

.alert-box {
    background: #fff3cd;
    border-left: 5px solid #ffc107;
    padding: 20px;
    border-radius: 12px;
    display: flex;
    gap: 15px;
}

.alert-icon {
    font-size: 1.5em;
}

.alert-content {
    flex: 1;
}

.alert-title {
    font-weight: 600;
    color: #333;
    margin-bottom: 5px;
}

.alert-description {
    font-size: 0.95em;
    color: #666;
    margin-bottom: 10px;
}

.alert-time {
    font-size: 0.85em;
    color: #999;
}

/* Forecast Section */
.forecast-section {
    background: rgba(255, 255, 255, 0.92);
    border-radius: 20px;
    padding: 30px;
    box-shadow: 0 10px 40px rgba(0, 0, 0, 0.1);
}

.forecast-cards {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(140px, 1fr));
    gap: 15px;
}

.forecast-card {
    background: linear-gradient(135deg, #f7fbff 0%, #eef6ff 100%);
    padding: 20px;
    border-radius: 14px;
    text-align: center;
    cursor: pointer;
    transition: all 0.3s;
    border: 1px solid rgba(29, 78, 216, 0.08);
}

.forecast-card:hover {
    background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%);
    color: white;
    transform: translateY(-5px);
    border-color: rgba(255,255,255,0.45);
}

.forecast-date {
    font-weight: 700;
    font-size: 1.05em;
    margin-bottom: 5px;
}

.forecast-date-small {
    font-size: 0.85em;
    opacity: 0.7;
    margin-bottom: 10px;
}

.forecast-icon {
    width: 50px;
    height: 50px;
    margin: 10px auto;
}

.forecast-temp {
    display: flex;
    justify-content: center;
    gap: 10px;
    font-size: 0.95em;
    margin-bottom: 10px;
}

.forecast-max {
    font-weight: 700;
}

.forecast-min {
    opacity: 0.7;
}

.forecast-desc {
    font-size: 0.85em;
    text-transform: capitalize;
    margin-bottom: 10px;
}

.forecast-details {
    display: flex;
    justify-content: space-around;
    font-size: 0.8em;
    opacity: 0.8;
}

/* Recent Searches */
.recent-searches-section {
    background: rgba(255, 255, 255, 0.92);
    border-radius: 20px;
    padding: 30px;
    margin-top: 40px;
    box-shadow: 0 10px 40px rgba(0, 0, 0, 0.1);
}

.recent-searches-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
    gap: 15px;
}

.recent-search-card {
    background: #f8f9fa;
    padding: 20px;
    border-radius: 12px;
    cursor: pointer;
    transition: all 0.3s;
    display: flex;
    align-items: center;
    gap: 15px;
}

.recent-search-card:hover {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    transform: translateY(-3px);
}

.recent-search-icon {
    font-size: 1.8em;
}

.recent-search-info {
    flex: 1;
}

.recent-search-name {
    font-weight: 600;
    font-size: 1.05em;
}

.recent-search-country {
    font-size: 0.85em;
    opacity: 0.7;
}

.recent-search-time {
    font-size: 0.75em;
    opacity: 0.6;
}

.recent-search-arrow {
    font-size: 1.2em;
    opacity: 0.5;
}

/* Responsive */
@media (max-width: 768px) {
    .navbar-brand h1 {
        font-size: 1.4em;
    }

    .hero-content h2 {
        font-size: 1.8em;
    }

    .search-container {
        flex-direction: column;
    }

    .temperature-display {
        flex-direction: column;
        text-align: center;
    }

    .weather-icon-large {
        width: 80px;
        height: 80px;
    }

    .temp-value {
        font-size: 2.5em;
    }

    .card-header {
        flex-direction: column;
        gap: 15px;
    }

    .stats-grid {
        grid-template-columns: repeat(2, 1fr);
    }

    .forecast-cards {
        grid-template-columns: repeat(2, 1fr);
    }

    .recent-searches-grid {
        grid-template-columns: 1fr;
    }
}
</style>
@endsection