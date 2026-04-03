<?php
include "path.php";
include_once SITE_ROOT . "/app/database/db.php";
include_once SITE_ROOT . "/app/helpers/track_visit.php";

// Требуем авторизации
if (!isset($_SESSION['id'])) {
    header('location: ' . BASE_URL . 'log.php');
    exit;
}

global $pdo;
$stmt_fav = $pdo->prepare("
    SELECT c.*, b.name AS brand_name 
    FROM favorites f 
    JOIN cars c ON f.id_car = c.id 
    LEFT JOIN brands b ON c.id_brand = b.id 
    WHERE f.id_user = ? 
    ORDER BY f.created_at DESC
");
$stmt_fav->execute([$_SESSION['id']]);
$cars = $stmt_fav->fetchAll();

// Для сердечек все эти машины уже в избранном
$user_favorites = array_column($cars, 'id');

?>
<!doctype html>
<html lang="ru" class="<?= ($_SESSION['theme'] ?? 'dark') === 'dark' ? 'dark' : '' ?>">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Избранное &mdash; AvtoTachka</title>

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

    <main class="max-w-7xl mx-auto px-4 py-8 mt-24 min-h-[60vh]">
        <div class="flex items-center justify-between mb-8">
            <h1 class="text-4xl font-bold text-slate-900 dark:text-white flex items-center gap-3">
                <span class="material-icons text-primary text-4xl">favorite</span>
                Сохраненные автомобили
            </h1>
            <a href="<?= BASE_URL ?>profile.php"
                class="text-slate-500 hover:text-primary transition-colors flex items-center gap-1 font-medium"
                style="text-decoration:none;">
                <span class="material-icons text-sm">arrow_back</span> В профиль
            </a>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
            <?php if (count($cars) > 0): ?>
                <?php foreach ($cars as $car): ?>
                    <div id="car-card-<?= $car['id'] ?>"
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
                                <span class="px-3 py-1 bg-primary text-white text-[10px] font-bold rounded uppercase">
                                    <?= $car['brand_name'] ?>
                                </span>
                            </div>

                            <button
                                class="fav-btn absolute top-4 right-4 w-10 h-10 rounded-full glass hover:bg-white/20 transition-colors border-none cursor-pointer flex items-center justify-center z-20 pointer-events-auto"
                                data-id="<?= $car['id'] ?>" title="Убрать из избранного"
                                onclick="removeFavoriteCard(this, <?= $car['id'] ?>)">
                                <i class="fas fa-heart text-xl text-primary transition-colors"></i>
                            </button>

                            <?php
                            $compareList = isset($_SESSION['compare']) ? $_SESSION['compare'] : [];
                            $inCompare = in_array($car['id'], $compareList);
                            ?>
                            <button
                                class="compare-btn absolute top-16 right-4 w-10 h-10 rounded-full glass <?= $inCompare ? 'bg-primary' : 'hover:bg-primary' ?> transition-colors border-none cursor-pointer flex items-center justify-center z-10"
                                data-id="<?= $car['id'] ?>" title="К сравнению">
                                <span class="material-icons text-white text-[20px]">balance</span>
                            </button>

                            <div
                                class="absolute inset-0 bg-slate-900/80 p-6 flex flex-col justify-end spec-fade pointer-events-none">
                                <div class="grid grid-cols-2 gap-4 text-xs text-white dark:text-slate-300">
                                    <div class="flex items-center space-x-2"><span
                                            class="material-icons text-primary text-sm">calendar_today</span><span>
                                            <?= $car['year'] ?> г.
                                        </span></div>
                                    <div class="flex items-center space-x-2"><span
                                            class="material-icons text-primary text-sm">speed</span><span>
                                            <?= $car['horsepower'] ?> л.с.
                                        </span></div>
                                    <div class="flex items-center space-x-2"><span
                                            class="material-icons text-primary text-sm">settings</span><span>
                                            <?= $car['engine_volume'] ?> л.
                                        </span></div>
                                    <div class="flex items-center space-x-2"><span
                                            class="material-icons text-primary text-sm">directions_car</span><span>
                                            <?= $car['body_type'] ?>
                                        </span></div>
                                </div>
                            </div>
                        </div>
                        <div class="p-6">
                            <h3 class="text-xl font-bold mb-1 text-slate-900 dark:text-white line-clamp-1">
                                <a href="<?= BASE_URL ?>single.php?id=<?= $car['id'] ?>"
                                    style="text-decoration:none; color:inherit;">
                                    <?= $car['title'] ?>
                                </a>
                            </h3>
                            <div class="flex items-end justify-between mt-4">
                                <div>
                                    <span class="text-slate-500 text-xs block mb-1">Цена от</span>
                                    <span class="text-2xl font-bold text-primary">
                                        <?= number_format($car['price'], 0, '', ' ') ?> ₽
                                    </span>
                                </div>
                                <a href="<?= BASE_URL ?>single.php?id=<?= $car['id'] ?>"
                                    class="material-icons text-slate-400 group-hover:text-primary transition-colors"
                                    style="text-decoration:none;">arrow_forward</a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div
                    class="col-span-full text-center py-20 glass dark:bg-slate-800/40 rounded-3xl border-dashed border-2 border-slate-300 dark:border-white/10">
                    <span class="material-icons text-6xl text-slate-300 dark:text-slate-600 mb-4 block">heart_broken</span>
                    <h3 class="text-2xl font-bold text-slate-900 dark:text-white mb-2">Здесь пока пусто</h3>
                    <p class="text-slate-500 dark:text-slate-400 max-w-md mx-auto mb-8">Вы еще не добавили ни одного
                        автомобиля в избранное. Перейдите в каталог, чтобы найти автомобиль своей мечты.</p>
                    <a href="<?= BASE_URL ?>#"
                        class="inline-block py-3 px-8 bg-primary text-white font-bold rounded-xl shadow-lg shadow-primary/20 hover:scale-105 transition-all"
                        style="text-decoration:none;">
                        Перейти в каталог
                    </a>
                </div>
            <?php endif; ?>
        </div>
    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0-beta2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            // Compare logic
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

        // Custom favorites removal logic for this page
        function removeFavoriteCard(btnEl, carId) {
            fetch('<?= BASE_URL ?>ajax_favorites.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ car_id: carId })
            })
                .then(res => res.json())
                .then(data => {
                    if (data.status === 'success') {
                        if (data.action === 'removed') {
                            // Fade out and remove the card
                            const card = document.getElementById('car-card-' + carId);
                            if (card) {
                                card.style.transition = 'all 0.4s ease';
                                card.style.opacity = '0';
                                card.style.transform = 'scale(0.95)';
                                setTimeout(() => {
                                    card.remove();
                                    // Check if empty
                                    const remainingCards = document.querySelectorAll('.tilt-card');
                                    if (remainingCards.length === 0) {
                                        location.reload(); // Reload to show empty state
                                    }
                                }, 400);
                            }
                        }
                    }
                })
                .catch(console.error);
        }
    </script>
    <?php include("app/include/footer.php"); ?>
</body>

</html>