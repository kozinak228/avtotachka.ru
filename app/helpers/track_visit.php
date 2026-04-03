<?php
/**
 * Трекер посещений — подключается на каждой публичной странице.
 * Не трекает: ботов, админов, AJAX-запросы, статику.
 */

// Нужен $pdo (уже доступен через db.php)
if (!isset($pdo)) return;

// Не трекаем AJAX
if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') return;

// Не трекаем админов
if (!empty($_SESSION['admin'])) return;

// Простой фильтр ботов
$ua = $_SERVER['HTTP_USER_AGENT'] ?? '';
if (empty($ua) || preg_match('/bot|crawl|spider|slurp|mediapartners/i', $ua)) return;

// Определяем страницу
$page = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
$page = trim($page, '/') ?: 'index';

// Определяем car_id если это страница авто
$car_id = null;
if (strpos($page, 'single') !== false && isset($_GET['id'])) {
    $car_id = intval($_GET['id']);
}

// Получаем IP
$ip = $_SERVER['HTTP_X_FORWARDED_FOR'] ?? $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
if (strpos($ip, ',') !== false) {
    $ip = trim(explode(',', $ip)[0]);
}

// Защита от спама: не более 1 записи с одного IP на одну страницу за 30 секунд
try {
    $check = $pdo->prepare("SELECT COUNT(*) FROM page_views WHERE ip = :ip AND page = :page AND created_at > DATE_SUB(NOW(), INTERVAL 30 SECOND)");
    $check->execute(['ip' => $ip, 'page' => $page]);
    if ((int)$check->fetchColumn() > 0) return;

    $stmt = $pdo->prepare("INSERT INTO page_views (ip, page, car_id, user_agent) VALUES (:ip, :page, :car_id, :ua)");
    $stmt->execute([
        'ip' => $ip,
        'page' => $page,
        'car_id' => $car_id,
        'ua' => mb_substr($ua, 0, 500),
    ]);
} catch (Exception $e) {
    // Молча игнорируем ошибки трекинга — не ломаем сайт
}
