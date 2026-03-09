<?php
include "path.php";
include_once SITE_ROOT . "/app/database/db.php";
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['search-term'])) {
    $cars = searchCars($_POST['search-term']);
    $searchTerm = htmlspecialchars($_POST['search-term']);
} else {
    $cars = [];
    $searchTerm = '';
}
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
    <title>Поиск — AvtoTachka</title>
</head>

<body class="bg-slate-50 dark:bg-background-dark text-slate-900 dark:text-slate-100 <?= ($_SESSION['theme'] ?? 'dark') === 'dark' ? 'dark-theme' : '' ?>">

    <?php include("app/include/header.php"); ?>

    <div class="container">
        <div class="content row">
            <div class="main-content col-12">
                <h2>Результаты поиска <?= $searchTerm ? 'по запросу: «' . $searchTerm . '»' : '' ?></h2>
                <div class="row">
                    <?php if (count($cars) > 0): ?>
                        <?php foreach ($cars as $car): ?>
                            <div class="col-md-4 col-12 mb-4">
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
                                        <span class="car-brand-badge"><?= $car['brand_name'] ?></span>
                                        <h5><a href="<?= BASE_URL . 'single.php?id=' . $car['id']; ?>"><?= $car['title'] ?></a></h5>
                                        <div class="car-specs">
                                            <span><i class="fas fa-calendar"></i> <?= $car['year'] ?></span>
                                            <span><i class="fas fa-cog"></i> <?= $car['engine_volume'] ?>л /
                                                <?= $car['horsepower'] ?> л.с.</span>
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
                            <p>Ничего не найдено. Попробуйте другой запрос.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <?php include("app/include/footer.php"); ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0-beta2/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-b5kHyXgcpbZJO/tY9Ul7kGkf1S0CWuKcCD38l8YkeH8z8QjE0GmW1gYU5S9FOnJ0"
        crossorigin="anonymous"></script>
</body>

</html>