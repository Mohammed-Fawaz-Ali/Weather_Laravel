<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Weather — VisualCrossing</title>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;800&display=swap" rel="stylesheet">
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <style>
    :root{--bg:#0f1724;--card:#0b1220;--muted:#9aa4b2;--accent:#60a5fa}
    *{box-sizing:border-box;font-family:Inter,system-ui,Arial}
    body{margin:0;background:linear-gradient(180deg,#071026 0%,#071a2b 100%);color:#e6eef6;min-height:100vh;display:flex;align-items:center;justify-content:center;padding:28px;transition:background 600ms}
    .wrap{width:100%;max-width:1100px}
    .card{background:rgba(255,255,255,0.03);border-radius:14px;padding:16px;margin-bottom:18px;box-shadow:0 8px 30px rgba(2,6,23,0.6)}
    .controls{display:flex;gap:8px;align-items:center}
    input[type=text], select{flex:1;padding:10px 12px;border-radius:10px;border:1px solid rgba(255,255,255,0.06);background:transparent;color:inherit}
    button{background:var(--accent);border:none;color:#04263a;padding:10px 14px;border-radius:10px;font-weight:700;cursor:pointer}
    .grid{display:grid;grid-template-columns:1fr 380px;gap:18px}
    .summary{display:flex;gap:12px;align-items:center}
    .big-temp{font-size:56px;font-weight:800}
    .meta{color:var(--muted);font-size:14px}
    .days{display:flex;gap:10px;overflow:auto;padding-top:12px}
    .day{min-width:120px;background:rgba(255,255,255,0.03);padding:12px;border-radius:12px;text-align:center;backdrop-filter:blur(6px)}
    .day .icon{font-size:28px}
    .forecast-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(120px,1fr));gap:8px}
    .details-row{display:flex;flex-direction:column;gap:8px}
    .small{font-size:13px;color:var(--muted)}
    .chart-card canvas{width:100%!important}
  </style>
</head>
<body>
  <div class="wrap">
    <h1 style="margin:0 0 12px 0;font-weight:800">Weather</h1>

    <div class="card">
      <div class="controls">
        <input id="city" type="text" placeholder="City (e.g. Hyderabad)" value="">
        <input id="state" type="text" placeholder="State (optional)" value="">
        <select id="country" style="max-width:160px">
          <option value="">Country (optional)</option>
          <option value="IN">India</option>
          <option value="US">United States</option>
          <option value="GB">United Kingdom</option>
          <option value="AU">Australia</option>
          <option value="CA">Canada</option>
        </select>
        <button id="fetchBtn">Get Weather</button>
      </div>
      <p class="meta" style="margin-top:8px">Data by Visual Crossing. You can also enter `lat,lng` in the City field.</p>
    </div>

    <div class="grid">
      <div>
        <div class="card" id="overviewCard">
          <div id="overview" class="summary">
            <div>
              <div id="locationName" style="font-weight:700">Loading...</div>
              <div id="conditions" class="meta">—</div>
            </div>
            <div style="margin-left:auto;text-align:right">
              <div id="temp" class="big-temp">—°</div>
              <div id="feels" class="meta">Feels like —</div>
            </div>
          </div>
          <div id="daysList" class="days" style="margin-top:12px"></div>
        </div>

        <div class="card chart-card" style="margin-top:12px">
          <canvas id="tempChart" height="160"></canvas>
        </div>
        <div class="card chart-card" style="margin-top:12px">
          <canvas id="hwChart" height="140"></canvas>
        </div>
      </div>

      <div>
        <div class="card">
          <div style="font-weight:700;margin-bottom:8px">Details</div>
          <div id="details" class="meta">Choose a location and press Get Weather.</div>
        </div>
        <div class="card" style="margin-top:12px">
          <div style="font-weight:700;margin-bottom:8px">Forecast</div>
          <div id="forecast" class="meta small">—</div>
          <div class="forecast-grid" id="forecastGrid" style="margin-top:10px"></div>
        </div>
      </div>
    </div>
  </div>

  <script>
    const fetchBtn = document.getElementById('fetchBtn');
    const cityInput = document.getElementById('city');
    const stateInput = document.getElementById('state');
    const countryInput = document.getElementById('country');
    const locationName = document.getElementById('locationName');
    const conditions = document.getElementById('conditions');
    const temp = document.getElementById('temp');
    const feels = document.getElementById('feels');
    const daysList = document.getElementById('daysList');
    const details = document.getElementById('details');
    const forecast = document.getElementById('forecast');
    const forecastGrid = document.getElementById('forecastGrid');

    let chart;

    function buildLocation() {
      const city = cityInput.value.trim();
      const state = stateInput.value.trim();
      const country = countryInput.value.trim();
      if (!city) return '17.9942,79.6072';
      // if lat,lng provided
      if (/^-?\d+(\.\d+)?\s*,\s*-?\d+(\.\d+)?$/.test(city)) return city;
      let parts = [city];
      if (state) parts.push(state);
      if (country) parts.push(country);
      return parts.join(',');
    }

    async function getWeather(loc) {
      locationName.textContent = 'Loading...';
      conditions.textContent = '';
      temp.textContent = '—°';
      details.textContent = '';
      daysList.innerHTML = '';
      forecast.textContent = '';
      forecastGrid.innerHTML = '';

      try {
        const res = await fetch('/api/weather?location=' + encodeURIComponent(loc));
        if (!res.ok) throw new Error('Fetch failed');
        const data = await res.json();
        renderWeather(data);
      } catch (err) {
        locationName.textContent = 'Error';
        details.textContent = err.message;
        document.body.style.background = 'linear-gradient(180deg,#2b2733,#1b2130)';
      }
    }

    function renderWeather(data) {
      const address = data.resolvedAddress || (data.address || 'Unknown location');
      locationName.textContent = address;
      const current = data.currentConditions || data.current || {};
      const today = data.days && data.days[0] ? data.days[0] : null;
      const curTemp = current.temp ?? (today ? today.temp : null);
      temp.textContent = curTemp !== null ? Math.round(curTemp) + '°' : '—°';
      conditions.textContent = current.conditions || (today ? today.conditions : '—');
      feels.textContent = current.feelslike ? 'Feels like ' + Math.round(current.feelslike) + '°' : '';

      // Details
      const detailsList = [];
      if (current.humidity !== undefined) detailsList.push('Humidity: ' + current.humidity + '%');
      if (current.windspeed !== undefined) detailsList.push('Wind: ' + current.windspeed + (data.windunit || ' mph'));
      if (today) detailsList.push('High/Low: ' + Math.round(today.tempmax) + '° / ' + Math.round(today.tempmin) + '°');
      details.textContent = detailsList.join(' · ');

      // Forecast summary
      if (data.days && data.days.length) {
        forecast.textContent = '';
        // forecast grid with icons
        data.days.slice(0,7).forEach(d => {
          const card = document.createElement('div'); card.className='day';
          const ic = iconFor(d.icon || d.conditions || '');
          card.innerHTML = `<div class="icon">${ic}</div><div style="font-weight:700;margin-top:6px">${d.datetime}</div><div style="font-size:18px;margin-top:6px">${Math.round(d.temp)}°</div><div class="meta" style="margin-top:6px">${d.conditions}</div>`;
          forecastGrid.appendChild(card);
        });

        // Days list for scrollable summary
        daysList.innerHTML = '';
        const labels = [];
        const temps = [];
        data.days.slice(0,14).forEach(d => {
          const el = document.createElement('div'); el.className='day';
          const date = d.datetime;
          const ic = iconFor(d.icon || d.conditions || '');
          el.innerHTML = `<div class="icon">${ic}</div><div style="font-weight:700">${date}</div><div style="font-size:18px;margin-top:6px">${Math.round(d.temp)}°</div><div class="meta" style="margin-top:6px">${d.conditions}</div>`;
          daysList.appendChild(el);
          labels.push(date);
          temps.push(d.temp);
        });

        // Chart datasets
        const humidity = data.days.slice(0,14).map(d => d.humidity ?? (d.hours && d.hours[0] ? d.hours[0].humidity : null));
        const wind = data.days.slice(0,14).map(d => d.windspeed ?? (d.hours && d.hours[0] ? d.hours[0].windspeed : null));
        // Chart
        const ctx = document.getElementById('tempChart').getContext('2d');
        if (chart) chart.destroy();
        chart = new Chart(ctx, {
          type: 'line',
          data: {
            labels,
            datasets: [{
              label: 'Temp',
              data: temps,
              borderColor: '#ffd166',
              backgroundColor: 'rgba(255,209,102,0.12)',
              tension: 0.3,
              fill: true,
              pointRadius:4
            }]
          },
          options: {
            responsive:true,
            scales:{
              y:{beginAtZero:false, ticks:{color:'#9aa4b2'}},
              x:{ticks:{color:'#9aa4b2'}}
            },
            plugins:{legend:{display:false}}
          }
        });

        const ctx2 = document.getElementById('hwChart').getContext('2d');
        if (window.hwChart) window.hwChart.destroy();
        window.hwChart = new Chart(ctx2, {
          type: 'bar',
          data: {
            labels,
            datasets: [
              { label: 'Humidity %', data: humidity, backgroundColor: 'rgba(96,165,250,0.18)', yAxisID: 'y' },
              { label: 'Wind', data: wind, type: 'line', borderColor: '#f87171', backgroundColor: 'rgba(248,113,113,0.06)', yAxisID: 'y1', tension:0.3 }
            ]
          },
          options: {
            responsive:true,
            scales:{
              y:{position:'left',ticks:{color:'#9aa4b2'}},
              y1:{position:'right',grid:{display:false},ticks:{color:'#9aa4b2'}}
            }
          }
        });
      }

      // dynamic background based on today's condition
      setBackground(today ? (today.icon || today.conditions) : (current.conditions||''));


    function iconFor(cond){
      cond = (cond||'').toLowerCase();
      if (/rain|shower|drizzle/.test(cond)) return '🌧️';
      if (/thunder|storm/.test(cond)) return '⛈️';
      if (/snow|sleet/.test(cond)) return '❄️';
      if (/clear|sun/.test(cond)) return '☀️';
      if (/cloud/.test(cond)) return '☁️';
      if (/fog|mist|haze/.test(cond)) return '🌫️';
      return '🌤️';
    }

    function setBackground(cond){
      cond = (cond||'').toLowerCase();
      if (/rain|drizzle/.test(cond)) document.body.style.background = 'linear-gradient(180deg,#0f1724 0%,#0b3a4a 100%)';
      else if (/thunder|storm/.test(cond)) document.body.style.background = 'linear-gradient(180deg,#1b1f3a 0%,#0b1020 100%)';
      else if (/snow/.test(cond)) document.body.style.background = 'linear-gradient(180deg,#e6f0ff 0%,#cfe6ff 100%)';
      else if (/clear|sun/.test(cond)) document.body.style.background = 'linear-gradient(180deg,#fff1b6 0%,#ffd166 100%)';
      else if (/cloud/.test(cond)) document.body.style.background = 'linear-gradient(180deg,#cfd8e3 0%,#9fb2c8 100%)';
      else document.body.style.background = 'linear-gradient(180deg,#071026 0%,#071a2b 100%)';
    }
      // Chart
      const ctx = document.getElementById('tempChart').getContext('2d');
      if (chart) chart.destroy();
      chart = new Chart(ctx, {
        type: 'line',
        data: {
          labels,
          datasets: [{
            label: 'Temp',
            data: temps,
            borderColor: '#60a5fa',
            backgroundColor: 'rgba(96,165,250,0.12)',
            tension: 0.3,
            fill: true,
            pointRadius:4
          }]
        },
        options: {
          responsive:true,
          scales:{
            y:{beginAtZero:false, ticks:{color:'#9aa4b2'}},
            x:{ticks:{color:'#9aa4b2'}}
          },
          plugins:{legend:{display:false}}
        }
      });
    }

    fetchBtn.addEventListener('click', () => getWeather(buildLocation()));
    cityInput.addEventListener('keydown', e => { if (e.key === 'Enter') getWeather(buildLocation()); });

    // initial load (centered location)
    cityInput.value = '17.9942,79.6072';
    getWeather(buildLocation());
  </script>
</body>
</html>
