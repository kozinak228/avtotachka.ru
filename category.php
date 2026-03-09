<?php
include "path.php";
include_once SITE_ROOT . "/app/database/db.php";

if (!isset($_GET['id'])) {
    header('location: ' . BASE_URL);
    exit();
}

// Редирект на index.php где есть фильтры
header('location: ' . BASE_URL . 'index.php?brand=' . $_GET['id']);
exit();

$brandInfo = selectOne('brands', ['id' => $_GET['id']]);

$cars = selectAll('cars', ['id_brand' => $_GET['id'], 'status' => 1]);
$brands = selectAll('brands');
?>
<!doctype html>
<html lang="ru" class="<?= ($_SESSION['theme'] ?? 'dark') === 'dark' ? 'dark' : '' ?>">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
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

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0-beta2/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-BmbxuPwQa2lc/FVzBcNJ7UAyJxM6wuqIj61tLrc4wSX0szH/Ev+nYRRuWlolflfl" crossorigin="anonymous">
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.7.2/css/all.css"
        integrity="sha384-fnmOCqbTlWIlj8LyTjo7mOUStjsKC4pOpQbqyi7RrhN7udi9RwhKkMHpvLbHG9Sr" crossorigin="anonymous">
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="preconnect" href="https://fonts.gstatic.com">
    <link href="https://fonts.googleapis.com/css2?family=Comfortaa:wght@300;400;500;600;700&display=swap"
        rel="stylesheet">
    <title><?= $brandInfo['name'] ?> — AvtoTachka</title>
</head>

<body class="bg-slate-50 dark:bg-background-dark text-slate-900 dark:text-slate-100 <?= ($_SESSION['theme'] ?? 'dark') === 'dark' ? 'dark-theme' : '' ?>">

    <?php include("app/include/header.php"); ?>

    <div class="container">
        <div class="content row">
            <div class="main-content col-md-9 col-12">
                <h2>Автомобили <strong><?= $brandInfo['name']; ?></strong></h2>
                <div class="row">
                    <?php if (count($cars) > 0): ?>
                        <?php foreach ($cars as $car): ?>
                            <div class="col-md-6 col-12 mb-4">
                                <div class="car-card">
                                    <div class="car-card-img">
                                        <?php if ($car['img']): ?>
                                            <img src="<?= BASE_URL . 'assets/images/cars/' . $car['img'] ?>" alt="<?= $car['title'] ?>"
                                                class="img-fluid">
                                        <?php else: ?>
                                            <div class="car-no-img"><i class="fas fa-car fa-3x"></i></div>
                                        <?php endif; ?>
                                    </div>
                                    <div class="car-card-body">
                                        <span class="car-brand-badge"><?= $brandInfo['name'] ?></span>
                                        <h5><a href="<?= BASE_URL . 'single.php?id=' . $car['id']; ?>"><?= $car['title'] ?></a></h5>
                                        <div class="car-specs">
                                            <span><i class="fas fa-calendar"></i> <?= $car['year'] ?></span>
                                            <span><i class="fas fa-cog"></i> <?= $car['engine_volume'] ?>л /
                                                <?= $car['horsepower'] ?> л.с.</span>
                                            <span><i class="fas fa-road"></i> <?= $car['body_type'] ?></span>
                                        </div>
                                        <div class="car-price">
                                            <?= number_format($car['price'], 0, '', ' ') ?> &#8381;
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="col-12">
                            <p>Нет автомобилей этого бренда в каталоге.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <div class="sidebar col-md-3 col-12">
                <div class="section search">
                    <h3>Поиск</h3>
                    <form action="search.php" method="post">
                        <input type="text" name="search-term" class="text-input" placeholder="Марка, модель...">
                    </form>
                </div>

                <div class="section topics brands-section">
                    <h3 class="brands-toggle" onclick="toggleBrands()">Бренды <span id="brands-arrow">▼</span></h3>
                    <input type="text" id="brand-search" class="brand-search-input" placeholder="Поиск бренда..." oninput="filterBrands()">
                    <ul id="brands-list">
                        <li><a href="<?= BASE_URL ?>">Все бренды</a></li>
                        <?php foreach ($brands as $i => $b): ?>
                            <li class="brand-item <?= $i >= 5 ? 'brand-hidden' : '' ?>" data-name="<?= mb_strtolower($b['name']) ?>">
                                <a href="<?= BASE_URL . 'category.php?id=' . $b['id']; ?>"><?= $b['name']; ?></a>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                    <?php if (count($brands) > 5): ?>
                        <button type="button" class="btn btn-sm btn-outline-secondary w-100 mt-2" id="brands-show-all" onclick="showAllBrands()">Показать все (<?= count($brands) ?>)</button>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <?php include("app/include/footer.php"); ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0-beta2/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-b5kHyXgcpbZJO/tY9Ul7kGkf1S0CWuKcCD38l8YkeH8z8QjE0GmW1gYU5S9FOnJ0"
        crossorigin="anonymous"></script>
    <script>
        let brandsExpanded = false;
        let brandsCollapsed = false;

        function toggleBrands() {
            const list = document.getElementById('brands-list');
            const arrow = document.getElementById('brands-arrow');
            const search = document.getElementById('brand-search');
            const showAllBtn = document.getElementById('brands-show-all');
            brandsCollapsed = !brandsCollapsed;
            if (brandsCollapsed) {
                list.style.display = 'none';
                search.style.display = 'none';
                if (showAllBtn) showAllBtn.style.display = 'none';
                arrow.textContent = '▶';
            } else {
                list.style.display = '';
                search.style.display = '';
                if (showAllBtn) showAllBtn.style.display = '';
                arrow.textContent = '▼';
            }
        }

        function showAllBrands() {
            const items = document.querySelectorAll('.brand-item');
            const btn = document.getElementById('brands-show-all');
            brandsExpanded = !brandsExpanded;
            items.forEach(item => {
                if (brandsExpanded) {
                    item.classList.remove('brand-hidden');
                } else {
                    const allItems = Array.from(document.querySelectorAll('.brand-item'));
                    const idx = allItems.indexOf(item);
                    if (idx >= 5) item.classList.add('brand-hidden');
                }
            });
            btn.textContent = brandsExpanded ? 'Свернуть' : 'Показать все (' + document.querySelectorAll('.brand-item').length + ')';
        }

        function filterBrands() {
            const query = document.getElementById('brand-search').value.toLowerCase();
            const items = document.querySelectorAll('.brand-item');
            const btn = document.getElementById('brands-show-all');
            items.forEach(item => {
                const name = item.getAttribute('data-name');
                if (query === '') {
                    if (!brandsExpanded) {
                        const allItems = Array.from(document.querySelectorAll('.brand-item'));
                        const idx = allItems.indexOf(item);
                        item.style.display = idx >= 5 ? 'none' : '';
                    } else {
                        item.style.display = '';
                    }
                } else {
                    item.style.display = name.includes(query) ? '' : 'none';
                }
            });
            if (btn) btn.style.display = query ? 'none' : '';
        }
    </script>
</body>

</html>