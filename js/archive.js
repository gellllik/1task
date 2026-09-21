(function () {
  'use strict';
  const search = document.getElementById('searchInput');
  const sort = document.getElementById('sortSelect');
  const clearBtn = document.getElementById('clearAllBtn');
  const list = document.getElementById('archiveList');
  if (!list) return;
  const cards = Array.from(list.querySelectorAll('.archive-card'));

  function filter() {
    const q = search.value.trim().toLowerCase();
    cards.forEach(c => {
      const t = (c.dataset.title || '').toLowerCase();
      c.style.display = (q === '' || t.includes(q)) ? '' : 'none';
    });
  }
  function applySort() {
    const m = sort.value;
    const s = [...cards].sort((a, b) => {
      if (m === 'date-desc') return (b.dataset.archivedAt || '').localeCompare(a.dataset.archivedAt || '');
      if (m === 'date-asc') return (a.dataset.archivedAt || '').localeCompare(b.dataset.archivedAt || '');
      if (m === 'title') return (a.dataset.title || '').localeCompare(b.dataset.title || '', 'ru');
      return 0;
    });
    s.forEach(c => list.appendChild(c));
  }

  search?.addEventListener('input', filter);
  sort?.addEventListener('change', applySort);

  clearBtn?.addEventListener('click', () => {
    if (!cards.length) { alert('Архив уже пуст.'); return; }
    if (!confirm('Полностью очистить архив?')) return;
    document.getElementById('clearAllForm').submit();
  });
})();