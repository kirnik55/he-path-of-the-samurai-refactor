@extends('layouts.app')

@section('content')
<div class="container py-3">
  <h3 class="mb-3">NASA OSDR</h3>

  {{-- Источник + панель поиска/сортировки (как на Astro) --}}
  <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-2 mb-2">
    <div class="small text-muted">Источник {{ $src }}</div>

    <form id="osdrFilterForm" class="row row-cols-auto g-2 align-items-center">
      <div class="col">
        <input type="text"
               class="form-control form-control-sm"
               id="osdrSearch"
               placeholder="Ключевые слова (id, title, URL)">
      </div>
      <div class="col">
        <select class="form-select form-select-sm" id="osdrSortColumn">
          <option value="none">Без сортировки</option>
          <option value="dataset_id">dataset_id</option>
          <option value="title">title</option>
          <option value="updated_at">updated_at</option>
          <option value="inserted_at">inserted_at</option>
        </select>
      </div>
      <div class="col">
        <select class="form-select form-select-sm" id="osdrSortDir">
          <option value="asc">По возрастанию</option>
          <option value="desc">По убыванию</option>
        </select>
      </div>
      <div class="col">
        <button class="btn btn-sm btn-primary" type="submit">Применить</button>
      </div>
    </form>
  </div>

  <div class="table-responsive">
    <table id="osdrTable" class="table table-sm table-striped align-middle">
      <thead>
        <tr>
          <th>#</th>
          <th>dataset_id</th>
          <th>title</th>
          <th>REST_URL</th>
          <th>updated_at</th>
          <th>inserted_at</th>
          <th>raw</th>
        </tr>
      </thead>
      <tbody>
      @forelse($items as $row)
        <tr>
          <td>{{ $row['id'] }}</td>
          <td>{{ $row['dataset_id'] ?? '—' }}</td>
          <td style="max-width:420px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap">
            {{ $row['title'] ?? '—' }}
          </td>
          <td>
            @if(!empty($row['rest_url']))
              <a href="{{ $row['rest_url'] }}" target="_blank" rel="noopener">открыть</a>
            @else — @endif
          </td>
          {{-- updated_at с fallback на inserted_at --}}
          <td>{{ $row['updated_at'] ?? $row['inserted_at'] ?? '—' }}</td>
          <td>{{ $row['inserted_at'] ?? '—' }}</td>
          <td>
            <details>
              <summary class="btn btn-outline-secondary btn-sm d-inline-block">JSON</summary>
              <pre class="mb-0 mt-2" style="max-height:260px;overflow:auto">
{{ json_encode($row['raw'] ?? [], JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE) }}
              </pre>
            </details>
          </td>
        </tr>
      @empty
        <tr><td colspan="7" class="text-center text-muted">нет данных</td></tr>
      @endforelse
      </tbody>
    </table>
  </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
  const table   = document.getElementById('osdrTable');
  const tbody   = table.querySelector('tbody');
  const form    = document.getElementById('osdrFilterForm');
  const search  = document.getElementById('osdrSearch');
  const sortCol = document.getElementById('osdrSortColumn');
  const sortDir = document.getElementById('osdrSortDir');

  const EMPTY_ROW_HTML = '<tr><td colspan="7" class="text-center text-muted">нет данных</td></tr>';

  // Сохраняем исходные строки (как снимок, без "перетасовки")
  const originalRows = Array.from(tbody.querySelectorAll('tr')).map(tr => tr.cloneNode(true));

  // Индексы колонок в <tr>
  const COL_INDEX = {
    dataset_id: 1,
    title: 2,
    updated_at: 4,
    inserted_at: 5,
  };

  function getCellText(tr, index) {
    const cell = tr.children[index];
    return cell ? cell.textContent.trim() : '';
  }

  function parseValue(val, key) {
    // Датовые поля сортируем как Date
    if (key === 'updated_at' || key === 'inserted_at') {
      if (!val || val === '—') return null;
      const d = new Date(val);
      return isNaN(d.getTime()) ? null : d;
    }
    // Остальное — строка
    return (val || '').toLowerCase();
  }

  function applyFilterAndSort() {
    const term = (search.value || '').toLowerCase().trim();
    const key  = sortCol.value;
    const dir  = sortDir.value === 'desc' ? 'desc' : 'asc';
    const mul  = dir === 'desc' ? -1 : 1;

    // Берём только "настоящие" строки с данными
    let rows = originalRows.filter(tr => {
      const tds = tr.querySelectorAll('td');
      if (!tds.length) return false;
      const first = tds[0].textContent.trim();
      return first !== 'нет данных';
    });

    // Фильтрация по ключевым словам (dataset_id + title + REST_URL)
    if (term) {
      rows = rows.filter(tr => {
        const dataset = getCellText(tr, COL_INDEX.dataset_id).toLowerCase();
        const title   = getCellText(tr, COL_INDEX.title).toLowerCase();
        const url     = getCellText(tr, 3).toLowerCase(); // REST_URL — 3-я колонка
        const haystack = dataset + ' ' + title + ' ' + url;
        return haystack.includes(term);
      });
    }

    // Сортировка (если выбрано поле)
    if (key !== 'none' && COL_INDEX[key] != null) {
      const colIndex = COL_INDEX[key];

      rows.sort((a, b) => {
        const va = parseValue(getCellText(a, colIndex), key);
        const vb = parseValue(getCellText(b, colIndex), key);

        if (va === null && vb === null) return 0;
        if (va === null) return 1;
        if (vb === null) return -1;

        if (va < vb) return -1 * mul;
        if (va > vb) return  1 * mul;
        return 0;
      });
    }

    // Перерисовываем tbody
    tbody.innerHTML = '';
    if (!rows.length) {
      tbody.insertAdjacentHTML('beforeend', EMPTY_ROW_HTML);
    } else {
      rows.forEach(tr => tbody.appendChild(tr));
    }
  }

  // Обработка формы (кнопка "Применить")
  form.addEventListener('submit', (e) => {
    e.preventDefault();
    applyFilterAndSort();
  });

  // Живой поиск по мере ввода
  search.addEventListener('input', applyFilterAndSort);

  // Автопересортировка при смене селектов
  sortCol.addEventListener('change', applyFilterAndSort);
  sortDir.addEventListener('change', applyFilterAndSort);

  // Стартовый рендер (без фильтра и сортировки)
  applyFilterAndSort();
});
</script>
@endsection
