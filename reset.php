<?php include("path.php");
include "app/database/db.php";
include "app/helps/csrf_helper.php";

$errMsg = [];
$successMsg = '';
$token = $_GET['token'] ?? '';

if (empty($token)) {
    header('location: ' . BASE_URL);
    exit;
}

// Проверка валидности токена
global $pdo;
$stmt = $pdo->prepare("SELECT * FROM password_resets WHERE token = ? AND expires_at > NOW()");
$stmt->execute([$token]);
$reset = $stmt->fetch();

if (!$reset) {
    die("<div style='text-align:center; padding: 50px; font-family: sans-serif;'><h2>Ошибка</h2><p>Ссылка недействительна или время ее действия (1 час) истекло.</p></div>");
}

$email = $reset['email'];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['reset-btn'])) {
    if (!validateCsrfToken($_POST)) {
        array_push($errMsg, "Ошибка CSRF токена!");
    } else {
        $pass1 = trim($_POST['pass1']);
        $pass2 = trim($_POST['pass2']);

        if (empty($pass1) || empty($pass2)) {
            array_push($errMsg, "Заполните оба поля!");
        } elseif ($pass1 !== $pass2) {
            array_push($errMsg, "Пароли не совпадают!");
        } elseif (mb_strlen($pass1, 'UTF-8') < 4) {
            array_push($errMsg, "Пароль должен быть не менее 4 символов!");
        } else {
            $hashed = password_hash($pass1, PASSWORD_DEFAULT);
            $user = selectOne('users', ['email' => $email]);

            if ($user) {
                update('users', $user['id'], ['password' => $hashed]);

                // Удаляем токен чтобы его нельзя было использовать повторно
                $del = $pdo->prepare("DELETE FROM password_resets WHERE email = ?");
                $del->execute([$email]);

                $successMsg = "Пароль успешно изменен! Вы можете <a href='" . BASE_URL . "log.php' class='alert-link'>войти</a> с новым паролем.";
            } else {
                array_push($errMsg, "Пользователь не найден!");
            }
        }
    }
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

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0-beta2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.7.2/css/all.css">
    <link rel="stylesheet" href="assets/css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Comfortaa:wght@300;400;500;600;700&display=swap"
        rel="stylesheet">
    <title>Новый пароль &mdash; AvtoTachka</title>
</head>

<body
    class="bg-slate-50 dark:bg-background-dark text-slate-900 dark:text-slate-100 <?= ($_SESSION['theme'] ?? 'dark') === 'dark' ? 'dark-theme' : '' ?>">

    <?php include("app/include/header.php"); ?>

    <div class="container reg_form">
        <form class="row justify-content-center" method="post" action="reset.php?token=<?= htmlspecialchars($token) ?>">
            <h2 class="col-12">Установка нового пароля</h2>
            <p class="col-12 text-center text-muted">Для аккаунта:
                <?= htmlspecialchars($email) ?>
            </p>

            <div class="mb-3 col-12 col-md-4">
                <?php if (!empty($errMsg)): ?>
                    <div class="alert alert-danger">
                        <?php foreach ($errMsg as $e)
                            echo "<p class='mb-0'>$e</p>"; ?>
                    </div>
                <?php endif; ?>

                <?php if ($successMsg): ?>
                    <div class="alert alert-success">
                        <?= $successMsg ?>
                    </div>
                <?php endif; ?>
            </div>

            <?php if (!$successMsg): ?>
                <?= csrfField() ?>
                <div class="w-100"></div>

                <div class="mb-3 col-12 col-md-4">
                    <label class="form-label">Новый пароль</label>
                    <div class="input-group">
                        <input name="pass1" type="password" class="form-control" id="pass1" required>
                        <button class="btn btn-outline-secondary toggle-password" type="button" data-target="#pass1"><i
                                class="fas fa-eye"></i></button>
                    </div>
                </div>

                <div class="w-100"></div>

                <div class="mb-3 col-12 col-md-4">
                    <label class="form-label">Повторите пароль</label>
                    <div class="input-group">
                        <input name="pass2" type="password" class="form-control" id="pass2" required>
                        <button class="btn btn-outline-secondary toggle-password" type="button" data-target="#pass2"><i
                                class="fas fa-eye"></i></button>
                    </div>
                </div>

                <div class="w-100"></div>

                <div class="mb-3 col-12 col-md-4 mt-3">
                    <button type="submit" name="reset-btn" class="btn btn-primary w-100">Сохранить пароль</button>
                </div>
            <?php endif; ?>

        </form>
    </div>

    <?php include("app/include/footer.php"); ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0-beta2/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>