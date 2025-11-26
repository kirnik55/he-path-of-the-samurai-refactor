<!doctype html>
<html lang="ru">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Space Dashboard</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    .page-fade-enter {
        opacity: 0;
        transform: translateY(8px);
        animation: pageFadeIn 0.4s ease-out forwards;
    }

    @keyframes pageFadeIn {
        from {
            opacity: 0;
            transform: translateY(8px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .card-animated {
        transition: transform 0.2s ease, box-shadow 0.2s ease;
    }

    .card-animated:hover {
        transform: translateY(-4px) scale(1.01);
        box-shadow: 0 0.75rem 2rem rgba(0, 0, 0, 0.15);
    }

    .badge-pulse {
        position: relative;
        overflow: visible;
    }

    .badge-pulse::after {
        content: "";
        position: absolute;
        inset: -4px;
        border-radius: 999px;
        border: 2px solid rgba(25, 135, 84, 0.35);
        opacity: 0;
        animation: badgePulse 1.5s ease-out infinite;
    }

    @keyframes badgePulse {
        0% {
            opacity: 0.6;
            transform: scale(0.9);
        }
        100% {
            opacity: 0;
            transform: scale(1.3);
        }
    }

    .metric-bar{
        margin-bottom: 0.75rem;
    }
    .metric-bar-label{
        display:flex;
        justify-content:space-between;
        font-size:.85rem;
        margin-bottom:.15rem;
    }
    .metric-bar-track{
        height:8px;
        border-radius:999px;
        background:rgba(0,0,0,.06);
        overflow:hidden;
    }
    .metric-bar-fill{
        height:100%;
        border-radius:999px;
        transition:width .4s ease-out;
    }
    .metric-bar-fill-speed{
        background:linear-gradient(90deg, #0d6efd, #20c997);
    }
    .metric-bar-fill-alt{
        background:linear-gradient(90deg, #6610f2, #fd7e14);
    }

</style>
  <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"/>
  <style>#map{height:340px}</style>
  <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
<nav class="navbar navbar-expand-lg bg-body-tertiary mb-3">
  <div class="container">
    <a class="navbar-brand" href="{{ url('/dashboard') }}">Dashboard</a>

    <div class="navbar-nav">
      <a class="nav-link" href="{{ url('/iss') }}">ISS</a>
      <a class="nav-link" href="{{ url('/osdr') }}">OSDR</a>
      <a class="nav-link" href="{{ url('/jwst') }}">JWST</a>
      <a class="nav-link" href="{{ url('/astro') }}">Astro</a>
      <a class="nav-link" href="{{ url('/cms/dashboard-experiment') }}">CMS</a>
    </div>
  </div>
</nav>
<div class="container py-3 page-fade-enter">
    @yield('content')
</div>
