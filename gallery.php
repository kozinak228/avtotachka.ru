<?php
include "path.php";
include_once SITE_ROOT . "/app/database/db.php";

$allCars = selectAll('cars', ['status' => 1]);
$brands = selectAll('brands');
?>
<!doctype html>
<html lang="ru" class="<?= ($_SESSION['theme'] ?? 'dark') === 'dark' ? 'dark' : '' ?>">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Галерея — AvtoTachka</title>
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

    <style>
        .masonry-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            grid-auto-rows: 250px;
            gap: 20px;
        }

        .masonry-item-large {
            grid-row: span 2;
        }

        .masonry-item-wide {
            grid-column: span 2;
        }

        .gallery-image-wrapper:hover .overlay {
            opacity: 1;
            backdrop-filter: blur(4px);
        }

        .gallery-image-wrapper:hover img {
            transform: scale(1.05);
        }

        .lightbox-active {
            display: flex !important;
            animation: scaleIn 0.3s ease-out;
        }

        @keyframes scaleIn {
            from {
                transform: scale(0.9);
                opacity: 0;
            }

            to {
                transform: scale(1);
                opacity: 1;
            }
        }

        .stagger-load {
            animation: fadeInUp 0.6s ease forwards;
            opacity: 0;
        }

        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
    </style>
</head>

<body class="<?= ($_SESSION['theme'] ?? 'dark') === 'dark' ? 'dark-theme' : '' ?>">

    <?php include("app/include/header.php"); ?>

    <div class="max-w-7xl mx-auto px-4 pt-28 pb-8 mt-12 relative z-10">
        <h1 class="text-4xl md:text-5xl font-bold text-center mb-4 text-slate-900 dark:text-white">Галерея автомобилей
        </h1>
        <p class="text-slate-500 dark:text-slate-400 text-center max-w-2xl mx-auto">
            Исследуйте будущее китайского автопрома через наш интерактивный каталог. Каждый снимок передает совершенство
            дизайна и технологий.
        </p>
    </div>

    <main class="max-w-7xl mx-auto px-4 mb-24 min-h-[50vh]">
        <div class="masonry-grid">
            <?php
            $delay = 0.1;
            $index = 0;
            foreach ($allCars as $car):
                $images = selectCarImages($car['id']);

                // Главное фото
                if ($car['img']):
                    $class = '';
                    if ($index % 5 == 0)
                        $class = 'masonry-item-large';
                    elseif ($index % 7 == 0)
                        $class = 'masonry-item-wide';
                    $delayVal = $delay . 's';
                    ?>
                    <div class="<?= $class ?> group relative overflow-hidden rounded-3xl bg-slate-200 dark:bg-slate-800 stagger-load border border-slate-200 dark:border-white/10 shadow-sm"
                        style="animation-delay: <?= $delayVal ?>">
                        <a href="<?= BASE_URL ?>single.php?id=<?= $car['id'] ?>"
                            class="block gallery-image-wrapper h-full w-full relative overflow-hidden cursor-pointer">
                            <img alt="<?= htmlspecialchars($car['title']) ?>"
                                class="w-full h-full object-cover transition-transform duration-700 ease-out"
                                src="<?= BASE_URL . 'assets/images/cars/' . $car['img'] ?>" />
                            <div
                                class="overlay absolute inset-0 bg-gradient-to-t from-black/80 via-black/20 to-transparent opacity-0 transition-opacity duration-300 flex flex-col justify-end p-6">
                                <span
                                    class="text-primary text-xs font-bold tracking-widest uppercase mb-1"><?= htmlspecialchars($car['brand_name'] ?? 'Авто') ?></span>
                                <h3 class="text-white text-2xl font-bold"><?= htmlspecialchars($car['title']) ?></h3>
                            </div>
                        </a>
                    </div>
                    <?php
                    $delay += 0.1;
                    $index++;
                endif;

                // Доп. фото
                foreach ($images as $image):
                    $class = '';
                    if ($index % 5 == 0)
                        $class = 'masonry-item-large';
                    elseif ($index % 7 == 0)
                        $class = 'masonry-item-wide';
                    $delayVal = $delay . 's';
                    ?>
                    <div class="<?= $class ?> group relative overflow-hidden rounded-3xl bg-slate-200 dark:bg-slate-800 stagger-load border border-slate-200 dark:border-white/10 shadow-sm"
                        style="animation-delay: <?= $delayVal ?>">
                        <a href="<?= BASE_URL ?>single.php?id=<?= $car['id'] ?>"
                            class="block gallery-image-wrapper h-full w-full relative overflow-hidden cursor-pointer">
                            <img alt="<?= htmlspecialchars($car['title']) ?>"
                                class="w-full h-full object-cover transition-transform duration-700 ease-out"
                                src="<?= BASE_URL . 'assets/images/cars/' . $image['img'] ?>" />
                            <div
                                class="overlay absolute inset-0 bg-gradient-to-t from-black/80 via-black/20 to-transparent opacity-0 transition-opacity duration-300 flex flex-col justify-end p-6">
                                <span class="text-primary text-xs font-bold tracking-widest uppercase mb-1">Детали</span>
                                <h3 class="text-white text-2xl font-bold"><?= htmlspecialchars($car['title']) ?></h3>
                            </div>
                        </a>
                    </div>
                    <?php
                    $delay += 0.1;
                    $index++;
                endforeach;
            endforeach;
            ?>
        </div>

        <?php if (count($allCars) == 0): ?>
            <div class="col-12 text-center text-slate-500 py-12">
                <p>В галерее пока нет фотографий.</p>
            </div>
        <?php endif; ?>
    </main>

    <?php include("app/include/footer.php"); ?>


</body>

</html>