@extends('layouts.app')

@section('content')
<div class="container pb-5">
  <h1 class="h4 mb-3">МКС — положение и орбита</h1>

  {{-- верхние карточки --}}
  <div class="row g-3 mb-3">
    <div class="col-6 col-md-3">
      <div class="border rounded p-2 text-center card-animated">
        <div class="small text-muted">Скорость МКС</div>
        <div class="fs-4">
          {{ isset(($iss['payload'] ?? [])['velocity']) ? number_format($iss['payload']['velocity'], 0, '', ' ') : '—' }}
        </div>
      </div>
    </div>
    <div class="col-6 col-md-3">
      <div class="border rounded p-2 text-center card-animated">
        <div class="small text-muted">Высота МКС</div>
        <div class="fs-4">
          {{ isset(($iss['payload'] ?? [])['altitude']) ? number_format($iss['payload']['altitude'], 0, '', ' ') : '—' }}
        </div>
      </div>
    </div>
  </div>

  @php
    $payload   = $iss['payload'] ?? [];
    $speedVal  = $payload['velocity'] ?? null;   // км/ч
    $altVal    = $payload['altitude'] ?? null;   // км

    // Нормируем значения в проценты для полосок
    // “нормальная” скорость ≈ 28 000 км/ч, высота ≈ 450 км
    $speedPct = $speedVal ? max(0, min(100, $speedVal / 28000 * 100)) : 0;
    $altPct   = $altVal   ? max(0, min(100, $altVal   / 450   * 100)) : 0;
  @endphp

  <div class="row g-3 mb-4">
    <div class="col-lg-6">
      <div class="metric-bar">
        <div class="metric-bar-label">
          <span>Скорость МКС</span>
          <span>
            @if($speedVal)
              {{ number_format($speedVal,0,'',' ') }} км/ч
            @else
              нет данных
            @endif
          </span>
        </div>
        <div class="metric-bar-track">
          <div class="metric-bar-fill metric-bar-fill-speed" style="width: {{ $speedPct }}%;"></div>
        </div>
      </div>
    </div>

    <div class="col-lg-6">
      <div class="metric-bar">
        <div class="metric-bar-label">
          <span>Высота орбиты</span>
          <span>
            @if($altVal)
              {{ number_format($altVal,0,'',' ') }} км
            @else
              нет данных
            @endif
          </span>
        </div>
        <div class="metric-bar-track">
          <div class="metric-bar-fill metric-bar-fill-alt" style="width: {{ $altPct }}%;"></div>
        </div>
      </div>
    </div>
  </div>

  <div class="row g-3">
    <div class="col-lg-7">
      <div class="card shadow-sm h-100 card-animated">
        <div class="card-body">
          <h5 class="card-title">Карта и трек МКС</h5>
          <div id="map" class="rounded mb-2 border" style="height:300px"></div>
          <p class="small text-muted mb-0">
            Данные поступают из rust_iss (/api/iss/last и /api/iss/trend).
          </p>
        </div>
      </div>
    </div>

    <div class="col-lg-5">
      <div class="card shadow-sm h-100 card-animated">
        <div class="card-body">
          <h5 class="card-title">Графики скорости и высоты</h5>
          <div class="mb-2">
            <canvas id="issSpeedChart" height="120"></canvas>
          </div>
          <div>
            <canvas id="issAltChart" height="120"></canvas>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', async function () {
  if (typeof L === 'undefined' || typeof Chart === 'undefined') {
    console.warn('Leaflet or Chart.js not found');
    return;
  }

  // стартовые данные из PHP
  const last = @json(($iss['payload'] ?? []));
  let lat0 = Number(last.latitude || 0);
  let lon0 = Number(last.longitude || 0);

  // карта (ТОЛЬКО маркер, без линии)
  const map = L.map('map', { attributionControl: false }).setView(
    [lat0 || 0, lon0 || 0],
    lat0 ? 3 : 2
  );
  L.tileLayer(
    'https://{s}.tile.openstreetmap.de/{z}/{x}/{y}.png',
    { noWrap: true }
  ).addTo(map);

  const marker = L.marker([lat0 || 0, lon0 || 0]).addTo(map).bindPopup('МКС');

  // графики
  const speedChart = new Chart(document.getElementById('issSpeedChart'), {
    type: 'line',
    data: {
      labels: [],
      datasets: [{
        label: 'Скорость, км/ч',
        data: []
      }]
    },
    options: {
      responsive: true,
      scales: { x: { display: false } }
    }
  });

  const altChart = new Chart(document.getElementById('issAltChart'), {
    type: 'line',
    data: {
      labels: [],
      datasets: [{
        label: 'Высота, км',
        data: []
      }]
    },
    options: {
      responsive: true,
      scales: { x: { display: false } }
    }
  });

  async function loadTrend() {
    try {
      const resp = await fetch('/api/iss/trend');
      if (!resp.ok) {
        console.error('trend HTTP error', resp.status);
        return;
      }
      const js = await resp.json();

      // формат, который ты показывал:
      // { movement, delta_km, dt_sec, velocity_kmh, from_time, to_time, from_lat, from_lon, to_lat, to_lon }
      if (js && js.velocity_kmh && js.from_time && js.to_time) {
        const fromTime = new Date(js.from_time);
        const toTime   = new Date(js.to_time);

        const labels = [
          fromTime.toLocaleTimeString(),
          toTime.toLocaleTimeString()
        ];

        const speed = Number(js.velocity_kmh) || 0;

        const lastPayload = @json(($iss['payload'] ?? []));
        const alt = Number(lastPayload.altitude || 0);

        // графики: рисуем постоянные значения (две точки)
        speedChart.data.labels = labels;
        speedChart.data.datasets[0].data = [speed, speed];
        speedChart.update();

        altChart.data.labels = labels;
        altChart.data.datasets[0].data = [alt, alt];
        altChart.update();

        // маркер перемещаем в точку "to"
        if (js.to_lat != null && js.to_lon != null) {
          marker.setLatLng([js.to_lat, js.to_lon]);
        }

        return;
      }

      // на будущее: если когда-то появится формат с points[]
      const points = Array.isArray(js.points) ? js.points : [];
      if (!points.length) {
        console.warn('trend: no points');
        return;
      }

      const labels = points.map(p => new Date(p.at).toLocaleTimeString());
      const speeds = points.map(p => p.velocity);
      const alts   = points.map(p => p.altitude);

      speedChart.data.labels = labels;
      speedChart.data.datasets[0].data = speeds;
      speedChart.update();

      altChart.data.labels = labels;
      altChart.data.datasets[0].data = alts;
      altChart.update();

      const lastPoint = points[points.length - 1];
      if (lastPoint && lastPoint.lat != null && lastPoint.lon != null) {
        marker.setLatLng([lastPoint.lat, lastPoint.lon]);
      }
    } catch (e) {
      console.error('trend fetch error', e);
    }
  }

  await loadTrend();
  setInterval(loadTrend, 15000);
});
</script>

@endsection
