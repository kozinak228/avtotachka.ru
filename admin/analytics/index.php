<?php
session_start();
include "../../path.php";
include SITE_ROOT . "/app/database/db.php";

if (!isset($_SESSION['admin']) || !$_SESSION['admin']) {
    header('location: ' . BASE_URL); exit;
}

global $pdo;

// ============ ДАННЫЕ ============

// Период: today, week, month
$period = $_GET['period'] ?? 'today';
$periodLabel = ['today' => 'Сегодня', 'week' => 'За 7 дней', 'month' => 'За 30 дней'][$period] ?? 'Сегодня';
$dateCondition = [
    'today' => "DATE(created_at) = CURDATE()",
    'week'  => "created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)",
    'month' => "created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)",
][$period] ?? "DATE(created_at) = CURDATE()";

// Уникальные посетители (по IP)
$q = $pdo->query("SELECT COUNT(DISTINCT ip) FROM page_views WHERE $dateCondition");
$uniqueVisitors = (int)$q->fetchColumn();

// Всего просмотров
$q = $pdo->query("SELECT COUNT(*) FROM page_views WHERE $dateCondition");
$totalViews = (int)$q->fetchColumn();

// Топ-10 просматриваемых авто
$topCars = $pdo->query("
    SELECT pv.car_id, c.title, c.img, COUNT(*) as views, COUNT(DISTINCT pv.ip) as unique_views
    FROM page_views pv
    JOIN cars c ON pv.car_id = c.id
    WHERE pv.car_id IS NOT NULL AND $dateCondition
    GROUP BY pv.car_id
    ORDER BY views DESC
    LIMIT 10
")->fetchAll(PDO::FETCH_ASSOC);

// Топ-10 страниц
$topPages = $pdo->query("
    SELECT page, COUNT(*) as views, COUNT(DISTINCT ip) as unique_views
    FROM page_views
    WHERE $dateCondition
    GROUP BY page
    ORDER BY views DESC
    LIMIT 10
")->fetchAll(PDO::FETCH_ASSOC);

// График по дням (последние 7 дней)
$dailyStats = $pdo->query("
    SELECT DATE(created_at) as day, COUNT(*) as views, COUNT(DISTINCT ip) as visitors
    FROM page_views
    WHERE created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)
    GROUP BY DATE(created_at)
    ORDER BY day ASC
")->fetchAll(PDO::FETCH_ASSOC);

$maxViews = max(array_column($dailyStats, 'views') ?: [1]);

// Общая статистика
$allTimeViews = (int)$pdo->query("SELECT COUNT(*) FROM page_views")->fetchColumn();
$allTimeVisitors = (int)$pdo->query("SELECT COUNT(DISTINCT ip) FROM page_views")->fetchColumn();

// Журнал посетителей — последние 30 уникальных IP за выбранный период
$recentVisitors = $pdo->query("
    SELECT ip, 
           COUNT(*) as total_views,
           MIN(created_at) as first_visit,
           MAX(created_at) as last_visit,
           GROUP_CONCAT(DISTINCT page ORDER BY created_at DESC SEPARATOR '||') as pages,
           (SELECT user_agent FROM page_views p2 WHERE p2.ip = page_views.ip ORDER BY p2.created_at DESC LIMIT 1) as user_agent
    FROM page_views
    WHERE $dateCondition
    GROUP BY ip
    ORDER BY last_visit DESC
    LIMIT 30
")->fetchAll(PDO::FETCH_ASSOC);

// Для каждого посетителя — подробная история
$visitorDetails = [];
foreach ($recentVisitors as $v) {
    $detailStmt = $pdo->prepare("SELECT page, car_id, created_at FROM page_views WHERE ip = :ip AND $dateCondition ORDER BY created_at DESC LIMIT 50");
    $detailStmt->execute(['ip' => $v['ip']]);
    $visitorDetails[$v['ip']] = $detailStmt->fetchAll(PDO::FETCH_ASSOC);
}

// Определение устройства по User-Agent
function detectDevice($ua) {
    if (preg_match('/Mobile|Android|iPhone|iPad/i', $ua)) return ['📱', 'Мобильный'];
    if (preg_match('/Tablet/i', $ua)) return ['📟', 'Планшет'];
    return ['💻', 'Компьютер'];
}
function detectBrowser($ua) {
    if (preg_match('/Chrome/i', $ua) && !preg_match('/Edge/i', $ua)) return 'Chrome';
    if (preg_match('/Firefox/i', $ua)) return 'Firefox';
    if (preg_match('/Safari/i', $ua) && !preg_match('/Chrome/i', $ua)) return 'Safari';
    if (preg_match('/Edge/i', $ua)) return 'Edge';
    if (preg_match('/Opera|OPR/i', $ua)) return 'Opera';
    if (preg_match('/YaBrowser/i', $ua)) return 'Яндекс';
    return 'Другой';
}

// Русские названия страниц
function pageNameRu($page) {
    $map = [
        'index' => 'Главная',
        'single.php' => 'Страница авто',
        'gallery.php' => 'Галерея',
        'compare.php' => 'Сравнение',
        'category.php' => 'Категория',
        'about.php' => 'О нас',
        'search.php' => 'Поиск',
    ];
    foreach ($map as $key => $name) {
        if (strpos($page, $key) !== false) return $name;
    }
    return $page;
}
?>
<!doctype html>
<html lang="ru">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Статистика — Админ-панель AvtoTachka</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0-beta2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= BASE_URL ?>assets/css/style.css">
    <style>
        * { box-sizing: border-box; }
        body { background: #0f172a !important; color: #e2e8f0 !important; font-family: 'Outfit', sans-serif; }

        /* ====== STAT CARDS ====== */
        .stat-card {
            background: linear-gradient(145deg, #1e293b 0%, #1a2332 100%);
            border: 1px solid rgba(255,255,255,0.06);
            border-radius: 1.25rem;
            padding: 1.5rem 1.75rem;
            transition: transform 0.3s, box-shadow 0.3s;
            position: relative;
            overflow: hidden;
        }
        .stat-card::before {
            content: '';
            position: absolute;
            top: 0; left: 0; right: 0;
            height: 3px;
            border-radius: 1.25rem 1.25rem 0 0;
        }
        .stat-card:nth-child(1) .stat-card::before, .col-6:nth-child(1) .stat-card::before { background: linear-gradient(90deg, #06b6d4, #22d3ee); }
        .col-6:nth-child(2) .stat-card::before { background: linear-gradient(90deg, #f59e0b, #fbbf24); }
        .col-6:nth-child(3) .stat-card::before { background: linear-gradient(90deg, #22c55e, #4ade80); }
        .col-6:nth-child(4) .stat-card::before { background: linear-gradient(90deg, #e11d48, #f43f5e); }
        .stat-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 12px 40px rgba(0,0,0,0.4);
        }
        .stat-number {
            font-size: 2.75rem;
            font-weight: 800;
            line-height: 1;
            letter-spacing: -0.02em;
        }
        .stat-label { font-size: 0.82rem; color: #94a3b8; margin-top: 0.35rem; }
        .stat-icon { font-size: 2.5rem; opacity: 0.15; }

        /* ====== PANELS ====== */
        .panel-card {
            background: #1e293b;
            border: 1px solid rgba(255,255,255,0.06);
            border-radius: 1.25rem;
            overflow: hidden;
            box-shadow: 0 4px 20px rgba(0,0,0,0.15);
        }
        .panel-header {
            padding: 1.25rem 1.5rem;
            border-bottom: 1px solid rgba(255,255,255,0.06);
            font-weight: 600;
            font-size: 1rem;
            display: flex; align-items: center; gap: 0.5rem;
            background: linear-gradient(180deg, rgba(255,255,255,0.02) 0%, transparent 100%);
        }

        /* ====== BAR CHART ====== */
        .bar-chart-row {
            display: flex; align-items: center; gap: 0.75rem;
            padding: 0.85rem 1.5rem;
            border-bottom: 1px solid rgba(255,255,255,0.03);
            transition: background 0.2s;
        }
        .bar-chart-row:hover { background: rgba(255,255,255,0.025); }
        .bar-chart-row:last-child { border-bottom: none; }
        .bar {
            height: 32px;
            border-radius: 8px;
            background: linear-gradient(90deg, #e11d48, #fb7185);
            box-shadow: 0 2px 12px rgba(225,29,72,0.25);
            transition: width 0.8s cubic-bezier(0.16, 1, 0.3, 1);
            display: flex; align-items: center; justify-content: flex-end;
            padding-right: 10px; font-size: 0.8rem; font-weight: 700; color: white;
            min-width: 36px;
        }

        /* ====== PERIOD TABS ====== */
        .period-tab {
            padding: 0.55rem 1.35rem;
            border-radius: 0.6rem;
            color: #64748b;
            text-decoration: none;
            transition: all 0.25s;
            font-weight: 600;
            font-size: 0.9rem;
        }
        .period-tab:hover { color: #e2e8f0; background: rgba(255,255,255,0.06); }
        .period-tab.active {
            background: linear-gradient(135deg, #e11d48, #be123c);
            color: white;
            box-shadow: 0 4px 15px rgba(225,29,72,0.35);
        }

        /* ====== TOP CARS ====== */
        .top-car-row {
            display: flex; align-items: center; gap: 1rem;
            padding: 0.85rem 1.5rem;
            border-bottom: 1px solid rgba(255,255,255,0.03);
            transition: background 0.2s;
        }
        .top-car-row:hover { background: rgba(255,255,255,0.025); }
        .top-car-row:last-child { border-bottom: none; }
        .top-car-thumb {
            width: 72px; height: 48px; object-fit: cover;
            border-radius: 10px; flex-shrink: 0;
            border: 1px solid rgba(255,255,255,0.08);
        }

        /* ====== RANK BADGES ====== */
        .rank-badge {
            width: 30px; height: 30px; border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            font-weight: 800; font-size: 0.8rem; flex-shrink: 0;
        }
        .rank-1 { background: linear-gradient(135deg, #f59e0b, #d97706); color: #1e293b; box-shadow: 0 2px 10px rgba(245,158,11,0.3); }
        .rank-2 { background: linear-gradient(135deg, #9ca3af, #6b7280); color: #1e293b; }
        .rank-3 { background: linear-gradient(135deg, #d97706, #92400e); color: white; }
        .rank-other { background: #293548; color: #64748b; }

        /* ====== BACK LINK ====== */
        a.back-link { color: #64748b; text-decoration: none; transition: color 0.2s; }
        a.back-link:hover { color: white; }

        /* ====== VISITOR LOG ====== */
        .visitor-row {
            padding: 1rem 1.5rem;
            border-bottom: 1px solid rgba(255,255,255,0.03);
            cursor: pointer;
            transition: background 0.2s;
        }
        .visitor-row:hover { background: rgba(255,255,255,0.025); }
        .visitor-row:last-child { border-bottom: none; }
        .visitor-detail {
            display: none;
            background: rgba(15,23,42,0.7);
            border-top: 1px solid rgba(255,255,255,0.04);
            padding: 1rem 1.5rem 1rem 4rem;
        }
        .visitor-detail.open { display: block; animation: slideDown 0.3s ease; }
        @keyframes slideDown {
            from { opacity: 0; transform: translateY(-8px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .visit-timeline { list-style: none; padding: 0; margin: 0; }
        .visit-timeline li {
            padding: 0.4rem 0;
            border-left: 2px solid #1e293b;
            padding-left: 1.25rem;
            margin-left: 0.5rem;
            font-size: 0.82rem;
            color: #94a3b8;
            position: relative;
            transition: color 0.2s;
        }
        .visit-timeline li:hover { color: #e2e8f0; }
        .visit-timeline li::before {
            content: '';
            width: 8px; height: 8px;
            border-radius: 50%;
            background: #e11d48;
            position: absolute;
            left: -5px; top: 0.65rem;
            box-shadow: 0 0 6px rgba(225,29,72,0.4);
        }
        .visit-timeline li .time { color: #475569; font-size: 0.75rem; font-family: monospace; }
        .device-badge {
            display: inline-flex; align-items: center; gap: 0.25rem;
            padding: 0.2rem 0.6rem;
            border-radius: 6px;
            font-size: 0.72rem;
            background: rgba(59,130,246,0.1);
            color: #60a5fa;
            border: 1px solid rgba(59,130,246,0.15);
        }
        .ip-badge {
            font-family: 'JetBrains Mono', monospace;
            font-size: 0.85rem;
            color: #60a5fa;
            font-weight: 600;
        }
        .expand-arrow {
            transition: transform 0.3s;
            font-size: 20px;
            color: #475569;
        }
        .visitor-row.expanded .expand-arrow {
            transform: rotate(180deg);
            color: #e11d48;
        }
    </style>
</head>
<body>
    <div class="container-fluid py-4 px-4" style="max-width: 1400px; margin: 0 auto;">

        <!-- Header -->
        <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-3">
            <div class="d-flex align-items-center gap-3">
                <a href="<?= BASE_URL ?>admin/cars/index.php" class="back-link">
                    <span class="material-icons">arrow_back</span>
                </a>
                <div>
                    <h1 class="h3 mb-0 fw-bold">
                        <span class="material-icons align-middle text-danger me-1" style="font-size:1.75rem;">analytics</span>
                        Статистика сайта
                    </h1>
                    <small class="text-muted">Аналитика посещений AvtoTachka</small>
                </div>
            </div>
            <div class="d-flex gap-2">
                <a href="?period=today" class="period-tab <?= $period === 'today' ? 'active' : '' ?>">Сегодня</a>
                <a href="?period=week" class="period-tab <?= $period === 'week' ? 'active' : '' ?>">7 дней</a>
                <a href="?period=month" class="period-tab <?= $period === 'month' ? 'active' : '' ?>">30 дней</a>
            </div>
        </div>

        <!-- Stat Cards -->
        <div class="row g-4 mb-4">
            <div class="col-6 col-lg-3">
                <div class="stat-card">
                    <div class="d-flex justify-content-between">
                        <div>
                            <div class="stat-number text-info"><?= number_format($uniqueVisitors) ?></div>
                            <div class="stat-label">Уникальных посетителей</div>
                            <div class="stat-label" style="color:#64748b;"><?= $periodLabel ?></div>
                        </div>
                        <span class="material-icons stat-icon">people</span>
                    </div>
                </div>
            </div>
            <div class="col-6 col-lg-3">
                <div class="stat-card">
                    <div class="d-flex justify-content-between">
                        <div>
                            <div class="stat-number text-warning"><?= number_format($totalViews) ?></div>
                            <div class="stat-label">Просмотров страниц</div>
                            <div class="stat-label" style="color:#64748b;"><?= $periodLabel ?></div>
                        </div>
                        <span class="material-icons stat-icon">visibility</span>
                    </div>
                </div>
            </div>
            <div class="col-6 col-lg-3">
                <div class="stat-card">
                    <div class="d-flex justify-content-between">
                        <div>
                            <div class="stat-number text-success"><?= number_format($allTimeVisitors) ?></div>
                            <div class="stat-label">Всего посетителей</div>
                            <div class="stat-label" style="color:#64748b;">За всё время</div>
                        </div>
                        <span class="material-icons stat-icon">public</span>
                    </div>
                </div>
            </div>
            <div class="col-6 col-lg-3">
                <div class="stat-card">
                    <div class="d-flex justify-content-between">
                        <div>
                            <div class="stat-number text-danger"><?= number_format($allTimeViews) ?></div>
                            <div class="stat-label">Всего просмотров</div>
                            <div class="stat-label" style="color:#64748b;">За всё время</div>
                        </div>
                        <span class="material-icons stat-icon">trending_up</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-4">
            <!-- Chart: Daily Views (last 7 days) -->
            <div class="col-12 col-lg-8">
                <div class="panel-card">
                    <div class="panel-header">
                        <span class="material-icons text-info">bar_chart</span>
                        Посещения за последние 7 дней
                    </div>
                    <?php if (empty($dailyStats)): ?>
                        <div class="p-4 text-center text-muted">Нет данных</div>
                    <?php else: ?>
                        <?php foreach ($dailyStats as $day):
                            $pct = ($day['views'] / $maxViews) * 100;
                            $dateStr = date('d.m', strtotime($day['day']));
                            $dayName = ['Вс','Пн','Вт','Ср','Чт','Пт','Сб'][date('w', strtotime($day['day']))];
                        ?>
                        <div class="bar-chart-row">
                            <div style="width:70px; flex-shrink:0; font-size:0.85rem;">
                                <strong><?= $dayName ?></strong>
                                <div style="color:#64748b; font-size:0.75rem;"><?= $dateStr ?></div>
                            </div>
                            <div style="flex:1;">
                                <div class="bar" style="width: <?= max(5, $pct) ?>%;">
                                    <?= $day['views'] ?>
                                </div>
                            </div>
                            <div style="width:90px; text-align:right; font-size:0.8rem; color:#94a3b8;">
                                <span class="material-icons" style="font-size:14px; vertical-align:middle;">person</span>
                                <?= $day['visitors'] ?> уник.
                            </div>
                        </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Top Pages -->
            <div class="col-12 col-lg-4">
                <div class="panel-card">
                    <div class="panel-header">
                        <span class="material-icons text-warning">description</span>
                        Популярные страницы
                    </div>
                    <?php if (empty($topPages)): ?>
                        <div class="p-4 text-center text-muted">Нет данных</div>
                    <?php else: ?>
                        <?php foreach ($topPages as $i => $pg): ?>
                        <div class="bar-chart-row">
                            <span class="rank-badge <?= $i < 3 ? 'rank-' . ($i+1) : 'rank-other' ?>"><?= $i + 1 ?></span>
                            <div style="flex:1; min-width:0;">
                                <div class="text-truncate fw-medium" style="font-size:0.9rem;"><?= htmlspecialchars(pageNameRu($pg['page'])) ?></div>
                                <div style="color:#64748b; font-size:0.75rem;" class="text-truncate">/<?= htmlspecialchars($pg['page']) ?></div>
                            </div>
                            <div style="text-align:right; white-space:nowrap;">
                                <div class="fw-bold"><?= $pg['views'] ?></div>
                                <div style="color:#64748b; font-size:0.7rem;"><?= $pg['unique_views'] ?> уник.</div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Top Cars -->
            <div class="col-12">
                <div class="panel-card">
                    <div class="panel-header">
                        <span class="material-icons text-danger">directions_car</span>
                        Самые просматриваемые авто — <?= $periodLabel ?>
                    </div>
                    <?php if (empty($topCars)): ?>
                        <div class="p-4 text-center text-muted">Нет данных за этот период</div>
                    <?php else: ?>
                        <?php foreach ($topCars as $i => $car): ?>
                        <div class="top-car-row">
                            <span class="rank-badge <?= $i < 3 ? 'rank-' . ($i+1) : 'rank-other' ?>"><?= $i + 1 ?></span>
                            <?php if (!empty($car['img'])): ?>
                                <img src="<?= BASE_URL ?>assets/images/cars/<?= htmlspecialchars($car['img']) ?>"
                                     alt="" class="top-car-thumb">
                            <?php else: ?>
                                <div class="top-car-thumb d-flex align-items-center justify-content-center" style="background:#334155;">
                                    <span class="material-icons" style="color:#64748b;">directions_car</span>
                                </div>
                            <?php endif; ?>
                            <div style="flex:1; min-width:0;">
                                <a href="<?= BASE_URL ?>single.php?id=<?= $car['car_id'] ?>"
                                   class="text-truncate d-block fw-medium" style="color:#e2e8f0; text-decoration:none; font-size:0.95rem;">
                                    <?= htmlspecialchars($car['title'] ?? 'Авто #' . $car['car_id']) ?>
                                </a>
                            </div>
                            <div style="text-align:right; white-space:nowrap;">
                                <div class="fw-bold" style="font-size:1.1rem;"><?= $car['views'] ?> <span style="font-size:0.75rem; color:#94a3b8;">просмотров</span></div>
                                <div style="color:#64748b; font-size:0.75rem;">
                                    <span class="material-icons" style="font-size:12px; vertical-align:middle;">person</span>
                                    <?= $car['unique_views'] ?> уникальных
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Visitor Log -->
            <div class="col-12 mt-4">
                <div class="panel-card">
                    <div class="panel-header">
                        <span class="material-icons text-info">group</span>
                        Журнал посетителей — <?= $periodLabel ?>
                        <span style="margin-left:auto; font-size:0.8rem; color:#64748b;"><?= count($recentVisitors) ?> посетителей</span>
                    </div>
                    <?php if (empty($recentVisitors)): ?>
                        <div class="p-4 text-center text-muted">Нет посетителей за этот период</div>
                    <?php else: ?>
                        <?php foreach ($recentVisitors as $vi => $visitor):
                            [$deviceIcon, $deviceName] = detectDevice($visitor['user_agent'] ?? '');
                            $browser = detectBrowser($visitor['user_agent'] ?? '');
                            $details = $visitorDetails[$visitor['ip']] ?? [];
                            $carPages = array_filter($details, fn($d) => !empty($d['car_id']));
                            $uniquePages = count(array_unique(array_column($details, 'page')));
                        ?>
                        <div class="visitor-row" onclick="toggleVisitor(<?= $vi ?>)" id="vrow-<?= $vi ?>">
                            <div class="d-flex align-items-center gap-3">
                                <span class="material-icons expand-arrow">expand_more</span>
                                <div style="font-size:1.3rem;"><?= $deviceIcon ?></div>
                                <div style="flex:1; min-width:0;">
                                    <div class="d-flex align-items-center gap-2 flex-wrap">
                                        <span class="ip-badge"><?= htmlspecialchars($visitor['ip']) ?></span>
                                        <span class="device-badge"><?= $deviceName ?> · <?= $browser ?></span>
                                    </div>
                                    <div style="font-size:0.78rem; color:#64748b; margin-top:0.15rem;">
                                        Первый визит: <?= date('H:i', strtotime($visitor['first_visit'])) ?>
                                        · Последний: <?= date('H:i', strtotime($visitor['last_visit'])) ?>
                                    </div>
                                </div>
                                <div class="text-end" style="white-space:nowrap;">
                                    <div class="fw-bold"><?= $visitor['total_views'] ?> <span style="font-size:0.75rem; color:#94a3b8;">стр.</span></div>
                                    <div style="font-size:0.75rem; color:#94a3b8;">
                                        <?= count($carPages) ?> авто · <?= $uniquePages ?> раздел.
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="visitor-detail" id="vdetail-<?= $vi ?>">
                            <ul class="visit-timeline">
                                <?php foreach ($details as $d):
                                    $pName = pageNameRu($d['page']);
                                    $time = date('H:i:s', strtotime($d['created_at']));
                                    // Если есть car_id, попробуем показать название
                                    $carTitle = '';
                                    if (!empty($d['car_id'])) {
                                        $cStmt = $pdo->prepare("SELECT title FROM cars WHERE id = :id");
                                        $cStmt->execute(['id' => $d['car_id']]);
                                        $carTitle = $cStmt->fetchColumn() ?: '';
                                    }
                                ?>
                                <li>
                                    <span class="time"><?= $time ?></span> —
                                    <strong><?= htmlspecialchars($pName) ?></strong>
                                    <?php if ($carTitle): ?>
                                        <span style="color:#f43f5e;"> → <?= htmlspecialchars($carTitle) ?></span>
                                    <?php endif; ?>
                                </li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="text-center mt-4" style="color:#475569; font-size:0.8rem;">
            <span class="material-icons" style="font-size:14px; vertical-align:middle;">info</span>
            Статистика обновляется в реальном времени. Визиты админов не учитываются.
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0-beta2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    function toggleVisitor(i) {
        const row = document.getElementById('vrow-' + i);
        const detail = document.getElementById('vdetail-' + i);
        row.classList.toggle('expanded');
        detail.classList.toggle('open');
    }
    </script>
</body>
</html>
