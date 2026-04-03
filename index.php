<?php
include "path.php";
include_once SITE_ROOT . "/app/database/db.php";
include_once SITE_ROOT . "/app/helpers/track_visit.php";

$page = isset($_GET['page']) ? intval($_GET['page']) : 1;
$perPage = 6;
$offset = ($page - 1) * $perPage;

$brand_id = isset($_GET['brand']) ? intval($_GET['brand']) : null;
$body_type = isset($_GET['body_type']) ? trim($_GET['body_type']) : null;
$price_min = isset($_GET['price_min']) && $_GET['price_min'] !== '' ? floatval($_GET['price_min']) : null;
$price_max = isset($_GET['price_max']) && $_GET['price_max'] !== '' ? floatval($_GET['price_max']) : null;
$transmission = isset($_GET['transmission']) ? trim($_GET['transmission']) : null;
$drive_type = isset($_GET['drive_type']) ? trim($_GET['drive_type']) : null;
$color = isset($_GET['color']) ? trim($_GET['color']) : null;
$mileage_max = isset($_GET['mileage_max']) && $_GET['mileage_max'] !== '' ? intval($_GET['mileage_max']) : null;
$engine_vol_min = isset($_GET['engine_vol_min']) && $_GET['engine_vol_min'] !== '' ? floatval($_GET['engine_vol_min']) : null;
$engine_vol_max = isset($_GET['engine_vol_max']) && $_GET['engine_vol_max'] !== '' ? floatval($_GET['engine_vol_max']) : null;
$year_min = isset($_GET['year_min']) && $_GET['year_min'] !== '' ? intval($_GET['year_min']) : null;

$filters = [];
if ($brand_id)
    $filters['brand'] = $brand_id;
if ($body_type)
    $filters['body_type'] = $body_type;
if ($price_min)
    $filters['price_min'] = $price_min;
if ($price_max)
    $filters['price_max'] = $price_max;
if ($transmission)
    $filters['transmission'] = $transmission;
if ($drive_type)
    $filters['drive_type'] = $drive_type;
if ($color)
    $filters['color'] = $color;
if ($mileage_max)
    $filters['mileage_max'] = $mileage_max;
if ($engine_vol_min)
    $filters['engine_volume_min'] = $engine_vol_min;
if ($engine_vol_max)
    $filters['engine_volume_max'] = $engine_vol_max;
if ($year_min)
    $filters['year_min'] = $year_min;

$cars = selectCarsForCatalog($perPage, $offset, $filters);
$totalCars = countCars($filters);
$totalPages = ceil($totalCars / $perPage);
$featured = selectFeaturedCars(5);
$brands = selectBrandsSorted();
$bodyTypes = getBodyTypes();

$user_favorites = [];
if (isset($_SESSION['id'])) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT id_car FROM favorites WHERE id_user = ?");
    $stmt->execute([$_SESSION['id']]);
    $user_favorites = $stmt->fetchAll(PDO::FETCH_COLUMN);
}
?>
<!doctype html>
<html lang="ru" class="<?= ($_SESSION['theme'] ?? 'dark') === 'dark' ? 'dark' : '' ?>">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>AvtoTachka &mdash; Каталог автомобилей из Китая</title>

    <!-- Tailwind CSS (Stitch Integration) -->
    <script src="https://cdn.tailwindcss.com?plugins=forms,typography"></script>
    <script>
        tailwind.config = {
            darkMode: "class",
            theme: {
                extend: {
                    colors: {
                        primary: "#e11d48", // Vibrant Red
                        "background-light": "#f8fafc",
                        "background-dark": "#0f172a",
                        accent: "#3b82f6", // Vibrant Blue
                    },
                    fontFamily: {
                        display: ["Outfit", "sans-serif"],
                        sans: ["Outfit", "sans-serif"],
                    },
                    borderRadius: {
                        DEFAULT: "0.75rem",
                    },
                    animation: {
                        'pulse-slow': 'pulse 3s cubic-bezier(0.4, 0, 0.6, 1) infinite',
                    }
                },
            },
        };
    </script>
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet" />
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;700&display=swap" rel="stylesheet" />

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0-beta2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.7.2/css/all.css">
    <link rel="stylesheet" href="assets/css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Comfortaa:wght@300;400;500;600;700&display=swap"
        rel="stylesheet">
</head>

<body
    class="bg-slate-50 dark:bg-background-dark text-slate-900 dark:text-slate-100 <?= ($_SESSION['theme'] ?? 'dark') === 'dark' ? 'dark-theme' : '' ?>">

    <?php include("app/include/header.php"); ?>

    <main
        class="max-w-7xl mx-auto px-4 py-16 flex flex-col lg:flex-row gap-8 mt-20">
        <!-- Фильтры (Sidebar) -->
        <aside class="w-full lg:w-1/4 space-y-6">
            <div class="glass dark:bg-slate-800/40 p-6 rounded-2xl sticky top-28">
                <h3 class="text-xl font-bold mb-6 flex items-center text-slate-900 dark:text-white">
                    <span class="material-icons mr-2 text-primary">tune</span> Фильтры
                </h3>
                <form action="index.php" method="get" class="space-y-6">
                    <div class="border-b border-slate-200 dark:border-white/10 pb-4">
                        <label
                            class="text-xs font-bold uppercase tracking-widest text-slate-500 dark:text-slate-400 mb-2 block">Бренд</label>
                        <select name="brand" <?= ($_SESSION['theme'] ?? 'dark') === 'dark' ? 'style="color-scheme: dark;"' : '' ?>
                            class="w-full bg-slate-100 dark:bg-slate-900 border-none rounded-lg p-3 text-sm focus:ring-primary transition-all text-slate-800 dark:text-white outline-none">
                            <option value="">Все бренды</option>
                            <?php foreach ($brands as $b): ?>
                                <option value="<?= $b['id'] ?>" <?= ($brand_id == $b['id']) ? 'selected' : '' ?>>
                                    <?= $b['name'] ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="border-b border-slate-200 dark:border-white/10 pb-4">
                        <label
                            class="text-xs font-bold uppercase tracking-widest text-slate-500 dark:text-slate-400 mb-2 block">Тип
                            кузова</label>
                        <select name="body_type" <?= ($_SESSION['theme'] ?? 'dark') === 'dark' ? 'style="color-scheme: dark;"' : '' ?>
                            class="w-full bg-slate-100 dark:bg-slate-900 border-none rounded-lg p-3 text-sm focus:ring-primary transition-all text-slate-800 dark:text-white outline-none">
                            <option value="">Все</option>
                            <?php foreach ($bodyTypes as $bt): ?>
                                <option value="<?= $bt ?>" <?= ($body_type == $bt) ? 'selected' : '' ?>><?= $bt ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="space-y-3 border-b border-slate-200 dark:border-white/10 pb-4">
                        <label
                            class="text-xs font-bold uppercase tracking-widest text-slate-500 dark:text-slate-400">Цена
                            (₽)</label>
                        <div class="flex items-center space-x-2">
                            <input name="price_min" value="<?= $price_min ?>"
                                class="w-1/2 bg-slate-100 dark:bg-slate-900 border-none rounded-lg p-2 text-sm text-slate-800 dark:text-white outline-none focus:ring-1 focus:ring-primary"
                                placeholder="От" type="number" min="0" />
                            <input name="price_max" value="<?= $price_max ?>"
                                class="w-1/2 bg-slate-100 dark:bg-slate-900 border-none rounded-lg p-2 text-sm text-slate-800 dark:text-white outline-none focus:ring-1 focus:ring-primary"
                                placeholder="До" type="number" min="0" />
                        </div>
                    </div>

                    <div class="border-b border-slate-200 dark:border-white/10 pb-4">
                        <label
                            class="text-xs font-bold uppercase tracking-widest text-slate-500 dark:text-slate-400 mb-2 block">Возраст авто</label>
                        <select name="year_min" <?= ($_SESSION['theme'] ?? 'dark') === 'dark' ? 'style="color-scheme: dark;"' : '' ?>
                            class="w-full bg-slate-100 dark:bg-slate-900 border-none rounded-lg p-3 text-sm focus:ring-primary transition-all text-slate-800 dark:text-white outline-none">
                            <option value="">Любой</option>
                            <?php
                            $currentYear = (int)date('Y');
                            $yearOptions = [
                                [$currentYear, 'до 1 года'],
                                [$currentYear - 2, 'до 3 лет'],
                                [$currentYear - 4, 'до 5 лет'],
                                [$currentYear - 9, 'до 10 лет'],
                                [0, 'старше 10 лет']
                            ];
                            foreach ($yearOptions as $yo):
                                if ($yo[0] === 0) continue; // "старше 10 лет" - не ограничиваем снизу
                            ?>
                                <option value="<?= $yo[0] ?>" <?= ($year_min == $yo[0]) ? 'selected' : '' ?>><?= $yo[1] ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="border-b border-slate-200 dark:border-white/10 pb-4">
                        <label
                            class="text-xs font-bold uppercase tracking-widest text-slate-500 dark:text-slate-400 mb-2 block">Коробка передач</label>
                        <select name="transmission" <?= ($_SESSION['theme'] ?? 'dark') === 'dark' ? 'style="color-scheme: dark;"' : '' ?>
                            class="w-full bg-slate-100 dark:bg-slate-900 border-none rounded-lg p-3 text-sm focus:ring-primary transition-all text-slate-800 dark:text-white outline-none">
                            <option value="">Все</option>
                            <?php
                            $transmissionOptions = ['Автомат', 'Механика', 'Робот', 'Вариатор'];
                            foreach ($transmissionOptions as $tr):
                            ?>
                                <option value="<?= $tr ?>" <?= ($transmission == $tr) ? 'selected' : '' ?>><?= $tr ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="border-b border-slate-200 dark:border-white/10 pb-4">
                        <label
                            class="text-xs font-bold uppercase tracking-widest text-slate-500 dark:text-slate-400 mb-2 block">Объём двигателя (л)</label>
                        <div class="flex items-center space-x-2">
                            <input name="engine_vol_min" value="<?= $engine_vol_min ?>"
                                class="w-1/2 bg-slate-100 dark:bg-slate-900 border-none rounded-lg p-2 text-sm text-slate-800 dark:text-white outline-none focus:ring-1 focus:ring-primary"
                                placeholder="От" type="number" min="0" max="15" step="0.1" />
                            <input name="engine_vol_max" value="<?= $engine_vol_max ?>"
                                class="w-1/2 bg-slate-100 dark:bg-slate-900 border-none rounded-lg p-2 text-sm text-slate-800 dark:text-white outline-none focus:ring-1 focus:ring-primary"
                                placeholder="До" type="number" min="0" max="15" step="0.1" />
                        </div>
                    </div>

                    <div class="border-b border-slate-200 dark:border-white/10 pb-4">
                        <label
                            class="text-xs font-bold uppercase tracking-widest text-slate-500 dark:text-slate-400 mb-2 block">Пробег</label>
                        <select name="mileage_max" <?= ($_SESSION['theme'] ?? 'dark') === 'dark' ? 'style="color-scheme: dark;"' : '' ?>
                            class="w-full bg-slate-100 dark:bg-slate-900 border-none rounded-lg p-3 text-sm focus:ring-primary transition-all text-slate-800 dark:text-white outline-none">
                            <option value="">Любой</option>
                            <?php
                            $mileageOptions = [
                                [10000, 'до 10 000 км'],
                                [30000, 'до 30 000 км'],
                                [60000, 'до 60 000 км'],
                                [100000, 'до 100 000 км']
                            ];
                            foreach ($mileageOptions as $mo):
                            ?>
                                <option value="<?= $mo[0] ?>" <?= ($mileage_max == $mo[0]) ? 'selected' : '' ?>><?= $mo[1] ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="border-b border-slate-200 dark:border-white/10 pb-4">
                        <label
                            class="text-xs font-bold uppercase tracking-widest text-slate-500 dark:text-slate-400 mb-2 block">Привод</label>
                        <select name="drive_type" <?= ($_SESSION['theme'] ?? 'dark') === 'dark' ? 'style="color-scheme: dark;"' : '' ?>
                            class="w-full bg-slate-100 dark:bg-slate-900 border-none rounded-lg p-3 text-sm focus:ring-primary transition-all text-slate-800 dark:text-white outline-none">
                            <option value="">Все</option>
                            <?php
                            $driveOptions = ['Передний', 'Задний', 'Полный'];
                            foreach ($driveOptions as $dr):
                            ?>
                                <option value="<?= $dr ?>" <?= ($drive_type == $dr) ? 'selected' : '' ?>><?= $dr ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="pb-4">
                        <label
                            class="text-xs font-bold uppercase tracking-widest text-slate-500 dark:text-slate-400 mb-2 block">Цвет</label>
                        <select name="color" <?= ($_SESSION['theme'] ?? 'dark') === 'dark' ? 'style="color-scheme: dark;"' : '' ?>
                            class="w-full bg-slate-100 dark:bg-slate-900 border-none rounded-lg p-3 text-sm focus:ring-primary transition-all text-slate-800 dark:text-white outline-none">
                            <option value="">Все</option>
                            <?php
                            $colorOptions = ['Чёрный', 'Белый', 'Серебристый/серый', 'Красный/Фиолетовый', 'Синий', 'Зелёный', 'Жёлтый/оранжевый', 'Шампанское/Коричневый'];
                            foreach ($colorOptions as $cl):
                            ?>
                                <option value="<?= $cl ?>" <?= ($color == $cl) ? 'selected' : '' ?>><?= $cl ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <button type="submit"
                        class="magnetic-btn w-full bg-primary py-3 rounded-xl font-bold shadow-lg shadow-primary/20 text-white border-none cursor-pointer">Применить</button>
                    <?php if (!empty($filters)): ?>
                        <a href="index.php"
                            class="block text-center mt-3 text-sm text-slate-500 hover:text-primary transition-colors"
                            style="text-decoration:none;">Сбросить фильтры</a>
                    <?php endif; ?>
                </form>
            </div>
        </aside>

        <!-- Каталог -->
        <section class="w-full lg:w-3/4">
            <div class="flex items-center justify-between mb-8">
                <h2 class="text-3xl font-bold text-slate-900 dark:text-white">Автомобили в наличии</h2>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                <?php if (count($cars) > 0): ?>
                    <?php foreach ($cars as $car): ?>
                        <div
                            class="tilt-card glass dark:bg-slate-800/40 rounded-2xl overflow-hidden group relative bg-[#fff] dark:bg-transparent border border-slate-200 dark:border-white/10">
                            <div class="relative h-64 overflow-hidden bg-slate-200 dark:bg-slate-900">
                                <?php if ($car['img']): ?>
                                    <a href="<?= BASE_URL ?>single.php?id=<?= $car['id'] ?>">
                                        <img alt="<?= $car['title'] ?>"
                                            class="w-full h-full object-cover transition-transform duration-700 group-hover:scale-110"
                                            src="<?= BASE_URL ?>assets/images/cars/<?= $car['img'] ?>" />
                                    </a>
                                <?php else: ?>
                                    <a href="<?= BASE_URL ?>single.php?id=<?= $car['id'] ?>"
                                        class="w-full h-full flex items-center justify-center text-slate-400"
                                        style="text-decoration:none;">
                                        <i class="fas fa-car fa-4x"></i>
                                    </a>
                                <?php endif; ?>

                                <div class="absolute top-4 left-4 flex space-x-2">
                                    <span
                                        class="px-3 py-1 bg-primary text-white text-[10px] font-bold rounded uppercase"><?= $car['brand_name'] ?></span>
                                </div>

                                <?php if (isset($_SESSION['id'])): ?>
                                    <button
                                        class="fav-btn absolute top-4 right-4 w-10 h-10 rounded-full glass hover:bg-white/20 transition-colors border-none cursor-pointer flex items-center justify-center z-20 pointer-events-auto"
                                        data-id="<?= $car['id'] ?>" title="В избранное">
                                        <i
                                            class="<?= in_array($car['id'], $user_favorites) ? 'fas text-primary' : 'far text-white' ?> fa-heart text-xl transition-colors"></i>
                                    </button>
                                <?php endif; ?>

                                <?php
                                $compareList = isset($_SESSION['compare']) ? $_SESSION['compare'] : [];
                                $inCompare = in_array($car['id'], $compareList);
                                $compareTopPos = isset($_SESSION['id']) ? 'top-16' : 'top-4';
                                ?>
                                <button
                                    class="compare-btn absolute <?= $compareTopPos ?> right-4 w-10 h-10 rounded-full glass <?= $inCompare ? 'bg-primary' : 'hover:bg-primary' ?> transition-colors border-none cursor-pointer flex items-center justify-center z-10"
                                    data-id="<?= $car['id'] ?>" title="К сравнению">
                                    <span class="material-icons text-white text-[20px]">balance</span>
                                </button>

                                <div
                                    class="absolute inset-0 bg-slate-900/80 p-6 flex flex-col justify-end spec-fade pointer-events-none">
                                    <div class="grid grid-cols-2 gap-4 text-xs text-white dark:text-slate-300">
                                        <div class="flex items-center space-x-2"><span
                                                class="material-icons text-primary text-sm">calendar_today</span><span><?= $car['year'] ?>
                                                г.</span></div>
                                        <div class="flex items-center space-x-2"><span
                                                class="material-icons text-primary text-sm">speed</span><span><?= $car['horsepower'] ?>
                                                л.с.</span></div>
                                        <div class="flex items-center space-x-2"><span
                                                class="material-icons text-primary text-sm">settings</span><span><?= $car['engine_volume'] ?>
                                                л.</span></div>
                                        <div class="flex items-center space-x-2"><span
                                                class="material-icons text-primary text-sm">directions_car</span><span><?= $car['body_type'] ?></span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="p-6">
                                <h3 class="text-xl font-bold mb-1 text-slate-900 dark:text-white">
                                    <a href="<?= BASE_URL ?>single.php?id=<?= $car['id'] ?>"
                                        style="text-decoration:none; color:inherit;"><?= $car['title'] ?></a>
                                </h3>
                                <div class="flex items-end justify-between mt-4">
                                    <div>
                                        <span class="text-slate-500 text-xs block mb-1">Цена от</span>
                                        <span
                                            class="text-2xl font-bold text-primary"><?= number_format($car['price'], 0, '', ' ') ?>
                                            ₽</span>
                                    </div>
                                    <a href="<?= BASE_URL ?>single.php?id=<?= $car['id'] ?>"
                                        class="material-icons text-slate-400 group-hover:text-primary transition-colors"
                                        style="text-decoration:none;">arrow_forward</a>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="col-span-full text-center py-10 text-slate-500 dark:text-slate-400">
                        <i class="fas fa-search fa-3x mb-3 text-slate-300 dark:text-slate-600"></i>
                        <p>По вашему запросу автомобили не найдены.</p>
                    </div>
                <?php endif; ?>
            </div>

            <?php if ($totalPages > 1): ?>
                <div class="mt-12 flex items-center justify-center space-x-2">
                    <?php
                    $range = 2;
                    $pages_to_show = [];
                    if ($totalPages <= 7) {
                        for ($i = 1; $i <= $totalPages; $i++)
                            $pages_to_show[] = $i;
                    } else {
                        $pages_to_show[] = 1;
                        $start = max(2, $page - $range);
                        $end = min($totalPages - 1, $page + $range);
                        if ($start > 2)
                            $pages_to_show[] = '...';
                        for ($i = $start; $i <= $end; $i++)
                            $pages_to_show[] = $i;
                        if ($end < $totalPages - 1)
                            $pages_to_show[] = '...';
                        $pages_to_show[] = $totalPages;
                    }

                    // Base params helper
                    $getBaseParams = function ($p) use ($brand_id, $body_type, $price_min, $price_max, $transmission, $drive_type, $color, $mileage_max, $engine_vol_min, $engine_vol_max, $year_min) {
                        $pa = ['page' => $p];
                        if ($brand_id)
                            $pa['brand'] = $brand_id;
                        if ($body_type)
                            $pa['body_type'] = $body_type;
                        if ($price_min)
                            $pa['price_min'] = $price_min;
                        if ($price_max)
                            $pa['price_max'] = $price_max;
                        if ($transmission)
                            $pa['transmission'] = $transmission;
                        if ($drive_type)
                            $pa['drive_type'] = $drive_type;
                        if ($color)
                            $pa['color'] = $color;
                        if ($mileage_max)
                            $pa['mileage_max'] = $mileage_max;
                        if ($engine_vol_min)
                            $pa['engine_vol_min'] = $engine_vol_min;
                        if ($engine_vol_max)
                            $pa['engine_vol_max'] = $engine_vol_max;
                        if ($year_min)
                            $pa['year_min'] = $year_min;
                        return $pa;
                    };

                    // Previous Button
                    if ($page > 1): ?>
                        <a href="?<?= http_build_query($getBaseParams($page - 1)) ?>"
                            class="w-10 h-10 rounded-lg glass text-slate-700 dark:text-slate-400 hover:bg-primary hover:text-white flex items-center justify-center transition-all border border-slate-200 dark:border-white/10"
                            style="text-decoration:none;">
                            <span class="material-icons text-xl">chevron_left</span>
                        </a>
                    <?php endif;

                    foreach ($pages_to_show as $p):
                        if ($p === '...'): ?>
                            <span class="w-8 h-10 flex items-center justify-center text-slate-400">...</span>
                        <?php else:
                            $params = $getBaseParams($p);
                            $activeClass = ($p == $page) ? 'bg-primary text-white border-primary shadow-lg shadow-primary/25' : 'glass text-slate-700 dark:text-slate-400 hover:bg-primary hover:text-white hover:border-primary border-slate-200 dark:border-white/10';
                            ?>
                            <a href="?<?= http_build_query($params) ?>"
                                class="w-10 h-10 rounded-lg <?= $activeClass ?> flex items-center justify-center transition-all font-bold"
                                style="text-decoration:none; border-width:1px; border-style:solid;">
                                <?= $p ?>
                            </a>
                        <?php endif;
                    endforeach;

                    // Next Button
                    if ($page < $totalPages): ?>
                        <a href="?<?= http_build_query($getBaseParams($page + 1)) ?>"
                            class="w-10 h-10 rounded-lg glass text-slate-700 dark:text-slate-400 hover:bg-primary hover:text-white flex items-center justify-center transition-all border border-slate-200 dark:border-white/10"
                            style="text-decoration:none;">
                            <span class="material-icons text-xl">chevron_right</span>
                        </a>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </section>
    </main>

    <section
        class="py-20 bg-slate-100 dark:bg-slate-900 relative overflow-hidden mt-8 border-y border-slate-200 dark:border-white/10">
        <div class="absolute inset-0 opacity-20 pointer-events-none">
            <div class="absolute top-0 left-1/4 w-96 h-96 bg-primary/30 rounded-full blur-[120px] animate-pulse-slow">
            </div>
            <div
                class="absolute bottom-0 right-1/4 w-96 h-96 bg-accent/30 rounded-full blur-[120px] animate-pulse-slow">
            </div>
        </div>
        <div class="max-w-7xl mx-auto px-4 relative z-10 text-center flex flex-col items-center">
            <h2 class="text-4xl font-bold mb-6 text-slate-900 dark:text-white">Управляйте будущим уже сегодня</h2>
            <p class="text-slate-600 dark:text-slate-400 max-w-2xl mx-auto mb-10">Наш шоурум предлагает самый широкий
                выбор высокотехнологичных китайских автомобилей. Откройте для себя новые стандарты.</p>
            <a href="<?= BASE_URL ?>#catalog" style="text-decoration: none;"
                class="magnetic-btn bg-slate-900 dark:bg-white !text-white dark:!text-slate-900 px-10 py-4.5 rounded-full font-bold text-lg hover:shadow-2xl transition-all border-none flex items-center justify-center min-w-[300px]">
                К каталогу автомобилей
            </a>
        </div>
    </section>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0-beta2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            document.querySelectorAll('.compare-btn').forEach(btn => {
                btn.addEventListener('click', function (e) {
                    e.preventDefault();
                    const carId = this.dataset.id;
                    const isAdded = this.classList.contains('bg-primary');
                    const action = isAdded ? 'remove' : 'add';

                    fetch('<?= BASE_URL ?>ajax_compare.php', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({ action: action, car_id: carId })
                    })
                        .then(res => res.json())
                        .then(data => {
                            if (data.status === 'success') {
                                const badge = document.getElementById('compareBadge');
                                if (badge) {
                                    badge.textContent = data.count;
                                    if (data.count > 0) {
                                        badge.classList.remove('hidden');
                                    } else {
                                        badge.classList.add('hidden');
                                    }
                                }

                                if (data.is_added) {
                                    this.classList.remove('hover:bg-primary');
                                    this.classList.add('bg-primary');
                                } else {
                                    this.classList.add('hover:bg-primary');
                                    this.classList.remove('bg-primary');
                                }
                            } else {
                                alert(data.message);
                            }
                        })
                        .catch(err => console.error('Error:', err));
                });
            });
        });
    </script>
    <?php include("app/include/footer.php"); ?>
</body>

</html>