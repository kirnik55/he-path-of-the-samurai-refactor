@extends('layouts.app')

@section('content')
<div class="container pb-5">
  <h1 class="h4 mb-3">Обзор</h1>

  {{-- верхние карточки: краткое состояние ISS --}}
  <div class="row g-3 mb-3">
    <div class="col-6 col-md-3">
      <div class="border rounded p-2 text-center card-animated">
        <div class="small text-muted">Скорость МКС</div>
        <div class="fs-4">
          {{ isset(($iss['payload'] ?? [])['velocity']) ? number_format($iss['payload']['velocity'],0,'',' ') : '—' }}
        </div>
      </div>
    </div>
    <div class="col-6 col-md-3">
      <div class="border rounded p-2 text-center card-animated">
        <div class="small text-muted">Высота МКС</div>
        <div class="fs-4">
          {{ isset(($iss['payload'] ?? [])['altitude']) ? number_format($iss['payload']['altitude'],0,'',' ') : '—' }}
        </div>
      </div>
    </div>
  </div>

  {{-- быстрые ссылки на контексты --}}
  <div class="row g-3">
    <div class="col-md-3">
      <div class="card card-animated h-100">
        <div class="card-body">
          <h5 class="card-title">МКС / орбита</h5>
          <p class="small text-muted">Карта, трек, скорость и высота станции.</p>
          <a href="{{ url('/iss') }}" class="btn btn-sm btn-primary">Открыть ISS-панель</a>
        </div>
      </div>
    </div>

    <div class="col-md-3">
      <div class="card card-animated h-100">
        <div class="card-body">
          <h5 class="card-title">OSDR датасеты</h5>
          <p class="small text-muted">Список наборов данных из OSDR.</p>
          <a href="{{ url('/osdr') }}" class="btn btn-sm btn-primary">Открыть OSDR</a>
        </div>
      </div>
    </div>

    <div class="col-md-3">
      <div class="card card-animated h-100">
        <div class="card-body">
          <h5 class="card-title">JWST галерея</h5>
          <p class="small text-muted">Просмотр последних изображений JWST.</p>
          <a href="{{ url('/jwst') }}" class="btn btn-sm btn-primary">Перейти к JWST</a>
        </div>
      </div>
    </div>

    <div class="col-md-3">
      <div class="card card-animated h-100">
        <div class="card-body">
          <h5 class="card-title">Астрособытия</h5>
          <p class="small text-muted">Сводка событий из AstronomyAPI.</p>
          <a href="{{ url('/astro') }}" class="btn btn-sm btn-primary">Открыть события</a>
        </div>
      </div>
    </div>
  </div>

  <div class="row g-3 mt-3">
    <div class="col-md-4">
      <div class="card card-animated h-100">
        <div class="card-body">
          <h5 class="card-title">CMS-эксперимент</h5>
          <p class="small text-muted">Небезопасный блок <code>dashboard_experiment</code> из базы.</p>
          <a href="{{ url('/cms/dashboard-experiment') }}" class="btn btn-sm btn-outline-secondary">Открыть CMS-демо</a>
        </div>
      </div>
    </div>
  </div>
</div>
@endsection
