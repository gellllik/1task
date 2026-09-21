<?php
require __DIR__ . '/functions.php';
requireAuth();
$user = currentUser();
$uid = (int)$user['id'];

$action = $_POST['action'] ?? '';

if ($action === 'toggle') {
    updateTask($uid, (int)$_POST['id'], [
        'status' => ($_POST['status'] ?? 'actual') === 'done' ? 'done' : 'actual'
    ]);
    redirect('index.php');
}

if ($action === 'delete') {
    $t = deleteTask($uid, (int)$_POST['id']);
    if ($t) archiveTask($uid, $t);
    redirect('index.php');
}

if ($action === 'create') {
    $title = trim($_POST['title'] ?? '');
    $desc  = trim($_POST['comment'] ?? '');
    $prio  = in_array($_POST['priority'] ?? '', ['low','medium','high'], true) ? $_POST['priority'] : 'medium';
    $date  = $_POST['date'] ?? date('Y-m-d');
    if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) $date = date('Y-m-d');
    if ($title !== '') {
        addTask($uid, [
            'title' => mb_substr($title, 0, 200),
            'description' => mb_substr($desc, 0, 1000),
            'date' => $date,
            'status' => 'actual',
            'priority' => $prio
        ]);
    }
    redirect('index.php');
}

if ($action === 'update') {
    $id = (int)($_POST['id'] ?? 0);
    $title = trim($_POST['title'] ?? '');
    $desc  = trim($_POST['comment'] ?? '');
    $prio  = in_array($_POST['priority'] ?? '', ['low','medium','high'], true) ? $_POST['priority'] : 'medium';
    $date  = $_POST['date'] ?? '';
    if ($title !== '' && preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
        updateTask($uid, $id, [
            'title' => mb_substr($title, 0, 200),
            'description' => mb_substr($desc, 0, 1000),
            'priority' => $prio,
            'date' => $date
        ]);
    }
    redirect('index.php');
}

$tasks = getTasks($uid);

$curY = isset($_GET['year']) ? (int)$_GET['year'] : (int)date('Y');
$curM = isset($_GET['month']) ? (int)$_GET['month'] : (int)date('n') - 1;
while ($curM < 0) { $curM += 12; $curY--; }
while ($curM > 11) { $curM -= 12; $curY++; }

$selDate = $_GET['date'] ?? date('Y-m-d');
if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $selDate)) $selDate = date('Y-m-d');

$dayTasks = array_values(array_filter($tasks, fn($t) => ($t['date'] ?? '') === $selDate));
$cAll = count($tasks);
$cActual = count(array_filter($tasks, fn($t) => ($t['status'] ?? '') === 'actual'));
$cDone = count(array_filter($tasks, fn($t) => ($t['status'] ?? '') === 'done'));

$monthNames = ['Январь','Февраль','Март','Апрель','Май','Июнь',
               'Июль','Август','Сентябрь','Октябрь','Ноябрь','Декабрь'];
?>
<!DOCTYPE html>
<html lang="ru">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Задачи на день — Главная</title>
<link rel="stylesheet" href="css/index.css">
<link rel="stylesheet" href="css/base.css">
</head>
<body>
<?php include __DIR__ . '/header.php'; ?>
<div class="container">
  <div class="content">
    <aside class="catalog">
      <h3>Календарь</h3>
      <div class="cal-nav">
        <button id="prevMonth">‹</button>
        <span class="month" id="monthLabel"><?= e($monthNames[$curM]) ?> <?= $curY ?></span>
        <button id="nextMonth">›</button>
      </div>
      <div class="cal-grid">
        <div class="wd">Пн</div><div class="wd">Вт</div><div class="wd">Ср</div>
        <div class="wd">Чт</div><div class="wd">Пт</div><div class="wd">Сб</div><div class="wd">Вс</div>
      </div>
      <div class="cal-grid" id="daysGrid"></div>
      <div class="day-tasks">
        <div class="title" id="selectedDateLabel">Задачи на <?= e(formatRu($selDate)) ?></div>
        <div id="tasksList">
          <?php if (empty($dayTasks)): ?>
            <div class="empty">Задач пока нет</div>
          <?php else: foreach ($dayTasks as $t): ?>
            <div class="item"><?= e($t['title']) ?></div>
          <?php endforeach; endif; ?>
        </div>
      </div>

      <a href="archive.php" class="archive-btn">Архив</a>
    </aside>

    <main class="main-block">
      <div class="stats">
        <button class="stat-card all active" data-filter="all">
          <div class="label">Все задачи</div>
          <div class="count" id="countAll"><?= $cAll ?></div>
        </button>
        <button class="stat-card actual" data-filter="actual">
          <div class="label">Актуальные</div>
          <div class="count" id="countActual"><?= $cActual ?></div>
        </button>
        <button class="stat-card done" data-filter="done">
          <div class="label">Выполненные</div>
          <div class="count" id="countDone"><?= $cDone ?></div>
        </button>
        <button class="stat-card new-task" id="newTaskBtn">
          <div class="plus">+</div>
          <div class="label">Новая задача</div>
        </button>
      </div>
          <div class="search-box">
          <input type="text" id="searchInput" placeholder="Поиск задачи по названию...">
          </div>
      <div class="list-title">Список поставленных задач</div>
      <div class="task-list" id="taskList"></div>
    </main>
  </div>
</div>

<div class="panel-overlay" id="taskPanel">
  <div class="panel">
    <div class="panel-header">
      <h3>Новая задача</h3>
      <button type="button" class="panel-close" data-close-panel>✕</button>
    </div>
    <form id="newTaskForm" method="post" action="index.php">
      <input type="hidden" name="action" value="create">
      <div class="form-group">
        <label for="taskTitle">Название</label>
        <input type="text" id="taskTitle" name="title" required maxlength="200">
      </div>
      <div class="form-group">
        <label for="taskComment">Комментарий</label>
        <textarea id="taskComment" name="comment" maxlength="1000" rows="3"></textarea>
      </div>
      <div class="form-group">
        <label for="taskPriority">Приоритет</label>
        <select id="taskPriority" name="priority">
          <option value="low">Низкий приоритет</option>
          <option value="medium">Средняя приоритет</option>
          <option value="high">В первую очередь!</option>
        </select>
      </div>
      <div class="form-group">
  <label for="taskDate">Дата выполнения</label>
  <input type="date" id="taskDate" name="date" required>
</div>
<div class="form-group">
  <label>Или выберите в календаре</label>
  <div class="panel-calendar">
    <div class="panel-cal-header">
      <button type="button" id="pPrev">‹</button>
      <span id="pLabel"></span>
      <button type="button" id="pNext">›</button>
    </div>
    <div class="panel-cal-weekdays">
      <span>Пн</span><span>Вт</span><span>Ср</span>
      <span>Чт</span><span>Пт</span><span>Сб</span><span>Вс</span>
    </div>
    <div class="panel-cal-days" id="pDays"></div>
  </div>
</div>
      <button type="submit" class="panel-submit">Создать</button>
    </form>
  </div>
</div>

<div class="panel-overlay" id="editModal">
  <div class="panel" style="margin:auto;max-width:520px;height:auto;border-radius:10px;">
    <div class="panel-header">
      <h3>Редактирование</h3>
      <button type="button" class="panel-close" data-close-edit>✕</button>
    </div>
    <form id="editTaskForm" method="post" action="index.php">
      <input type="hidden" name="action" value="update">
      <input type="hidden" id="editTaskId" name="id">
      <div class="form-group">
        <label for="editTitle">Название</label>
        <input type="text" id="editTitle" name="title" required maxlength="200">
      </div>
      <div class="form-group">
        <label for="editComment">Комментарий</label>
        <textarea id="editComment" name="comment" maxlength="1000" rows="3"></textarea>
      </div>
      <div class="form-group">
        <label for="editPriority">Приоритет</label>
        <select id="editPriority" name="priority">
          <option value="low">Низкий приоритет</option>
          <option value="medium">Средняя приоритет</option>
          <option value="high">В первую очередь!</option>
        </select>
      </div>
      <div class="form-group">
        <label for="editDate">Дата</label>
        <input type="date" id="editDate" name="date" required>
      </div>
      <button type="submit" class="panel-submit">Сохранить</button>
    </form>
  </div>
</div>

<script>
window.__TASKS = <?= json_encode($tasks, JSON_UNESCAPED_UNICODE) ?>;
window.__START_DATE = { year: <?= (int)$curY ?>, month: <?= (int)$curM ?> };
window.__SELECTED_DATE = <?= json_encode($selDate) ?>;
</script>
<script src="js/index.js"></script>
</body>
</html>