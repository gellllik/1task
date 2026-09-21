<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/db.php';


function e($s) { return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }
function redirect($url) { header("Location: $url"); exit; }

function formatRu(?string $date): string {
    if (!$date) return '—';
    $ts = strtotime($date);
    return $ts ? date('d.m.Y', $ts) : '—';
}
function formatDateTimeRu(?string $dt): string {
    if (!$dt) return '—';
    $ts = strtotime($dt);
    return $ts ? date('d.m.Y в H:i', $ts) : '—';
}


const POSITION_LABELS = [
    'programmer' => 'Программист',
    'lawyer'     => 'Юрист',
    'economist'  => 'Экономист',
];
function positionLabel(?string $k): string {
    return POSITION_LABELS[$k] ?? ($k ?: '—');
}

function initials(string $name): string {
    $parts = preg_split('/\s+/', trim($name), -1, PREG_SPLIT_NO_EMPTY);
    if (!$parts) return '??';
    if (count($parts) === 1) return mb_strtoupper(mb_substr($parts[0], 0, 2));
    return mb_strtoupper(mb_substr($parts[0], 0, 1) . mb_substr($parts[1], 0, 1));
}


function currentUser(): ?array {
    if (empty($_SESSION['user_id'])) return null;
    $st = db()->prepare("SELECT * FROM users WHERE id = ? LIMIT 1");
    $st->execute([(int)$_SESSION['user_id']]);
    return $st->fetch() ?: null;
}
function isLoggedIn(): bool { return currentUser() !== null; }
function requireAuth(): void { if (!isLoggedIn()) redirect('login.php'); }


function getTasks(int $uid): array {
    $st = db()->prepare("SELECT * FROM tasks WHERE user_id = ? ORDER BY date ASC, id ASC");
    $st->execute([$uid]);
    return $st->fetchAll();
}
function addTask(int $uid, array $t): int {
    $st = db()->prepare("INSERT INTO tasks (user_id,title,description,date,status,priority)
                         VALUES (?,?,?,?,?,?)");
    $st->execute([$uid, $t['title'], $t['description'] ?? '',
                  $t['date'], $t['status'] ?? 'actual', $t['priority'] ?? 'medium']);
    return (int)db()->lastInsertId();
}
function updateTask(int $uid, int $id, array $f): bool {
    $allowed = ['title','description','date','status','priority'];
    $set = []; $vals = [];
    foreach ($f as $k => $v) { if (in_array($k, $allowed, true)) { $set[] = "$k = ?"; $vals[] = $v; } }
    if (!$set) return false;
    $vals[] = $id; $vals[] = $uid;
    $st = db()->prepare("UPDATE tasks SET " . implode(',', $set) . " WHERE id=? AND user_id=?");
    return $st->execute($vals);
}
function deleteTask(int $uid, int $id): ?array {
    $st = db()->prepare("SELECT * FROM tasks WHERE id=? AND user_id=? LIMIT 1");
    $st->execute([$id, $uid]);
    $t = $st->fetch();
    if (!$t) return null;
    db()->prepare("DELETE FROM tasks WHERE id=? AND user_id=?")->execute([$id, $uid]);
    return $t;
}


function getArchive(int $uid): array {
    $st = db()->prepare("SELECT * FROM archive WHERE user_id = ? ORDER BY archived_at DESC");
    $st->execute([$uid]);
    return $st->fetchAll();
}
function archiveTask(int $uid, array $t): void {
    $st = db()->prepare("INSERT INTO archive (user_id,title,description,date,status,priority,archived_at)
                         VALUES (?,?,?,?,?,?,NOW())");
    $st->execute([$uid, $t['title'], $t['description'] ?? '', $t['date'],
                  $t['status'] ?? 'actual', $t['priority'] ?? 'medium']);
}
function removeFromArchive(int $uid, int $id): ?array {
    $st = db()->prepare("SELECT * FROM archive WHERE id=? AND user_id=? LIMIT 1");
    $st->execute([$id, $uid]);
    $t = $st->fetch();
    if (!$t) return null;
    db()->prepare("DELETE FROM archive WHERE id=? AND user_id=?")->execute([$id, $uid]);
    return $t;
}
function clearArchive(int $uid): void {
    db()->prepare("DELETE FROM archive WHERE user_id = ?")->execute([$uid]);
}


function findUserByLogin(string $login): ?array {
    $st = db()->prepare("SELECT * FROM users WHERE LOWER(login)=LOWER(?) LIMIT 1");
    $st->execute([$login]);
    return $st->fetch() ?: null;
}
function addUser(array $d): int {
    $st = db()->prepare("INSERT INTO users
        (fullname,phone,position,login,password_hash,email,birthdate,telegram,city,bio,photo,registered_at)
        VALUES (?,?,?,?,?,?,?,?,?,?,?,NOW())");
    $st->execute([
        $d['fullname'] ?? '', $d['phone'] ?? '', $d['position'] ?? 'programmer',
        $d['login'] ?? '', $d['password_hash'] ?? '', $d['email'] ?? '',
        $d['birthdate'] ?? null, $d['telegram'] ?? '', $d['city'] ?? '',
        $d['bio'] ?? '', $d['photo'] ?? ''
    ]);
    return (int)db()->lastInsertId();
}
function updateUser(int $id, array $f): bool {
    $allowed = ['fullname','phone','position','login','password_hash','email',
                'birthdate','telegram','city','bio','photo'];
    $set = []; $vals = [];
    foreach ($f as $k => $v) { if (in_array($k, $allowed, true)) { $set[] = "$k = ?"; $vals[] = $v; } }
    if (!$set) return false;
    $vals[] = $id;
    return db()->prepare("UPDATE users SET " . implode(',', $set) . " WHERE id=?")->execute($vals);
}
function deleteUser(int $id): void {
    db()->prepare("DELETE FROM users WHERE id = ?")->execute([$id]);
}