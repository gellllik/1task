(function() {
  'use strict';

  const tasks = Array.isArray(window.__TASKS) ? window.__TASKS.slice() : [];
  const start = window.__START_DATE || { year: new Date().getFullYear(), month: new Date().getMonth() };
  const selectedDateInit = window.__SELECTED_DATE || new Date().toISOString().slice(0, 10);


  const monthNames = ['Январь','Февраль','Март','Апрель','Май','Июнь',
                      'Июль','Август','Сентябрь','Октябрь','Ноябрь','Декабрь'];

  let currentYear = start.year;
  let currentMonth = start.month;
  let selectedDate = selectedDateInit;
  let activeFilter = 'all';
  let searchQuery = '';

  const daysGrid = document.getElementById('daysGrid');
  const monthLabel = document.getElementById('monthLabel');
  const tasksList = document.getElementById('tasksList');
  const selectedDateLabel = document.getElementById('selectedDateLabel');
  const taskListEl = document.getElementById('taskList');
  const searchInput = document.getElementById('searchInput');
  const countAll = document.getElementById('countAll');
  const countActual = document.getElementById('countActual');
  const countDone = document.getElementById('countDone');


  const pad = n => String(n).padStart(2, '0');
  const toKey = (y, m, d) => `${y}-${pad(m + 1)}-${pad(d)}`;
  const formatRu = key => {
    const [y, m, d] = key.split('-');
    return `${d}.${m}.${y}`;
  };
  function escapeHtml(str) {
    const div = document.createElement('div');
    div.textContent = String(str == null ? '' : str);
    return div.innerHTML;
  }
  function getPriorityLabel(p) {
    switch (p) {
      case 'low':    return 'Низкий';
      case 'medium': return 'Средний';
      case 'high':   return 'Высокий';
      default:       return 'Средний';
    }
  }


  function renderCalendar() {
    monthLabel.textContent = `${monthNames[currentMonth]} ${currentYear}`;
    daysGrid.innerHTML = '';

    const firstDay = new Date(currentYear, currentMonth, 1);
    const offset = firstDay.getDay() === 0 ? 6 : firstDay.getDay() - 1;
    const daysIn = new Date(currentYear, currentMonth + 1, 0).getDate();

    const today = new Date();
    const todayKey = toKey(today.getFullYear(), today.getMonth(), today.getDate());

    for (let i = 0; i < offset; i++) {
      const e = document.createElement('div');
      e.className = 'day empty';
      daysGrid.appendChild(e);
    }

    for (let d = 1; d <= daysIn; d++) {
      const key = toKey(currentYear, currentMonth, d);
      const cell = document.createElement('div');
      cell.className = 'day';
      cell.textContent = d;
      if (key === todayKey) cell.classList.add('today');
      if (tasks.some(t => t.date === key)) cell.classList.add('has-task');
      if (key === selectedDate) cell.classList.add('selected');

      cell.addEventListener('click', () => {
        selectedDate = key;
        const url = new URL(window.location);
        url.searchParams.set('date', key);
        url.searchParams.set('year', currentYear);
        url.searchParams.set('month', currentMonth);
        history.replaceState({}, '', url);
        
        renderCalendar();
        renderDayTasks(key);
      });
      daysGrid.appendChild(cell);
    }
  }


  function renderDayTasks(key) {
    if (!key) return;
    selectedDateLabel.textContent = `Задачи на ${formatRu(key)}`;
    tasksList.innerHTML = '';
    const dayTasks = tasks.filter(t => t.date === key);
    if (dayTasks.length === 0) {
      const empty = document.createElement('div');
      empty.className = 'empty';
      empty.textContent = 'Задач пока нет';
      tasksList.appendChild(empty);
      return;
    }
    dayTasks.forEach(t => {
      const el = document.createElement('div');
      el.className = 'item';
      el.textContent = t.title;
      tasksList.appendChild(el);
    });
  }


  function updateCounts() {
    countAll.textContent = tasks.length;
    countActual.textContent = tasks.filter(t => t.status === 'actual').length;
    countDone.textContent = tasks.filter(t => t.status === 'done').length;
  }
  function renderTaskList() {
    let list = tasks.slice();
    if (activeFilter !== 'all') list = list.filter(t => t.status === activeFilter);
    if (searchQuery.trim() !== '') {
      const q = searchQuery.trim().toLowerCase();
      list = list.filter(t => String(t.title || '').toLowerCase().includes(q));
    }
    list.sort((a, b) => String(a.date).localeCompare(String(b.date)));
    taskListEl.innerHTML = '';
    if (list.length === 0) {
      const empty = document.createElement('div');
      empty.className = 'empty-list';
      empty.innerHTML = '<div class="icon"></div><div>Задач пока нет</div>';
      taskListEl.appendChild(empty);
      return;
    }
    list.forEach(t => {
      const card = document.createElement('div');
      card.className = `task-card ${t.status}`;
      card.dataset.id = t.id;
      const isDone = t.status === 'done';
      const statusLabel = isDone ? 'Выполнена' : 'Актуальная';
     card.innerHTML = `
        <button type="button" class="task-check" data-action="toggle"
                aria-label="${isDone ? 'Отметить как актуальную' : 'Отметить как выполненную'}"
                title="${isDone ? 'Снять отметку' : 'Отметить выполненной'}">
          ${isDone ? '✓' : ''}
        </button>
        <div class="task-body">
          <div class="task-title">${escapeHtml(t.title)}</div>
          <div class="task-meta">
            <span>${formatRu(t.date)}</span>
            <span class="badge">${statusLabel}</span>
            <span class="badge priority-${escapeHtml(t.priority || 'medium')}">${getPriorityLabel(t.priority)}</span>
          </div>
          ${t.description ? `<div class="task-desc">${escapeHtml(t.description)}</div>` : ''}
        </div>
        <div class="task-actions">
          <button type="button" class="task-action-btn edit" data-action="edit">✎ Редактировать</button>
          <button type="button" class="task-action-btn delete" data-action="delete">✕ Удалить</button>
        </div>
      `;
      taskListEl.appendChild(card);
    });
  }


  function postAction(data) {
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = 'index.php';
    form.style.display = 'none';
    Object.entries(data).forEach(([k, v]) => {
      const inp = document.createElement('input');
      inp.type = 'hidden';
      inp.name = k;
      inp.value = v;
      form.appendChild(inp);
    });
    document.body.appendChild(form);
    form.submit();
  }

 
  taskListEl.addEventListener('click', e => {
    const btn = e.target.closest('[data-action]');
    if (!btn) return;
    const card = btn.closest('.task-card');
    if (!card) return;
    const id = Number(card.dataset.id);
    const action = btn.dataset.action;
    const task = tasks.find(t => t.id === id);
    if (!task) return;
    if (action === 'toggle') {
      postAction({
        action: 'toggle',
        id: id,
        status: task.status === 'done' ? 'actual' : 'done'
      });
      return;
    }
    if (action === 'delete') {
      if (!confirm('Удалить эту задачу?')) return;
      postAction({ action: 'delete', id: id });
      return;
    }
    if (action === 'edit') {
      openEditModal(task);
      return;
    }
  });

 
  document.querySelectorAll('.stat-card[data-filter]').forEach(btn => {
    btn.addEventListener('click', () => {
      document.querySelectorAll('.stat-card[data-filter]').forEach(b => b.classList.remove('active'));
      btn.classList.add('active');
      activeFilter = btn.dataset.filter;
      renderTaskList();
    });
  });

  
  if (searchInput) {
    searchInput.addEventListener('input', e => {
      searchQuery = e.target.value;
      renderTaskList();
    });
  }

  
  const prevBtn = document.getElementById('prevMonth');
  const nextBtn = document.getElementById('nextMonth');
  if (prevBtn) prevBtn.addEventListener('click', () => {
    currentMonth--;
    if (currentMonth < 0) { currentMonth = 11; currentYear--; }
    renderCalendar();
  });
  if (nextBtn) nextBtn.addEventListener('click', () => {
    currentMonth++;
    if (currentMonth > 11) { currentMonth = 0; currentYear++; }
    renderCalendar();
  });

  
  const taskPanel = document.getElementById('taskPanel');
  const newTaskBtn = document.getElementById('newTaskBtn');
  const newTaskForm = document.getElementById('newTaskForm');
  const taskDateInput = document.getElementById('taskDate');
  function openPanel() {
    if (!taskPanel) return;
    taskPanel.classList.add('open');
    document.body.style.overflow = 'hidden';
    if (newTaskForm) newTaskForm.reset();
    initPanelCalendar();
    setTimeout(() => {
      const t = document.getElementById('taskTitle');
      if (t) t.focus();
    }, 250);
  }
  function closePanel() {
    if (!taskPanel) return;
    taskPanel.classList.remove('open');
    document.body.style.overflow = '';
  }
  if (newTaskBtn) newTaskBtn.addEventListener('click', openPanel);
  if (taskPanel) {
    taskPanel.querySelectorAll('[data-close-panel]').forEach(b => b.addEventListener('click', closePanel));
    taskPanel.addEventListener('click', e => {
      if (e.target === taskPanel) closePanel();
    });
  }

  
  const pLabelEl = document.getElementById('pLabel');
  const pDaysEl = document.getElementById('pDays');
  let pYear, pMonth, pSelectedDate;
  function initPanelCalendar() {
    const today = new Date();
    pYear = today.getFullYear();
    pMonth = today.getMonth();
    pSelectedDate = toKey(pYear, pMonth, today.getDate());
    if (taskDateInput) taskDateInput.value = pSelectedDate;
    renderPanelCalendar();
  }
  function renderPanelCalendar() {
    if (!pLabelEl || !pDaysEl) return;
    pLabelEl.textContent = `${monthNames[pMonth]} ${pYear}`;
    pDaysEl.innerHTML = '';
    const firstDay = new Date(pYear, pMonth, 1);
    const offset = firstDay.getDay() === 0 ? 6 : firstDay.getDay() - 1;
    const daysIn = new Date(pYear, pMonth + 1, 0).getDate();
    const today = new Date();
    const todayKey = toKey(today.getFullYear(), today.getMonth(), today.getDate());
    for (let i = 0; i < offset; i++) {
      const e = document.createElement('span');
      e.className = 'p-day empty';
      pDaysEl.appendChild(e);
    }
    for (let d = 1; d <= daysIn; d++) {
      const key = toKey(pYear, pMonth, d);
      const el = document.createElement('span');
      el.className = 'p-day';
      el.textContent = d;
      if (key === todayKey) el.classList.add('today');
      if (key === pSelectedDate) el.classList.add('selected');
      el.addEventListener('click', () => {
        pSelectedDate = key;
        if (taskDateInput) taskDateInput.value = key;
        renderPanelCalendar();
      });
      pDaysEl.appendChild(el);
    }
  }
  const pPrevBtn = document.getElementById('pPrev');
  const pNextBtn = document.getElementById('pNext');
  if (pPrevBtn) pPrevBtn.addEventListener('click', () => {
    pMonth--;
    if (pMonth < 0) { pMonth = 11; pYear--; }
    renderPanelCalendar();
  });
  if (pNextBtn) pNextBtn.addEventListener('click', () => {
    pMonth++;
    if (pMonth > 11) { pMonth = 0; pYear++; }
    renderPanelCalendar();
  });
  if (taskDateInput) {
    taskDateInput.addEventListener('change', () => {
      const v = taskDateInput.value;
      if (!/^\d{4}-\d{2}-\d{2}$/.test(v)) return;
      pSelectedDate = v;
      const [y, m] = v.split('-');
      pYear = parseInt(y, 10);
      pMonth = parseInt(m, 10) - 1;
      renderPanelCalendar();
    });
  }

 
  const editModal = document.getElementById('editModal');
  function openEditModal(task) {
    if (!editModal) return;
    document.getElementById('editTaskId').value = task.id;
    document.getElementById('editTitle').value = task.title || '';
    document.getElementById('editComment').value = task.description || '';
    document.getElementById('editPriority').value = task.priority || 'medium';
    document.getElementById('editDate').value = task.date || '';
    editModal.classList.add('open');
    document.body.style.overflow = 'hidden';
  }
  function closeEditModal() {
    if (!editModal) return;
    editModal.classList.remove('open');
    document.body.style.overflow = '';
  }
  if (editModal) {
    editModal.querySelectorAll('[data-close-edit]').forEach(b => b.addEventListener('click', closeEditModal));
    editModal.addEventListener('click', e => {
      if (e.target === editModal) closeEditModal();
    });
  }


  document.addEventListener('keydown', e => {
    if (e.key === 'Escape') {
      if (taskPanel && taskPanel.classList.contains('open')) closePanel();
      if (editModal && editModal.classList.contains('open')) closeEditModal();
    }
  });


  renderCalendar();
  renderDayTasks(selectedDate);
  updateCounts();
  renderTaskList();
})();