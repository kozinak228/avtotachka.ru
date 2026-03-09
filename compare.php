<?php
session_start();
require_once("app/database/db.php");
require "path.php";
?>
<!DOCTYPE html>
<html lang="ru" class="<?= ($_SESSION['theme'] ?? 'dark') === 'dark' ? 'dark' : '' ?>">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>AvtoTachka - Сравнение Автомобилей</title>

    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com?plugins=forms,typography"></script>

    <!-- Google Fonts & Icons -->
    <link
        href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700&family=Material+Icons+Outlined&family=Material+Icons&display=swap"
        rel="stylesheet" />

    <!-- Tailwind Config -->
    <script>
        tailwind.config = {
            darkMode: "class",
            theme: {
                extend: {
                    colors: {
                        primary: "#e11d48", // Rose 600
                        "background-light": "#f8fafc",
                        "background-dark": "#0f172a",
                    },
                    fontFamily: {
                        display: ["Plus Jakarta Sans", "sans-serif"],
                    }
                },
            },
        };
    </script>

    <!-- Custom Global CSS -->
    <link rel="stylesheet" href="assets/css/style.css">

    <style>
        .compare-table {
            display: grid;
            gap: 1rem;
            overflow-x: auto;
            padding-bottom: 2rem;
        }

        /* Sticky first column for labels */
        .spec-label-col {
            position: sticky;
            left: 0;
            z-index: 10;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(10px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .animate-fade-in {
            animation: fadeIn 0.5s ease-out forwards;
        }
    </style>
</head>

<body
    class="font-display bg-background-light dark:bg-background-dark text-slate-900 dark:text-slate-100 transition-colors duration-300 min-h-screen flex flex-col <?= ($_SESSION['theme'] ?? 'light') === 'dark' ? 'dark-theme' : '' ?>">

    <!-- Header -->
    <?php include("app/include/header.php"); ?>

    <!-- Main Content -->
    <main class="flex-grow container mx-auto px-4 sm:px-6 lg:px-8 py-24 sm:py-32">
        <div class="max-w-7xl mx-auto">

            <div class="flex items-center gap-3 mb-8 animate-fade-in">
                <span class="material-icons text-primary text-4xl">balance</span>
                <h1 class="text-3xl font-extrabold tracking-tight text-slate-900 dark:text-white">Сравнение Автомобилей
                </h1>
            </div>

            <?php
            $compareList = isset($_SESSION['compare']) ? $_SESSION['compare'] : [];

            if (empty($compareList)): ?>
                <div class="bg-white dark:bg-slate-800 rounded-3xl p-12 text-center border border-slate-200 dark:border-slate-700 shadow-sm animate-fade-in"
                    style="animation-delay: 0.1s;">
                    <span
                        class="material-icons-outlined text-6xl text-slate-300 dark:text-slate-600 mb-4">directions_car</span>
                    <h2 class="text-2xl font-bold text-slate-800 dark:text-white mb-2">Список сравнения пуст</h2>
                    <p class="text-slate-500 dark:text-slate-400 mb-8 max-w-md mx-auto">Вы еще не добавили ни одного
                        автомобиля для сравнения. Перейдите в каталог, чтобы выбрать интересующие вас модели.</p>
                    <a href="<?= BASE_URL ?>"
                        class="inline-flex items-center gap-2 px-8 py-3 bg-primary hover:bg-red-600 text-white font-bold rounded-xl transition-all shadow-[0_0_15px_rgba(225,29,72,0.3)] hover:shadow-[0_0_25px_rgba(225,29,72,0.5)]">
                        Перейти в каталог
                        <span class="material-icons text-sm">arrow_forward</span>
                    </a>
                </div>
            <?php else:
                // Fetch car data safely
                $inQuery = implode(',', array_fill(0, count($compareList), '?'));
                global $pdo;
                $stmt = $pdo->prepare("SELECT * FROM cars WHERE id IN ($inQuery)");
                $stmt->execute($compareList);
                $cars = $stmt->fetchAll(PDO::FETCH_ASSOC);

                // Sort cars to match insertion order in session
                $orderedCars = [];
                foreach ($compareList as $id) {
                    foreach ($cars as $car) {
                        if ($car['id'] == $id) {
                            $orderedCars[] = $car;
                            break;
                        }
                    }
                }

                $gridColsClass = 'grid-cols-' . (count($orderedCars) + 1); // +1 for labels
                ?>

                <div class="bg-white dark:bg-slate-800 rounded-3xl border border-slate-200 dark:border-slate-700 shadow-sm overflow-hidden animate-fade-in"
                    style="animation-delay: 0.1s;">
                    <div class="compare-table"
                        style="grid-template-columns: minmax(150px, 1fr) repeat(<?= count($orderedCars) ?>, minmax(250px, 1fr));">

                        <!-- Header Row (Images & Titles) -->
                        <div
                            class="spec-label-col p-6 flex flex-col justify-end border-b border-r border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-900">
                            <h3 class="font-bold text-lg text-slate-900 dark:text-white">Параметры</h3>
                        </div>

                        <?php foreach ($orderedCars as $index => $car): ?>
                            <div
                                class="p-6 border-b border-slate-200 dark:border-slate-700 flex flex-col relative group text-center">
                                <!-- Remove Button -->
                                <button onclick="removeFromCompare(<?= $car['id'] ?>)"
                                    class="absolute top-4 right-4 z-20 w-8 h-8 flex items-center justify-center bg-white/90 dark:bg-slate-800/90 text-slate-400 hover:text-primary rounded-full shadow-md backdrop-blur-sm opacity-0 group-hover:opacity-100 transition-all hover:scale-110">
                                    <span class="material-icons text-sm">close</span>
                                </button>

                                <a href="<?= BASE_URL . 'single.php?id=' . $car['id'] ?>"
                                    class="block relative rounded-2xl overflow-hidden aspect-video mb-4 shadow-sm group-hover:shadow-md transition-shadow">
                                    <?php if (!empty($car['img'])): ?>
                                        <img src="<?= BASE_URL . 'assets/images/cars/' . htmlspecialchars($car['img']) ?>"
                                            alt="<?= htmlspecialchars($car['title'] ?? '') ?>"
                                            class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500">
                                    <?php else: ?>
                                        <div class="w-full h-full bg-slate-200 dark:bg-slate-800 flex items-center justify-center">
                                            <span class="material-icons-outlined text-4xl text-slate-400">directions_car</span>
                                        </div>
                                    <?php endif; ?>
                                    <div class="absolute inset-0 bg-black/10 group-hover:bg-transparent transition-colors">
                                    </div>
                                </a>
                                <a href="<?= BASE_URL . 'single.php?id=' . $car['id'] ?>"
                                    class="text-lg font-bold text-slate-900 dark:text-white hover:text-primary transition-colors line-clamp-2 leading-tight">
                                    <?= htmlspecialchars($car['title'] ?? '') ?>
                                </a>
                                <div class="mt-2 text-primary font-bold text-xl">
                                    <?= number_format($car['price'], 0, '', ' ') ?> ₽
                                </div>
                            </div>
                        <?php endforeach; ?>

                        <!-- Specification Rows -->
                        <?php
                        $specs = [
                            ['label' => 'Год выпуска', 'key' => 'year', 'icon' => 'calendar_today'],
                            ['label' => 'Пробег (км)', 'key' => 'mileage', 'icon' => 'speed'],
                            ['label' => 'Двигатель', 'key' => 'engine_type', 'icon' => 'settings'],
                            ['label' => 'Объем (л)', 'key' => 'engine_volume', 'icon' => 'local_gas_station'],
                            ['label' => 'Мощность (л.с.)', 'key' => 'horsepower', 'icon' => 'bolt'],
                            ['label' => 'Кузов', 'key' => 'body_type', 'icon' => 'directions_car'],
                            ['label' => 'Привод', 'key' => 'drive_type', 'icon' => 'tire_repair'],
                            ['label' => 'Коробка', 'key' => 'transmission', 'icon' => 'account_tree'],
                            ['label' => 'Цвет', 'key' => 'color', 'icon' => 'palette'],
                        ];

                        foreach ($specs as $rowIndex => $spec):
                            $bgClass = $rowIndex % 2 === 0 ? 'bg-white dark:bg-slate-900' : 'bg-slate-50 dark:bg-slate-800';
                            ?>
                            <!-- Spec Label -->
                            <div
                                class="spec-label-col p-4 flex items-center gap-3 border-r border-slate-200 dark:border-slate-700 <?= $bgClass ?> text-slate-500 dark:text-slate-400">
                                <span class="material-icons-outlined text-[18px] opacity-70">
                                    <?= $spec['icon'] ?>
                                </span>
                                <span class="font-medium text-sm">
                                    <?= $spec['label'] ?>
                                </span>
                            </div>

                            <!-- Spec Values -->
                            <?php foreach ($orderedCars as $car): ?>
                                <div class="p-4 flex flex-col justify-center items-center text-center border-l-0 <?= $bgClass ?>">
                                    <span class="font-semibold text-slate-900 dark:text-white">
                                        <?php
                                        $val = $car[$spec['key']];
                                        echo (!empty($val) || $val === '0') ? htmlspecialchars($val) : '<span class="text-slate-300 dark:text-slate-600">-</span>';
                                        ?>
                                    </span>
                                </div>
                            <?php endforeach; ?>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </main>

    <!-- Footer -->
    <?php include("app/include/footer.php"); ?>

    <script>
        function removeFromCompare(carId) {
            fetch('<?= BASE_URL ?>ajax_compare.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ action: 'remove', car_id: carId })
            })
                .then(res => res.json())
                .then(data => {
                    if (data.status === 'success') {
                        // Update header badge
                        const badge = document.getElementById('compareBadge');
                        if (badge) {
                            badge.textContent = data.count;
                            if (data.count === 0) badge.classList.add('hidden');
                        }
                        // Reload page to update layout
                        window.location.reload();
                    }
                })
                .catch(err => console.error('Error:', err));
        }
    </script>
</body>

</html>