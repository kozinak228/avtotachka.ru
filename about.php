<?php
session_start();
include("app/database/db.php");
include "path.php";
?>
<!DOCTYPE html>
<html lang="ru" class="<?= ($_SESSION['theme'] ?? 'dark') === 'dark' ? 'dark' : '' ?>">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>О нас &mdash; AvtoTachka</title>

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


    <!-- Custom Global CSS -->
    <link rel="stylesheet" href="assets/css/style.css">
</head>

<body
    class="font-display bg-background-light dark:bg-background-dark text-slate-900 dark:text-slate-100 transition-colors duration-300 min-h-screen flex flex-col <?= ($_SESSION['theme'] ?? 'dark') === 'dark' ? 'dark-theme' : '' ?>">

    <!-- Header -->
    <?php include("app/include/header.php"); ?>

    <!-- Main Content -->
    <main class="flex-grow pt-24 pb-16">
        <!-- Hero Section -->
        <section class="relative py-20 lg:py-32 overflow-hidden">
            <div class="absolute inset-0 z-0">
                <img src="https://images.unsplash.com/photo-1619682817481-e994891cd1f5?q=80&w=2000&auto=format&fit=crop"
                    alt="Premium Dealership Background" class="w-full h-full object-cover">
                <div class="absolute inset-0 bg-slate-900/80 backdrop-blur-sm"></div>
            </div>

            <div class="container mx-auto px-4 relative z-10 text-center animate-fade-in-up">
                <h1 class="text-5xl md:text-7xl font-extrabold text-white mb-6 tracking-tight">
                    Открываем <span class="text-primary relative inline-block">Азию<span
                            class="absolute -bottom-2 left-0 w-full h-2 bg-primary/30 rounded-full blur-[2px]"></span></span>
                    для России
                </h1>
                <p class="text-xl md:text-2xl text-slate-300 max-w-3xl mx-auto leading-relaxed">
                    AvtoTachka — ваш надежный проводник в мир высокотехнологичных, комфортных и инновационных автомобилей
                    из Поднебесной. Мы делаем премиум доступным.
                </p>
            </div>

            <!-- Floating orbs behind hero text -->
            <div
                class="absolute top-1/2 left-1/4 w-64 h-64 bg-primary/40 rounded-full blur-[100px] -translate-y-1/2 -translate-x-1/2">
            </div>
            <div
                class="absolute top-1/2 right-1/4 w-64 h-64 bg-blue-500/30 rounded-full blur-[100px] -translate-y-1/2 translate-x-1/2">
            </div>
        </section>

        <!-- Stats Section -->
        <section class="container mx-auto px-4 py-16 -mt-16 relative z-20">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 animate-fade-in-up" style="animation-delay: 0.1s;">
                <div
                    class="bg-white/90 dark:bg-slate-800/90 backdrop-blur-md p-8 rounded-3xl border border-white/20 dark:border-slate-700/50 shadow-xl text-center transform hover:-translate-y-2 transition-transform duration-300">
                    <span class="material-icons-outlined text-5xl text-primary mb-4">emoji_events</span>
                    <h3 class="text-4xl font-extrabold text-slate-900 dark:text-white mb-2">5+ лет</h3>
                    <p class="text-slate-500 dark:text-slate-400 font-medium">Успешной работы на рынке прямых поставок.
                    </p>
                </div>
                <div
                    class="bg-white/90 dark:bg-slate-800/90 backdrop-blur-md p-8 rounded-3xl border border-white/20 dark:border-slate-700/50 shadow-xl text-center transform hover:-translate-y-2 transition-transform duration-300">
                    <span class="material-icons-outlined text-5xl text-primary mb-4">directions_car</span>
                    <h3 class="text-4xl font-extrabold text-slate-900 dark:text-white mb-2">1500+</h3>
                    <p class="text-slate-500 dark:text-slate-400 font-medium">Счастливых клиентов, получивших свои
                        автомобили.</p>
                </div>
                <div
                    class="bg-white/90 dark:bg-slate-800/90 backdrop-blur-md p-8 rounded-3xl border border-white/20 dark:border-slate-700/50 shadow-xl text-center transform hover:-translate-y-2 transition-transform duration-300">
                    <span class="material-icons-outlined text-5xl text-primary mb-4">handshake</span>
                    <h3 class="text-4xl font-extrabold text-slate-900 dark:text-white mb-2">12</h3>
                    <p class="text-slate-500 dark:text-slate-400 font-medium">Эксклюзивных контрактов с официальными
                        дилерами в Китае.</p>
                </div>
            </div>
        </section>

        <!-- Our Mission Section -->
        <section class="container mx-auto px-4 py-16">
            <div class="max-w-4xl mx-auto bg-white dark:bg-slate-800 rounded-3xl p-8 md:p-12 shadow-sm border border-slate-200 dark:border-slate-700 animate-fade-in-up"
                style="animation-delay: 0.2s;">
                <h2 class="text-3xl md:text-4xl font-bold text-slate-900 dark:text-white mb-8 text-center">Наша
                    философия</h2>

                <div class="space-y-6 text-lg text-slate-600 dark:text-slate-300 leading-relaxed">
                    <p>
                        Китайский автопром совершил квантовый скачок за последние десятилетия. Сегодня автомобили из КНР
                        — это не просто средство передвижения. Это симбиоз передовых технологий, бескомпромиссного
                        уровня безопасности и футуристического дизайна. Наша цель — сломать устаревшие стереотипы и
                        доказать, что премиальное качество может быть доступным каждому.
                    </p>
                    <p>
                        В <strong class="text-primary">AvtoTachka</strong> мы тщательно отбираем каждую модель, прежде
                        чем предложить её вам. Наши эксперты лично тестируют новинки, изучают техническую документацию и
                        анализируют отзывы реальных владельцев. Мы предлагаем только те автомобили, в качестве которых
                        уверены на 100%.
                    </p>
                    <p>
                        Мы верим, что процесс покупки автомобиля должен быть таким же комфортным и инновационным, как и
                        сам автомобиль. Поэтому мы обеспечиваем полное сопровождение сделки: от выбора комплектации и
                        оформления таможенных документов до вручения ключей.
                    </p>
                </div>
            </div>
        </section>

        <!-- Contact CTA -->
        <section class="container mx-auto px-4 py-16">
            <div class="max-w-4xl mx-auto text-center bg-gradient-to-br from-slate-900 to-slate-800 rounded-3xl p-12 shadow-2xl relative overflow-hidden animate-fade-in-up"
                style="animation-delay: 0.3s;">
                <div
                    class="absolute inset-0 opacity-20 bg-[url('https://www.transparenttextures.com/patterns/cubes.png')] mix-blend-overlay">
                </div>
                <div class="relative z-10">
                    <h2 class="text-3xl md:text-4xl font-bold text-white mb-6">Готовы шагнуть в будущее?</h2>
                    <p class="text-slate-300 mb-10 text-lg max-w-2xl mx-auto">
                        Оставьте заявку, и наши специалисты свяжутся с вами для индивидуальной консультации. Мы подберем
                        автомобиль, который идеально подойдет именно вам.
                    </p>
                    <a href="<?= BASE_URL ?>#catalog"
                        class="inline-flex items-center gap-2 bg-primary hover:bg-red-600 text-white font-bold text-lg py-4 px-10 rounded-full transition-all hover:scale-105 hover:shadow-[0_0_30px_rgba(225,29,72,0.4)]">
                        Смотреть каталог автомобилей
                        <span class="material-icons">arrow_forward</span>
                    </a>
                </div>
            </div>
        </section>
    </main>

    <!-- Footer -->
    <?php include("app/include/footer.php"); ?>

</body>

</html>