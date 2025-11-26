@extends('layouts.app')

@section('content')
<div class="container pb-5">
  <h1 class="h4 mb-3">CMS — экспериментальный блок</h1>

  <div class="card card-animated">
    <div class="card-header fw-semibold">CMS — блок из БД (dashboard_experiment)</div>
    <div class="card-body">
      @php
        try {
          $___b = DB::selectOne("SELECT content FROM cms_blocks WHERE slug='dashboard_experiment' AND is_active = TRUE LIMIT 1");
          echo $___b ? $___b->content : '<div class="text-muted">блок не найден</div>';
        } catch (\Throwable $e) {
          echo '<div class="text-danger">ошибка БД: '.e($e->getMessage()).'</div>';
        }
      @endphp
    </div>
  </div>
</div>
@endsection
