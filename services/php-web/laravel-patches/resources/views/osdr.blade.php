@extends('layouts.app')

@section('content')
<div class="container py-3">
  <h3 class="mb-3">NASA OSDR</h3>

  <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-2 mb-2">
    <div class="small text-muted">Источник {{ $src }}</div>

    <form id="osdrSortForm" class="row row-cols-auto g-2 align-items-center">
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
  const sortForm = document.getElementById('osdrSortForm');
  const sortCol  = document.getElementById('osdrSortColumn');
  const sortDir  = document.getElementById('osdrSortDir');

  const originalRows = Array.from(tbody.querySelectorAll('tr'));

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
    if (key === 'updated_at' || key === 'inserted_at') {
      if (!val || val === '—') return null;
      const d = new Date(val);
      return isNaN(d.getTime()) ? null : d;
    }
    return (val || '').toLowerCase();
  }

  function applySort() {
    const key = sortCol.value;
    const dir = sortDir.value === 'desc' ? 'desc' : 'asc';
    const mul = dir === 'desc' ? -1 : 1;

    if (key === 'none' || !COL_INDEX[key]) {
      tbody.innerHTML = '';
      originalRows.forEach(tr => tbody.appendChild(tr));
      return;
    }

    const colIndex = COL_INDEX[key];

    const rows = originalRows.slice().filter(tr => {
      const tds = tr.querySelectorAll('td');
      if (!tds.length) return false;
      const firstCell = tds[0].textContent.trim();
      if (firstCell === 'нет данных') return false;
      return true;
    });

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

    tbody.innerHTML = '';
    rows.forEach(tr => tbody.appendChild(tr));
  }

  sortForm.addEventListener('submit', (e) => {
    e.preventDefault();
    applySort();
  });

  sortCol.addEventListener('change', applySort);
  sortDir.addEventListener('change', applySort);
});
</script>
@endsection
