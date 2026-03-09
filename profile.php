<?php
include "path.php";
include_once SITE_ROOT . "/app/database/db.php";
include_once SITE_ROOT . "/app/helps/csrf_helper.php";

// Требуем авторизации
if (!isset($_SESSION['id'])) {
    header('location: ' . BASE_URL . 'log.php');
    exit;
}

$errMsg = [];
$successMsg = '';

$currentUser = selectOne('users', ['id' => $_SESSION['id']]);

// Переключение темы
if (isset($_POST['toggle_theme'])) {
    $newTheme = ($_SESSION['theme'] ?? 'dark') === 'dark' ? 'light' : 'dark';
    $_SESSION['theme'] = $newTheme;
    header('location: ' . BASE_URL . 'profile.php');
    exit;
}

// Обновление профиля
if (isset($_POST['update_profile'])) {
    if (!validateCsrfToken($_POST)) {
        array_push($errMsg, "Ошибка CSRF токена. Попробуйте снова.");
    } else {
        $login = trim($_POST['login']);
        $email = trim($_POST['email']);
        $bio = trim($_POST['bio'] ?? '');

        if ($login === '' || $email === '') {
            array_push($errMsg, "Логин и Email не могут быть пустыми.");
        } else {
            $existEmail = selectOne('users', ['email' => $email]);
            $existLogin = selectOne('users', ['username' => $login]);

            if ($existEmail && $existEmail['id'] !== $_SESSION['id']) {
                array_push($errMsg, "Этот Email уже занят другим пользователем.");
            } elseif ($existLogin && $existLogin['id'] !== $_SESSION['id']) {
                array_push($errMsg, "Этот Логин уже занят.");
            } else {

                $updateData = [
                    'username' => $login,
                    'email' => $email,
                    'bio' => $bio
                ];

                // Проверка аватарки
                if (!empty($_POST['cropped_image'])) {
                    $base64_string = $_POST['cropped_image'];
                    $img_parts = explode(";base64,", $base64_string);
                    if (count($img_parts) == 2) {
                        $img_base64 = base64_decode($img_parts[1]);

                        $avatarName = time() . "_" . $_SESSION['id'] . ".webp";
                        $imgPath = ROOT_PATH . "/assets/images/avatars/" . $avatarName;

                        // Создаем папку если нет
                        if (!file_exists(ROOT_PATH . "/assets/images/avatars")) {
                            mkdir(ROOT_PATH . "/assets/images/avatars", 0777, true);
                        }

                        file_put_contents($imgPath, $img_base64);
                        $updateData['avatar'] = $avatarName;

                        // Удаление старой автарки
                        if (!empty($currentUser['avatar']) && file_exists(ROOT_PATH . "/assets/images/avatars/" . $currentUser['avatar'])) {
                            unlink(ROOT_PATH . "/assets/images/avatars/" . $currentUser['avatar']);
                        }
                    }
                }

                update('users', $_SESSION['id'], $updateData);
                $_SESSION['login'] = $login;
                $_SESSION['email'] = $email;
                $successMsg = "Данные профиля успешно обновлены!";
                // Обновляем текущего юзера после апдейта
                $currentUser = selectOne('users', ['id' => $_SESSION['id']]);
            }
        }
    }
}

// Смена пароля
if (isset($_POST['change_password'])) {
    if (!validateCsrfToken($_POST)) {
        array_push($errMsg, "Ошибка CSRF токена. Попробуйте снова.");
    } else {
        $oldPass = $_POST['old_password'];
        $newPass = $_POST['new_password'];
        $newPass2 = $_POST['new_password_2'];

        if (!password_verify($oldPass, $currentUser['password'])) {
            array_push($errMsg, "Текущий пароль введен неверно.");
        } elseif ($newPass !== $newPass2) {
            array_push($errMsg, "Новые пароли не совпадают.");
        } elseif (mb_strlen($newPass, 'UTF8') < 4) {
            array_push($errMsg, "Новый пароль должен содержать минимум 4 символа.");
        } else {
            $hashed = password_hash($newPass, PASSWORD_DEFAULT);
            update('users', $_SESSION['id'], ['password' => $hashed]);
            $successMsg = "Пароль успешно изменен!";
            $currentUser = selectOne('users', ['id' => $_SESSION['id']]);
        }
    }
}

// Получение избранных машин
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
$user_favorites_cars = $stmt_fav->fetchAll();

$userAvatar = !empty($currentUser['avatar']) ? BASE_URL . "assets/images/avatars/" . $currentUser['avatar'] : '';
?>
<!doctype html>
<html lang="ru" class="<?= ($_SESSION['theme'] ?? 'dark') === 'dark' ? 'dark' : '' ?>">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Личный кабинет &mdash; AvtoTachka</title>

    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com?plugins=forms,typography"></script>
    <script>
        tailwind.config = {
            darkMode: "class",
            theme: {
                extend: {
                    colors: {
                        primary: "#e11d48",
                        "background-light": "#f8f6f6",
                        "background-dark": "#0a0607",
                    },
                    fontFamily: {
                        display: ["Space Grotesk", "sans-serif"],
                        sans: ["Space Grotesk", "sans-serif"],
                    }
                }
            }
        };
    </script>

    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet" />
    <link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@300;400;500;600;700&display=swap"
        rel="stylesheet" />

    <!-- Bootstrap and Cropper -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0-beta2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.13/cropper.min.css">

    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.7.2/css/all.css">
    <link rel="stylesheet" href="assets/css/style.css">

    <style>
        body {
            font-family: 'Space Grotesk', sans-serif;
        }

        .glass-panel {
            background: rgba(23, 17, 19, 0.7);
            backdrop-filter: blur(16px);
            border: 1px solid rgba(226, 29, 72, 0.1);
        }

        .fluid-bg {
            background: radial-gradient(circle at 0% 0%, rgba(226, 29, 72, 0.15) 0%, transparent 50%),
                radial-gradient(circle at 100% 100%, rgba(226, 29, 72, 0.1) 0%, transparent 50%);
        }

        .card-stack:hover .card-1 {
            transform: rotate(-6deg) translateX(-60px) translateY(-10px);
        }

        .card-stack:hover .card-2 {
            transform: rotate(0deg) translateY(-20px);
        }

        .card-stack:hover .card-3 {
            transform: rotate(6deg) translateX(60px) translateY(-10px);
        }

        .glow-red {
            box-shadow: 0 0 15px rgba(226, 29, 72, 0.4);
        }

        .pulse-red {
            animation: pulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;
        }

        @keyframes pulse {

            0%,
            100% {
                opacity: 1;
                transform: scale(1);
            }

            50% {
                opacity: .8;
                transform: scale(1.02);
                box-shadow: 0 0 25px rgba(226, 29, 72, 0.6);
            }
        }

        .avatar-hover-icon {
            opacity: 0;
            transition: opacity 0.3s ease;
        }

        .avatar-wrapper:hover .avatar-hover-icon {
            opacity: 1;
        }

        .form-control {
            background-color: rgba(255, 255, 255, 0.05) !important;
            border: 1px solid rgba(255, 255, 255, 0.1) !important;
            color: inherit !important;
        }

        .form-control:focus {
            border-color: #e11d48 !important;
            box-shadow: 0 0 0 0.25rem rgba(225, 29, 72, 0.25) !important;
        }
    </style>
</head>

<body
    class="bg-background-light dark:bg-background-dark text-slate-900 dark:text-slate-100 min-h-screen font-display <?= ($_SESSION['theme'] ?? 'dark') === 'dark' ? 'dark-theme' : '' ?>">

    <?php include("app/include/header.php"); ?>

    <!-- Fluid Background -->
    <div class="fixed inset-0 pointer-events-none fluid-bg z-0 mt-20"></div>

    <main class="relative z-10 p-4 md:p-8 max-w-7xl mx-auto space-y-12 mt-20 mb-20">

        <?php if (!empty($errMsg)): ?>
            <div class="alert alert-danger mb-4 shadow-lg border-primary text-primary glass-panel">
                <ul class="mb-0">
                    <?php foreach ($errMsg as $err): ?>
                        <li>
                            <?= $err ?>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <?php if ($successMsg): ?>
            <div class="alert alert-success mb-4 shadow-lg border-green-500 text-green-400 glass-panel"
                style="border-color: #22c55e;">
                <?= $successMsg ?>
            </div>
        <?php endif; ?>

        <!-- Profile Head -->
        <section class="flex flex-col md:flex-row items-center justify-between gap-8 glass-panel p-8 rounded-3xl">
            <div class="flex items-center gap-8">
                <div class="relative avatar-wrapper cursor-pointer"
                    onclick="document.getElementById('avatarInput').click();">
                    <div
                        class="w-32 h-32 rounded-full p-1 border-2 border-primary glow-red relative overflow-hidden group">
                        <?php if ($userAvatar): ?>
                            <img src="<?= $userAvatar ?>" id="avatarPreview"
                                class="w-full h-full rounded-full object-cover border-4 border-background-dark"
                                alt="Avatar">
                        <?php else: ?>
                            <div
                                class="w-full h-full rounded-full bg-slate-800 flex items-center justify-center border-4 border-background-dark">
                                <i class="fas fa-user-circle fa-4x text-slate-500" id="avatarIcon"></i>
                                <img src="" id="avatarPreview" class="w-full h-full rounded-full object-cover"
                                    style="display:none;" alt="Avatar">
                            </div>
                        <?php endif; ?>

                        <div
                            class="absolute inset-0 bg-black/60 avatar-hover-icon flex items-center justify-center rounded-full m-1">
                            <i class="fas fa-camera text-2xl text-white"></i>
                        </div>
                    </div>
                    <?php if ($currentUser['admin']): ?>
                        <div
                            class="absolute -bottom-2 -right-2 bg-primary text-[10px] font-black px-2 py-1 rounded-md text-white shadow-xl uppercase tracking-widest">
                            Admin
                        </div>
                    <?php else: ?>
                        <div
                            class="absolute -bottom-2 -right-2 bg-slate-700 text-[10px] font-black px-2 py-1 rounded-md text-white shadow-xl uppercase tracking-widest">
                            Member
                        </div>
                    <?php endif; ?>
                </div>

                <div>
                    <h1 class="text-4xl font-bold text-slate-900 dark:text-slate-100 tracking-tight">
                        <?= htmlspecialchars($currentUser['username']) ?>
                    </h1>
                    <p class="text-slate-500 dark:text-slate-400 text-lg mt-1">
                        <?= htmlspecialchars($currentUser['email']) ?>
                    </p>
                    <div class="flex gap-3 mt-4">
                        <form method="post" action="profile.php" class="m-0">
                            <button type="submit" name="toggle_theme"
                                class="px-4 py-2 bg-white/5 hover:bg-white/10 rounded-lg text-sm font-medium border border-slate-300 dark:border-white/10 transition-colors flex items-center gap-2">
                                <i
                                    class="fas <?= ($_SESSION['theme'] ?? 'dark') === 'dark' ? 'fa-sun' : 'fa-moon' ?>"></i>
                                <?= ($_SESSION['theme'] ?? 'dark') === 'dark' ? 'Светлая тема' : 'Темная тема' ?>
                            </button>
                        </form>
                        <?php if ($currentUser['admin']): ?>
                            <a href="<?= BASE_URL ?>admin/cars/index.php"
                                class="px-4 py-2 bg-primary/20 hover:bg-primary/30 text-primary rounded-lg text-sm font-bold border border-primary/30 transition-colors"
                                style="text-decoration:none;">
                                Админ панель
                            </a>
                        <?php endif; ?>
                        <a href="<?= BASE_URL ?>logout.php"
                            class="px-4 py-2 bg-red-500/10 hover:bg-red-500/20 text-red-500 rounded-lg text-sm font-medium border border-red-500/20 transition-colors"
                            style="text-decoration:none;">
                            Выйти
                        </a>
                    </div>
                </div>
            </div>

            <!-- Stats -->
            <div class="flex gap-4 w-full md:w-auto overflow-x-auto pb-2">
                <div
                    class="bg-white/5 dark:bg-black/20 p-6 rounded-xl min-w-[160px] flex flex-col justify-center border border-slate-200 dark:border-white/5 shadow-sm">
                    <span class="text-slate-500 dark:text-slate-400 text-sm font-medium mb-1">В избранном</span>
                    <span class="text-3xl font-bold text-slate-900 dark:text-white leading-none">
                        <?= count($user_favorites_cars) ?>
                    </span>
                    <span class="text-primary text-xs font-bold mt-2 flex items-center gap-1">
                        Автомобилей
                    </span>
                </div>
                <div
                    class="bg-white/5 dark:bg-black/20 p-6 rounded-xl min-w-[160px] flex flex-col justify-center border border-slate-200 dark:border-white/5 shadow-sm">
                    <span class="text-slate-500 dark:text-slate-400 text-sm font-medium mb-1">Статус</span>
                    <span class="text-xl font-bold text-slate-900 dark:text-white leading-tight mt-1">
                        <?= $currentUser['admin'] ? 'Администратор' : 'Пользователь' ?>
                    </span>
                    <span class="text-primary text-xs font-bold mt-2 flex items-center gap-1 animate-pulse">
                        <span class="material-icons text-xs" style="font-size: 14px;">verified</span> Активный
                    </span>
                </div>
            </div>
        </section>

        <section class="grid grid-cols-1 lg:grid-cols-2 gap-12 pt-4">

            <!-- Left Col: Forms -->
            <div class="space-y-8">

                <!-- Edit Profile Info -->
                <div class="glass-panel p-8 rounded-3xl">
                    <h2 class="text-2xl font-bold mb-6 flex items-center gap-2">
                        <span class="material-icons text-primary">edit</span> Настройки профиля
                    </h2>
                    <form action="profile.php" method="post" id="profileForm">
                        <?= csrfField() ?>
                        <input type="file" id="avatarInput" accept="image/*" style="display:none;">
                        <input type="hidden" name="cropped_image" id="cropped_image">

                        <div class="space-y-4">
                            <div>
                                <label
                                    class="text-sm font-medium text-slate-500 dark:text-slate-400 mb-1 block">Логин</label>
                                <input type="text" name="login" class="form-control"
                                    value="<?= htmlspecialchars($currentUser['username']) ?>" required>
                            </div>
                            <div>
                                <label
                                    class="text-sm font-medium text-slate-500 dark:text-slate-400 mb-1 block">Email</label>
                                <input type="email" name="email" class="form-control"
                                    value="<?= htmlspecialchars($currentUser['email']) ?>" required>
                            </div>
                            <div>
                                <label class="text-sm font-medium text-slate-500 dark:text-slate-400 mb-1 block">О
                                    себе</label>
                                <textarea name="bio" class="form-control"
                                    rows="3"><?= htmlspecialchars($currentUser['bio'] ?? '') ?></textarea>
                            </div>
                            <button type="submit" name="update_profile"
                                class="w-full py-3 bg-primary text-white font-bold rounded-xl shadow-lg shadow-primary/20 glow-red transition-all mt-2">
                                Сохранить изменения
                            </button>
                        </div>
                    </form>
                </div>

                <!-- Edit Password -->
                <div class="glass-panel p-8 rounded-3xl">
                    <h2 class="text-2xl font-bold mb-6 flex items-center gap-2">
                        <span class="material-icons text-slate-400">shield</span> Безопасность
                    </h2>
                    <form action="profile.php" method="post">
                        <?= csrfField() ?>
                        <div class="space-y-4">
                            <div>
                                <label class="text-sm font-medium text-slate-500 dark:text-slate-400 mb-1 block">Текущий
                                    пароль</label>
                                <input type="password" name="old_password" class="form-control" required>
                            </div>
                            <div>
                                <label class="text-sm font-medium text-slate-500 dark:text-slate-400 mb-1 block">Новый
                                    пароль</label>
                                <input type="password" name="new_password" class="form-control" required>
                            </div>
                            <div>
                                <label
                                    class="text-sm font-medium text-slate-500 dark:text-slate-400 mb-1 block">Повторите
                                    пароль</label>
                                <input type="password" name="new_password_2" class="form-control" required>
                            </div>
                            <button type="submit" name="change_password"
                                class="w-full py-3 bg-slate-800 dark:bg-white/10 text-white font-bold rounded-xl hover:bg-slate-700 dark:hover:bg-white/20 transition-all mt-2 border border-slate-700 dark:border-white/10">
                                Обновить пароль
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Right Col: Favorites 3D Stack -->
            <div class="space-y-6">
                <div class="flex items-center justify-between">
                    <h2 class="text-3xl font-bold tracking-tight">Ваше Избранное</h2>
                    <a href="<?= BASE_URL ?>favorites.php" class="text-primary text-sm font-bold flex items-center gap-1 hover:underline"
                        style="text-decoration:none;">Каталог <span class="material-icons text-sm">arrow_forward</span></a>
                </div>
                <p class="text-slate-500 dark:text-slate-400 text-lg leading-relaxed mb-8">
                    Персональная подборка лучших автомобилей из Китая.
                </p>

                <?php if (count($user_favorites_cars) > 0): ?>
                    <!-- Stacked Cards for Desktop (first 3 cars) -->
                    <div
                        class="relative h-[450px] w-full flex items-center justify-center card-stack group cursor-pointer mb-8 hidden md:flex" onclick="window.location.href='<?= BASE_URL ?>favorites.php'">
                        <?php
                        // Reverse slice to put the 1st one on top (z-index wise)
                        $stackCars = array_slice($user_favorites_cars, 0, 3);
                        $count = count($stackCars);
                        ?>

                        <?php if ($count >= 3):
                            $car = $stackCars[2]; ?>
                            <!-- Card 3 (Bottom) -->
                            <div
                                class="absolute w-[340px] h-[400px] rounded-2xl glass-panel shadow-2xl transition-all duration-700 ease-out border-white/20 card-3 rotate-[2deg] translate-x-4 overflow-hidden">
                                <div class="h-3/5 w-full bg-cover bg-center rounded-t-2xl"
                                    style="background-image: url('<?= BASE_URL ?>assets/images/cars/<?= $car['img'] ?>')"></div>
                                <div class="p-6">
                                    <div class="flex justify-between items-center mb-2">
                                        <span class="text-xs text-primary font-black uppercase tracking-widest">
                                            <?= htmlspecialchars($car['brand_name']) ?>
                                        </span>
                                        <span class="text-sm font-bold text-slate-900 dark:text-slate-100">
                                            <?= number_format($car['price'], 0, '', ' ') ?> ₽
                                        </span>
                                    </div>
                                    <h3 class="text-xl font-bold text-slate-900 dark:text-white">
                                        <?= htmlspecialchars($car['title']) ?>
                                    </h3>
                                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-2 line-clamp-2">
                                        <?= strip_tags($car['description'] ?? 'Премиальный автомобиль.') ?>
                                    </p>
                                </div>
                                <a href="<?= BASE_URL ?>single.php?id=<?= $car['id'] ?>" class="absolute inset-0 z-10"></a>
                            </div>
                        <?php endif; ?>

                        <?php if ($count >= 2):
                            $car = $stackCars[1]; ?>
                            <!-- Card 2 (Middle) -->
                            <div
                                class="absolute w-[340px] h-[400px] rounded-2xl glass-panel shadow-2xl transition-all duration-700 ease-out border-white/20 card-2 -rotate-[1deg] overflow-hidden">
                                <div class="h-3/5 w-full bg-cover bg-center rounded-t-2xl"
                                    style="background-image: url('<?= BASE_URL ?>assets/images/cars/<?= $car['img'] ?>')"></div>
                                <div class="p-6">
                                    <div class="flex justify-between items-center mb-2">
                                        <span class="text-xs text-primary font-black uppercase tracking-widest">
                                            <?= htmlspecialchars($car['brand_name']) ?>
                                        </span>
                                        <span class="text-sm font-bold text-slate-900 dark:text-slate-100">
                                            <?= number_format($car['price'], 0, '', ' ') ?> ₽
                                        </span>
                                    </div>
                                    <h3 class="text-xl font-bold text-slate-900 dark:text-white">
                                        <?= htmlspecialchars($car['title']) ?>
                                    </h3>
                                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-2 line-clamp-2">
                                        <?= strip_tags($car['description'] ?? 'Премиальный автомобиль.') ?>
                                    </p>
                                </div>
                                <a href="<?= BASE_URL ?>single.php?id=<?= $car['id'] ?>" class="absolute inset-0 z-20"></a>
                            </div>
                        <?php endif; ?>

                        <?php if ($count >= 1):
                            $car = $stackCars[0]; ?>
                            <!-- Card 1 (Top) -->
                            <div
                                class="absolute w-[340px] h-[400px] rounded-2xl glass-panel shadow-2xl transition-all duration-700 ease-out border-primary/40 card-1 -rotate-[4deg] -translate-x-4 z-30 overflow-hidden">
                                <div class="h-3/5 w-full bg-cover bg-center rounded-t-2xl"
                                    style="background-image: url('<?= BASE_URL ?>assets/images/cars/<?= $car['img'] ?>')"></div>
                                <div class="p-6">
                                    <div class="flex justify-between items-center mb-2">
                                        <span class="text-xs text-primary font-black uppercase tracking-widest">
                                            <?= htmlspecialchars($car['brand_name']) ?>
                                        </span>
                                        <span class="text-sm font-bold text-slate-900 dark:text-slate-100">
                                            <?= number_format($car['price'], 0, '', ' ') ?> ₽
                                        </span>
                                    </div>
                                    <h3 class="text-xl font-bold text-slate-900 dark:text-white">
                                        <?= htmlspecialchars($car['title']) ?>
                                    </h3>
                                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-2 line-clamp-2">
                                        <?= strip_tags($car['description'] ?? 'Премиальный автомобиль.') ?>
                                    </p>
                                </div>
                                <a href="<?= BASE_URL ?>single.php?id=<?= $car['id'] ?>" class="absolute inset-0 z-30"></a>
                            </div>
                        <?php endif; ?>
                    </div>

                    <!-- Button to view all saved cars -->
                    <div class="text-center">
                        <a href="<?= BASE_URL ?>favorites.php" class="magnetic-btn inline-flex items-center justify-center gap-2 w-full md:w-auto py-3 px-8 bg-primary hover:bg-primary/90 text-white font-bold rounded-xl shadow-lg shadow-primary/20 transition-all border-none" style="text-decoration:none;">
                            Все автомобили в каталоге (<?= count($user_favorites_cars) ?>)
                        </a>
                    </div>
                <?php else: ?>
                    <div
                        class="glass-panel p-12 rounded-3xl text-center border-dashed border-2 border-slate-300 dark:border-white/10">
                        <span
                            class="material-icons text-6xl text-slate-300 dark:text-slate-700 mb-4 block">heart_broken</span>
                        <h3 class="text-xl font-bold text-slate-900 dark:text-white mb-2">Пусто</h3>
                        <p class="text-slate-500 dark:text-slate-400 mb-6">Вы пока ничего не добавили в избранное.</p>
                        <a href="<?= BASE_URL ?>"
                            class="inline-block py-3 px-8 bg-primary text-white font-bold rounded-xl shadow-lg shadow-primary/20 hover:scale-105 transition-all text-decoration-none">
                            Искать автомобили
                        </a>
                    </div>
                <?php endif; ?>
            </div>

        </section>

    </main>

    <!-- Modal for Cropper -->
    <div class="modal fade" id="cropModal" data-bs-backdrop="static" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content glass-panel border-white/10" style="background-color: var(--card-bg);">
                <div class="modal-header border-white/10">
                    <h5 class="modal-title font-bold text-slate-900 dark:text-white" id="cropModalLabel">Обрезка аватара
                    </h5>
                    <button type="button"
                        class="btn-close <?php echo ($_SESSION['theme'] ?? 'dark') === 'dark' ? 'btn-close-white' : '' ?>"
                        data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-0">
                    <div class="img-container"
                        style="max-height: 500px; width: 100%; display:flex; justify-content:center; background-color: #000;">
                        <img id="imageToCrop" src="" style="max-width: 100%;">
                    </div>
                </div>
                <div class="modal-footer border-white/10">
                    <button type="button" class="btn btn-secondary text-white bg-slate-600 border-none"
                        data-bs-dismiss="modal">Отмена</button>
                    <button type="button" class="btn btn-primary bg-primary border-none glow-red"
                        id="cropButton">Применить и сохранить</button>
                </div>
            </div>
        </div>
    </div>

    <?php include("app/include/footer.php"); ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0-beta2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.13/cropper.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // Cropper.js logic
            let cropper;
            const avatarInput = document.getElementById('avatarInput');
            const imageToCrop = document.getElementById('imageToCrop');
            const cropModalObj = new bootstrap.Modal(document.getElementById('cropModal'));
            const avatarPreview = document.getElementById('avatarPreview');
            const avatarIcon = document.getElementById('avatarIcon');

            if (avatarInput) {
                avatarInput.addEventListener('change', function (e) {
                    const files = e.target.files;
                    if (files && files.length > 0) {
                        const file = files[0];
                        const reader = new FileReader();
                        reader.onload = function (event) {
                            imageToCrop.src = event.target.result;
                            cropModalObj.show();
                        };
                        reader.readAsDataURL(file);
                        avatarInput.value = '';
                    }
                });
            }

            const cropModalEl = document.getElementById('cropModal');
            if (cropModalEl) {
                cropModalEl.addEventListener('shown.bs.modal', function () {
                    cropper = new Cropper(imageToCrop, {
                        aspectRatio: 1,
                        viewMode: 1,
                        autoCropArea: 1,
                        responsive: true
                    });
                });

                cropModalEl.addEventListener('hidden.bs.modal', function () {
                    if (cropper) {
                        cropper.destroy();
                        cropper = null;
                    }
                });
            }

            const cropBtn = document.getElementById('cropButton');
            if (cropBtn) {
                cropBtn.addEventListener('click', function () {
                    if (!cropper) return;

                    cropper.getCroppedCanvas({
                        width: 300,
                        height: 300
                    }).toBlob((blob) => {
                        const reader = new FileReader();
                        reader.onloadend = function () {
                            const base64data = reader.result;
                            document.getElementById('cropped_image').value = base64data;

                            avatarPreview.src = base64data;
                            avatarPreview.style.display = 'block';
                            if (avatarIcon) avatarIcon.style.display = 'none';

                            cropModalObj.hide();

                            const submitBtn = document.querySelector('button[name="update_profile"]');
                            if (submitBtn) {
                                submitBtn.innerHTML = '<i class="fas fa-save mr-2"></i> Сохранить (с новым аватаром)';
                                submitBtn.classList.remove('bg-primary');
                                submitBtn.classList.add('bg-yellow-500', 'text-slate-900', 'hover:bg-yellow-400', 'glow-red', 'shadow-yellow-500/50');
                            }
                        }
                        reader.readAsDataURL(blob);
                    }, 'image/webp', 0.8);
                });
            }
        });
    </script>
</body>

</html>