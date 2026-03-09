<?php include("path.php");
include "app/database/db.php";
include "app/helps/csrf_helper.php";

$errMsg = [];
$successMsg = '';
$email = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['forgot-btn'])) {
    if (!validateCsrfToken($_POST)) {
        array_push($errMsg, "Ошибка проверки CSRF!");
    } else {
        $email = trim($_POST['email']);
        if (empty($email)) {
            array_push($errMsg, "Введите ваш email!");
        } else {
            $user = selectOne('users', ['email' => $email]);
            if ($user) {
                // Генерируем уникальный токен
                $token = bin2hex(random_bytes(32));

                // Сохраняем в таблицу
                global $pdo;
                // Сначала удаляем старые токены этого юзера, если были
                $del = $pdo->prepare("DELETE FROM password_resets WHERE email = ?");
                $del->execute([$email]);

                $stmt = $pdo->prepare("INSERT INTO password_resets (email, token, expires_at) VALUES (?, ?, DATE_ADD(NOW(), INTERVAL 1 HOUR))");
                $stmt->execute([$email, $token]);

                // Формируем ссылку
                $resetLink = BASE_URL . "reset.php?token=" . $token;

                // Имитация отправки email (запись в файл)
                $logMessage = "[" . date('Y-m-d H:i:s') . "] Reset password link for $email : $resetLink \n";
                file_put_contents(ROOT_PATH . "/emails.log", $logMessage, FILE_APPEND);

                $successMsg = "Ссылка для восстановления пароля отправлена! (Заглушка: проверьте файл emails.log в папке сайта)";
            } else {
                // В целях безопасности можно писать "Если такой email есть, то письмо отправлено", но для теста выведем явно
                array_push($errMsg, "Пользователь с таким email не найден!");
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
    <title>Восстановление пароля &mdash; AvtoTachka</title>
</head>

<body
    class="bg-slate-50 dark:bg-background-dark text-slate-900 dark:text-slate-100 <?= ($_SESSION['theme'] ?? 'dark') === 'dark' ? 'dark-theme' : '' ?>">

    <?php include("app/include/header.php"); ?>

    <div class="container reg_form">
        <form class="row justify-content-center" method="post" action="forgot.php">
            <h2 class="col-12">Восстановление пароля</h2>

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

            <?= csrfField() ?>
            <div class="w-100"></div>

            <div class="mb-3 col-12 col-md-4">
                <label class="form-label">Ваш Email</label>
                <input name="email" value="<?= htmlspecialchars($email) ?>" type="email" class="form-control"
                    placeholder="example@gmail.com" required>
            </div>

            <div class="w-100"></div>

            <div class="mb-3 col-12 col-md-4">
                <button type="submit" name="forgot-btn" class="btn btn-secondary w-100">Отправить ссылку</button>
            </div>
        </form>
    </div>

    <?php include("app/include/footer.php"); ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0-beta2/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>