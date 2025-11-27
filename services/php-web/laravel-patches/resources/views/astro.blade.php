@extends('layouts.app')

@section('content')
<div class="container pb-5">
  <h1 class="h4 mb-3">Астрономические события (AstronomyAPI)</h1>

<div class="card-body">
  <div class="d-flex justify-content-between align-items-center mb-2">
    <form id="astroForm" class="row g-2 align-items-center">
      <div class="col-auto">
        <input type="number" step="0.0001" class="form-control form-control-sm" name="lat" value="55.7558" placeholder="lat">
      </div>
      <div class="col-auto">
        <input type="number" step="0.0001" class="form-control form-control-sm" name="lon" value="37.6176" placeholder="lon">
      </div>
      <div class="col-auto">
        <input type="number" min="1" max="30" class="form-control form-control-sm" name="days" value="7" style="width:90px" title="дней">
      </div>
      <div class="col-auto">
        <button class="btn btn-sm btn-primary" type="submit">Показать</button>
      </div>
    </form>

    <div class="d-flex align-items-center gap-2">
      <span class="small text-muted d-none d-md-inline">Сортировка:</span>
      <select id="astroSortColumn" class="form-select form-select-sm">
        <option value="when">По времени</option>
        <option value="name">По объекту</option>
        <option value="type">По событию</option>
        <option value="extra">По доп.полю</option>
      </select>
      <select id="astroSortDir" class="form-select form-select-sm" style="width:110px">
        <option value="asc">По возрастанию</option>
        <option value="desc">По убыванию</option>
      </select>
    </div>
  </div>

      <div class="table-responsive">
        <table class="table table-sm align-middle">
          <thead>
            <tr><th>#</th><th>Тело</th><th>Событие</th><th>Когда (UTC)</th><th>Дополнительно</th></tr>
          </thead>
          <tbody id="astroBody">
            <tr><td colspan="5" class="text-muted">нет данных</td></tr>
          </tbody>
        </table>
      </div>

      <details class="mt-2">
        <summary>Полный JSON</summary>
        <pre id="astroRaw" class="bg-light rounded p-2 small m-0" style="white-space:pre-wrap"></pre>
      </details>
    </div>
  </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
  const form = document.getElementById('astroForm');
  const body = document.getElementById('astroBody');
  const raw  = document.getElementById('astroRaw');

  const sortColSel = document.getElementById('astroSortColumn');
  const sortDirSel = document.getElementById('astroSortDir');

  // Хранилище всех найденных событий
  let astroRows = [];

  function normalize(node){
    const name = node.name || node.body || node.object || node.target || '';
    const type = node.type || node.event_type || node.category || node.kind || '';
    const when = node.time || node.date || node.occursAt || node.peak || node.instant || '';
    const extra = node.magnitude || node.mag || node.altitude || node.note || '';
    return {name, type, when, extra};
  }

  function collect(root){
    const rows = [];
    (function dfs(x){
      if (!x || typeof x !== 'object') return;
      if (Array.isArray(x)) { x.forEach(dfs); return; }
      if ((x.type || x.event_type || x.category) && (x.name || x.body || x.object || x.target)) {
        rows.push(normalize(x));
      }
      Object.values(x).forEach(dfs);
    })(root);
    return rows;
  }

  function renderTable(rows){
    if (!rows.length) {
      body.innerHTML = '<tr><td colspan="5" class="text-muted">события не найдены</td></tr>';
      return;
    }
    body.innerHTML = rows.slice(0,200).map((r,i)=>`
      <tr>
        <td>${i+1}</td>
        <td>${r.name || '—'}</td>
        <td>${r.type || '—'}</td>
        <td><code>${r.when || '—'}</code></td>
        <td>${r.extra || ''}</td>
      </tr>
    `).join('');
  }

  function parseMaybeDate(val){
    if (!val) return null;
    const d = new Date(val);
    return isNaN(d.getTime()) ? null : d;
  }

  function compareRows(a, b, col, dir){
    const mul = dir === 'desc' ? -1 : 1;

    if (col === 'when') {
      const da = parseMaybeDate(a.when);
      const db = parseMaybeDate(b.when);
      if (da && db) {
        if (da < db) return -1 * mul;
        if (da > db) return  1 * mul;
        return 0;
      }
    }

    const va = (a[col] ?? '').toString().toLowerCase();
    const vb = (b[col] ?? '').toString().toLowerCase();

    if (va < vb) return -1 * mul;
    if (va > vb) return  1 * mul;
    return 0;
  }

  function applySortAndRender(){
    if (!astroRows.length) {
      renderTable([]);
      return;
    }
    const col = sortColSel.value || 'when';
    const dir = sortDirSel.value || 'asc';
    const sorted = [...astroRows].sort((a,b)=>compareRows(a,b,col,dir));
    renderTable(sorted);
  }

  async function load(q){
    body.innerHTML = '<tr><td colspan="5" class="text-muted">Загрузка…</td></tr>';
    const url = '/api/astro/events?' + new URLSearchParams(q).toString();
    try{
      const r  = await fetch(url);
      const js = await r.json();
      raw.textContent = JSON.stringify(js, null, 2);

      astroRows = collect(js);
      applySortAndRender();
    }catch(e){
      console.error(e);
      body.innerHTML = '<tr><td colspan="5" class="text-danger">ошибка загрузки</td></tr>';
    }
  }

  form.addEventListener('submit', ev=>{
    ev.preventDefault();
    const q = Object.fromEntries(new FormData(form).entries());
    load(q);
  });

  // реагируем на смену сортировки
  sortColSel.addEventListener('change', applySortAndRender);
  sortDirSel.addEventListener('change', applySortAndRender);

  // автозагрузка
  load({lat: form.lat.value, lon: form.lon.value, days: form.days.value});
});
</script>
@endsection

