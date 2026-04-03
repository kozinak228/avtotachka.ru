<?php
include "path.php";
include_once SITE_ROOT . "/app/database/db.php";
include_once SITE_ROOT . "/app/helpers/track_visit.php";

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$car = selectCarById($id);
if (!$car) {
    header('location: ' . BASE_URL);
    exit;
}
$carImages = selectCarImages($id);
$brands = selectAll('brands');

$user_favorites = [];
if (isset($_SESSION['id'])) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT id_car FROM favorites WHERE id_user = ?");
    $stmt->execute([$_SESSION['id']]);
    $user_favorites = $stmt->fetchAll(PDO::FETCH_COLUMN);
}

// Подключаем контроллер комментариев для обработки POST
include_once SITE_ROOT . "/app/controllers/commentaries.php";
?>
<!doctype html>
<html lang="ru" class="<?= ($_SESSION['theme'] ?? 'dark') === 'dark' ? 'dark' : '' ?>">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= $car['title'] ?> &mdash; AvtoTachka</title>

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
    <!-- Swiper CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css" />
</head>

<body
    class="font-display bg-background-light dark:bg-slate-900 text-slate-900 dark:text-slate-100 transition-colors duration-300 <?= ($_SESSION['theme'] ?? 'dark') === 'dark' ? 'dark-theme' : '' ?>">

    <?php include("app/include/header.php"); ?>

    <main class="max-w-7xl mx-auto px-4 py-8 mt-20">
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">
            <div class="lg:col-span-8 space-y-8">
                <div class="flex justify-between items-end animate-fade-in-up">
                    <div class="space-y-2">
                        <h1 class="text-4xl font-extrabold tracking-tight text-slate-900 dark:text-white">
                            <?= htmlspecialchars($car['title']) ?>
                        </h1>
                        <p class="text-slate-500 dark:text-slate-400 flex items-center gap-2">
                            <span
                                class="px-3 py-1 bg-primary text-white text-xs font-bold rounded-full uppercase tracking-wider"><?= htmlspecialchars($car['brand_name']) ?></span>
                            <?php if (!empty($_SESSION['admin'])): ?>
                                Добавлено: <?= date('d.m.Y', strtotime($car['created_date'])) ?>
                            <?php endif; ?>
                        </p>
                    </div>
                    <div class="text-right">
                        <span class="text-3xl font-bold text-primary"><?= number_format($car['price'], 0, '', ' ') ?>
                            ₽</span>
                    </div>
                </div>

                <div class="relative group rounded-3xl overflow-hidden shadow-2xl glass dark:bg-slate-800/40 border border-slate-200 dark:border-white/10 animate-fade-in-up"
                    style="animation-delay: 0.1s;">

                    <!-- Swiper Main Container -->
                    <div class="swiper carSwiper w-full h-[400px] md:h-[500px]">
                        <div class="swiper-wrapper">
                            <!-- Main Image Slide -->
                            <div class="swiper-slide">
                                <?php if ($car['img']): ?>
                                    <img alt="<?= htmlspecialchars($car['title']) ?>" class="w-full h-full object-cover"
                                        src="<?= BASE_URL . 'assets/images/cars/' . $car['img'] ?>" />
                                <?php else: ?>
                                    <div
                                        class="w-full h-full bg-slate-200 dark:bg-slate-800 flex items-center justify-center">
                                        <i class="fas fa-car fa-10x text-slate-400"></i>
                                    </div>
                                <?php endif; ?>
                            </div>

                            <!-- Additional Gallery Images Slides -->
                            <?php if (!empty($carImages)): ?>
                                <?php foreach ($carImages as $image): ?>
                                    <div class="swiper-slide">
                                        <img src="<?= BASE_URL . 'assets/images/cars/' . $image['img'] ?>" alt="Фото авто"
                                            class="w-full h-full object-cover">
                                    </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>

                        <!-- Add Navigation -->
                        <div
                            class="swiper-button-next !text-white drop-shadow-md opacity-0 group-hover:opacity-100 transition-opacity">
                        </div>
                        <div
                            class="swiper-button-prev !text-white drop-shadow-md opacity-0 group-hover:opacity-100 transition-opacity">
                        </div>

                        <!-- Add Pagination -->
                        <div class="swiper-pagination"></div>
                    </div>

                    <div
                        class="absolute inset-0 bg-gradient-to-t from-black/60 via-transparent to-transparent pointer-events-none">
                    </div>

                    <?php if (isset($_SESSION['id'])): ?>
                        <button
                            class="fav-btn absolute top-6 right-6 p-4 bg-white/90 dark:bg-slate-800/90 rounded-full shadow-lg hover:bg-white/90 hover:scale-110 active:scale-95 transition-all outline-none border-none cursor-pointer flex items-center justify-center z-20 pointer-events-auto"
                            data-id="<?= $car['id'] ?>" title="В избранное">
                            <span
                                class="material-icons transition-colors <?= in_array($car['id'], $user_favorites) ? 'text-primary' : 'text-slate-400' ?>"><?= in_array($car['id'], $user_favorites) ? 'favorite' : 'favorite_border' ?></span>
                        </button>
                    <?php endif; ?>

                    <?php
                    $compareList = isset($_SESSION['compare']) ? $_SESSION['compare'] : [];
                    $inCompare = in_array($car['id'], $compareList);
                    $compareTopPos = isset($_SESSION['id']) ? 'top-24' : 'top-6';
                    ?>
                    <button
                        class="compare-btn absolute <?= $compareTopPos ?> right-6 p-4 <?= $inCompare ? 'bg-primary text-white' : 'bg-white/90 dark:bg-slate-800/90 text-slate-400' ?> rounded-full shadow-lg hover:bg-primary hover:text-white hover:scale-110 active:scale-95 transition-all outline-none border-none cursor-pointer flex items-center justify-center z-10"
                        data-id="<?= $car['id'] ?>" title="К сравнению">
                        <span class="material-icons">balance</span>
                    </button>
                </div>

                <!-- Галерея была удалена, так как фото теперь в слайдере -->

                <section class="space-y-6 pt-4 animate-fade-in-up" style="animation-delay: 0.2s;">
                    <h2 class="text-2xl font-bold flex items-center gap-2 text-slate-900 dark:text-white">
                        <span class="w-8 h-1 bg-primary rounded-full"></span>
                        Характеристики
                    </h2>
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                        <div
                            class="spec-card flex items-center justify-between p-4 glass dark:!bg-slate-800/40 border border-white/40 dark:!border-white/5 rounded-2xl transition-all shadow-sm hover:translate-y-[-2px]">
                            <div class="flex items-center gap-3">
                                <span class="material-icons text-slate-400">calendar_today</span>
                                <span class="text-xs font-medium text-slate-500 dark:text-slate-400">Год выпуска</span>
                            </div>
                            <span class="font-bold text-slate-900 dark:text-white"><?= $car['year'] ?></span>
                        </div>
                        <div
                            class="spec-card flex items-center justify-between p-4 glass dark:!bg-slate-800/40 border border-white/40 dark:!border-white/5 rounded-2xl transition-all shadow-sm hover:translate-y-[-2px]">
                            <div class="flex items-center gap-3">
                                <span class="material-icons text-slate-400">speed</span>
                                <span class="text-xs font-medium text-slate-500 dark:text-slate-400">Пробег</span>
                            </div>
                            <span
                                class="font-bold text-slate-900 dark:text-white"><?= number_format($car['mileage'], 0, '', ' ') ?>
                                км</span>
                        </div>
                        <div
                            class="spec-card flex items-center justify-between p-4 glass dark:!bg-slate-800/40 border border-white/40 dark:!border-white/5 rounded-2xl transition-all shadow-sm hover:translate-y-[-2px]">
                            <div class="flex items-center gap-3">
                                <span class="material-icons text-slate-400">settings</span>
                                <span class="text-xs font-medium text-slate-500 dark:text-slate-400">Мощность</span>
                            </div>
                            <span class="font-bold text-slate-900 dark:text-white"><?= $car['horsepower'] ?> л.с.</span>
                        </div>
                        <div
                            class="spec-card flex items-center justify-between p-4 glass dark:!bg-slate-800/40 border border-white/40 dark:!border-white/5 rounded-2xl transition-all shadow-sm hover:translate-y-[-2px]">
                            <div class="flex items-center gap-3">
                                <span class="material-icons text-slate-400">local_gas_station</span>
                                <span class="text-xs font-medium text-slate-500 dark:text-slate-400">Двигатель</span>
                            </div>
                            <span class="font-bold text-slate-900 dark:text-white"><?= $car['engine_type'] ?></span>
                        </div>
                        <div
                            class="spec-card flex items-center justify-between p-4 glass dark:!bg-slate-800/40 border border-white/40 dark:!border-white/5 rounded-2xl transition-all shadow-sm hover:translate-y-[-2px]">
                            <div class="flex items-center gap-3">
                                <span class="material-icons text-slate-400">directions_car</span>
                                <span class="text-xs font-medium text-slate-500 dark:text-slate-400">Кузов</span>
                            </div>
                            <span class="font-bold text-slate-900 dark:text-white"><?= $car['body_type'] ?></span>
                        </div>
                        <div
                            class="spec-card flex items-center justify-between p-4 glass dark:!bg-slate-800/40 border border-white/40 dark:!border-white/5 rounded-2xl transition-all shadow-sm hover:translate-y-[-2px]">
                            <div class="flex items-center gap-3">
                                <span class="material-icons text-slate-400">swap_calls</span>
                                <span class="text-xs font-medium text-slate-500 dark:text-slate-400">КПП</span>
                            </div>
                            <span class="font-bold text-slate-900 dark:text-white"><?= $car['transmission'] ?></span>
                        </div>
                        <div
                            class="spec-card flex items-center justify-between p-4 glass dark:!bg-slate-800/40 border border-white/40 dark:!border-white/5 rounded-2xl transition-all shadow-sm hover:translate-y-[-2px]">
                            <div class="flex items-center gap-3">
                                <span class="material-icons text-slate-400">tire_repair</span>
                                <span class="text-xs font-medium text-slate-500 dark:text-slate-400">Привод</span>
                            </div>
                            <span class="font-bold text-slate-900 dark:text-white"><?= $car['drive_type'] ?></span>
                        </div>
                        <div
                            class="spec-card flex items-center justify-between p-4 glass dark:!bg-slate-800/40 border border-white/40 dark:!border-white/5 rounded-2xl transition-all shadow-sm hover:translate-y-[-2px]">
                            <div class="flex items-center gap-3">
                                <span class="material-icons text-slate-400">water_drop</span>
                                <span class="text-xs font-medium text-slate-500 dark:text-slate-400">Объем</span>
                            </div>
                            <span class="font-bold text-slate-900 dark:text-white"><?= $car['engine_volume'] ?> л</span>
                        </div>
                        <div
                            class="spec-card flex items-center justify-between p-4 glass dark:!bg-slate-800/40 border border-white/40 dark:!border-white/5 rounded-2xl transition-all shadow-sm hover:translate-y-[-2px]">
                            <div class="flex items-center gap-3">
                                <span class="material-icons text-slate-400">palette</span>
                                <span class="text-xs font-medium text-slate-500 dark:text-slate-400">Цвет</span>
                            </div>
                            <span
                                class="font-bold text-slate-900 dark:text-white"><?= htmlspecialchars($car['color']) ?></span>
                        </div>
                    </div>
                </section>

                <?php if (!empty($car['description'])): ?>
                    <section class="space-y-4 pt-4 animate-fade-in-up" style="animation-delay: 0.3s;">
                        <h2 class="text-2xl font-bold flex items-center gap-2 text-slate-900 dark:text-white">
                            <span class="w-8 h-1 bg-primary rounded-full"></span> Описание
                        </h2>
                        <div class="glass p-8 rounded-[2rem] shadow-sm animate-fade-in-up prose prose-slate dark:prose-invert max-w-none break-all"
                            style="animation-delay: 0.4s;">
                            <?= $car['description'] ?>
                        </div>
                    </section>
                <?php endif; ?>

                <div class="pt-6 relative z-0">
                    <?php
                    $page = $car['id'];
                    $_GET['post'] = $car['id'];
                    include("app/include/comments.php");
                    ?>
                </div>

            </div>

            <!-- Сайдбар -->
            <aside class="lg:col-span-4 space-y-8 mt-12 lg:mt-0">
                <div
                    class="bg-[#fff] dark:bg-slate-800 p-6 rounded-3xl border border-slate-200 dark:border-slate-700 shadow-sm">
                    <div class="flex justify-between items-center mb-6">
                        <h3 class="text-lg font-bold text-slate-900 dark:text-white">Популярные бренды</h3>
                    </div>
                    <ul class="space-y-1 m-0 p-0 list-none">
                        <?php foreach (array_slice($brands, 0, 6) as $b): ?>
                            <li><a class="flex items-center justify-between p-3 rounded-xl hover:bg-slate-50 dark:hover:bg-slate-700 transition-all font-medium text-slate-700 dark:text-slate-300 hover:text-primary dark:hover:text-primary"
                                    style="text-decoration:none;"
                                    href="<?= BASE_URL . 'index.php?brand=' . $b['id'] ?>"><?= $b['name'] ?> <span
                                        class="material-icons text-slate-300 text-sm">chevron_right</span></a></li>
                        <?php endforeach; ?>
                        <li><a href="<?= BASE_URL ?>"
                                class="block text-center mt-3 text-sm text-primary font-bold hover:underline"
                                style="text-decoration:none;">Все бренды</a></li>
                    </ul>
                </div>

                <div
                    class="glass dark:bg-slate-800/40 p-6 rounded-3xl border border-slate-200 dark:border-white/10 shadow-lg">
                    <h3 class="text-lg font-bold mb-4 flex items-center text-slate-900 dark:text-white">
                        <span class="material-icons text-primary mr-2">contact_support</span> Связаться с нами
                    </h3>
                    <p class="text-slate-500 dark:text-slate-400 text-sm mb-6">Заинтересовал этот автомобиль? Свяжитесь с нами удобным способом:</p>
                    <a href="tel:+79771310000"
                        class="magnetic-btn w-full bg-primary hover:bg-red-600 text-white py-4 rounded-xl font-bold flex items-center justify-center gap-2 transition-all border-none cursor-pointer shadow-[0_0_20px_rgba(225,29,72,0.3)] hover:shadow-[0_0_30px_rgba(225,29,72,0.5)]"
                        style="text-decoration:none;">
                        <span class="material-icons">phone_in_talk</span> Позвонить
                    </a>
                    <a href="https://max.ru/call/+79771310000" target="_blank"
                        class="magnetic-btn mt-3 w-full text-white py-4 rounded-xl font-bold flex items-center justify-center gap-2 transition-all text-center"
                        style="text-decoration:none; background: linear-gradient(135deg, #3B82F6, #8B5CF6, #EC4899);">
                        <img src="<?= BASE_URL ?>assets/images/max_icon.svg" alt="MAX" width="24" height="24" style="border-radius:5px;"> Написать в MAX
                    </a>
                    <a href="https://t.me/+79771310000" target="_blank"
                        class="magnetic-btn mt-3 w-full bg-[#2AABEE] text-white py-4 rounded-xl font-bold flex items-center justify-center gap-2 transition-all text-center"
                        style="text-decoration:none;">
                        <i class="fab fa-telegram text-lg"></i> Написать в Telegram
                    </a>
                    <a href="https://wa.me/79771310000" target="_blank"
                        class="magnetic-btn mt-3 w-full bg-[#25D366] text-white py-4 rounded-xl font-bold flex items-center justify-center gap-2 transition-all text-center"
                        style="text-decoration:none;">
                        <i class="fab fa-whatsapp text-lg"></i> Написать в WhatsApp
                    </a>
                </div>
            </aside>
        </div>
    </main>

    <?php include("app/include/footer.php"); ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0-beta2/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-b5kHyXgcpbZJO/tY9Ul7kGkf1S0CWuKcCD38l8YkeH8z8QjE0GmW1gYU5S9FOnJ0"
        crossorigin="anonymous"></script>
    <!-- Swiper JS -->
    <script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            // Initialize Swiper
            const swiper = new Swiper(".carSwiper", {
                navigation: {
                    nextEl: ".swiper-button-next",
                    prevEl: ".swiper-button-prev",
                },
                pagination: {
                    el: ".swiper-pagination",
                    clickable: true,
                },
                keyboard: {
                    enabled: true,
                },
                loop: true,
            });

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

                                const iconPos = this.querySelector('.material-icons');
                                if (data.is_added) {
                                    this.classList.remove('bg-white/90', 'dark:bg-slate-800/90', 'text-slate-400');
                                    this.classList.add('bg-primary', 'text-white');
                                } else {
                                    this.classList.add('bg-white/90', 'dark:bg-slate-800/90', 'text-slate-400');
                                    this.classList.remove('bg-primary', 'text-white');
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
</body>

</html>