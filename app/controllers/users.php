<?php
include_once SITE_ROOT . "/app/database/db.php";
include_once SITE_ROOT . "/app/helps/csrf_helper.php";

$errMsg = [];

function userAuth($user)
{
    $_SESSION['id'] = $user['id'];
    $_SESSION['login'] = $user['username'];
    $_SESSION['email'] = $user['email'] ?? '';
    $_SESSION['admin'] = $user['admin'];
    if ($_SESSION['admin']) {
        header('location: ' . BASE_URL . "admin/cars/index.php");
    }
    else {
        header('location: ' . BASE_URL);
    }
}

$users = selectAll('users');

// Код для формы регистрации
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['button-reg'])) {

    if (!validateCsrfToken($_POST)) {
        array_push($errMsg, "Ошибка CSRF токена! Попробуйте отправить форму еще раз.");
    }
    else {
        $admin = 0;
        $login = trim($_POST['login']);
        $email = trim($_POST['mail']);
        $passF = trim($_POST['pass-first']);
        $passS = trim($_POST['pass-second']);

        if ($login === '' || $email === '' || $passF === '') {
            array_push($errMsg, "Не все поля заполнены!");
        }
        elseif (mb_strlen($login, 'UTF8') < 2) {
            array_push($errMsg, "Логин должен быть более 2-х символов");
        }
        elseif ($passF !== $passS) {
            array_push($errMsg, "Пароли в обоих полях должны соотвеь!");
        }
        else {
            $existence = selectOne('users', ['email' => $email]);
            if ($existence && $existence['email'] === $email) {
                array_push($errMsg, "Пользователь с такой почтой уже зарегистрирован!");
            }
            else {
                $pass = password_hash($passF, PASSWORD_DEFAULT);
                $post = [
                    'admin' => $admin,
                    'username' => $login,
                    'email' => $email,
                    'password' => $pass
                ];
                $id = insert('users', $post);
                $user = selectOne('users', ['id' => $id]);
                userAuth($user);
            }
        }
    }
}
else {
    $login = '';
    $email = '';
}

// Код для формы авторизации
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['button-log'])) {

    if (!validateCsrfToken($_POST)) {
        array_push($errMsg, "Ошибка CSRF токена! Попробуйте отправить форму еще раз.");
    }
    else {
        $email = trim($_POST['mail']);
        $pass = trim($_POST['password']);

        if ($email === '' || $pass === '') {
            array_push($errMsg, "Не все поля заполнены!");
        }
        else {
            // Защита от брутфорса
            global $pdo;
            $ip = $_SERVER['REMOTE_ADDR'];

            // Очистка старых попыток (старше 5 минут)
            $stmt_cleanup = $pdo->prepare("DELETE FROM login_attempts WHERE last_attempt_time < DATE_SUB(NOW(), INTERVAL 5 MINUTE)");
            $stmt_cleanup->execute();

            $stmt_check = $pdo->prepare("SELECT attempts, TIME_TO_SEC(TIMEDIFF(DATE_ADD(last_attempt_time, INTERVAL 5 MINUTE), NOW())) as time_left FROM login_attempts WHERE ip_address = ?");
            $stmt_check->execute([$ip]);
            $attempt_row = $stmt_check->fetch();

            if ($attempt_row && $attempt_row['attempts'] >= 5 && $attempt_row['time_left'] > 0) {
                // Передаем оставшееся время в секундах через скрытое поле ошибки массива
                $timeLeft = (int)$attempt_row['time_left'];
                array_push($errMsg, "BLOCKED_TIMER:" . $timeLeft);
            }
            else {
                $existence = selectOne('users', ['email' => $email]);
                if (!$existence) {
                    $existence = selectOne('users', ['username' => $email]);
                }

                if ($existence && password_verify($pass, $existence['password'])) {
                    // Успешный вход: сбрасываем попытки
                    $stmt_clear = $pdo->prepare("DELETE FROM login_attempts WHERE ip_address = ?");
                    $stmt_clear->execute([$ip]);
                    userAuth($existence);
                }
                else {
                    // Неудачный вход: увеличиваем счетчик
                    if ($attempt_row) {
                        $stmt_update = $pdo->prepare("UPDATE login_attempts SET attempts = attempts + 1, last_attempt_time = NOW() WHERE ip_address = ?");
                        $stmt_update->execute([$ip]);
                    }
                    else {
                        $stmt_insert = $pdo->prepare("INSERT INTO login_attempts (ip_address, attempts, last_attempt_time) VALUES (?, 1, NOW())");
                        $stmt_insert->execute([$ip]);
                    }
                    array_push($errMsg, "Email либо пароль введены неверно!");
                }
            }
        }
    }
}
else {
    $email = '';
}

// Код добавления пользователя в админке
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['create-user'])) {

    if (!validateCsrfToken($_POST)) {
        array_push($errMsg, "Ошибка проверки безопасности (CSRF).");
    }
    else {
        $admin = 0;
        $login = trim($_POST['login']);
        $email = trim($_POST['mail']);
        $passF = trim($_POST['pass-first']);
        $passS = trim($_POST['pass-second']);

        if ($login === '' || $email === '' || $passF === '') {
            array_push($errMsg, "Не все поля заполнены!");
        }
        elseif (mb_strlen($login, 'UTF8') < 2) {
            array_push($errMsg, "Логин должен быть более 2-х символов");
        }
        elseif ($passF !== $passS) {
            array_push($errMsg, "Пароли в обоих полях должны совпадат !");
        }
        else {
            $existence = selectOne('users', ['email' => $email]);
            if ($existence && $existence['email'] === $email) {
                array_push($errMsg, "Пользователь с такой почтой уже зарегистрирован!");
            }
            else {
                $pass = password_hash($passF, PASSWORD_DEFAULT);
                if (isset($_POST['admin']))
                    $admin = 1;
                $user = [
                    'admin' => $admin,
                    'username' => $login,
                    'email' => $email,
                    'password' => $pass
                ];
                $id = insert('users', $user);
                $user = selectOne('users', ['id' => $id]);
                userAuth($user);
            }
        }
    }
}
else {
    $login = '';
    $email = '';
}

// Код удаления пользователя в админке
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_id'])) {
    if (!isset($_SESSION['admin']) || !$_SESSION['admin']) {
        header('location: ' . BASE_URL);
        exit;
    }
    if (!validateCsrfToken($_POST)) {
        header('location: ' . BASE_URL . 'admin/users/index.php');
        exit;
    }
    $id = $_POST['delete_id'];

    // Получаем пользователя, чтобы узнать его email (для удаления комментов)
    $user = selectOne('users', ['id' => $id]);

    if ($user) {
        // 1. Находим все машины, которые добавил этот пользователь
        $userCars = selectAll('cars', ['id_user' => $id]);

        foreach ($userCars as $car) {
            $carId = $car['id'];

            // 1.1 Удаляем главное фото машины с диска
            if (!empty($car['img']) && file_exists(ROOT_PATH . "/assets/images/cars/" . $car['img'])) {
                unlink(ROOT_PATH . "/assets/images/cars/" . $car['img']);
            }

            // 1.2 Получаем галерею машины и удаляем все фото с диска
            $carImages = selectCarImages($carId);
            if ($carImages) {
                foreach ($carImages as $image) {
                    if (!empty($image['img']) && file_exists(ROOT_PATH . "/assets/images/cars/" . $image['img'])) {
                        unlink(ROOT_PATH . "/assets/images/cars/" . $image['img']);
                    }
                    deleteCarImage($image['id']);
                }
            }

            // 1.3 Удаляем все комментарии, оставленные ПОД этой машиной (любыми пользователями)
            global $pdo;
            $sql = "DELETE FROM comments WHERE page = :page";
            $query = $pdo->prepare($sql);
            $query->execute(['page' => $carId]);

            // 1.4 Удаляем саму машину из БД
            delete('cars', $carId);
        }

        // 2. Удаляем все комментарии, которые оставил САМ этот пользователь
        if (!empty($user['email'])) {
            global $pdo;
            $sql = "DELETE FROM comments WHERE email = :email";
            $query = $pdo->prepare($sql);
            $query->execute(['email' => $user['email']]);
        }

        // 3. Удаляем самого пользователя
        delete('users', $id);
    }

    header('location: ' . BASE_URL . 'admin/users/index.php');
}

// РЕДАКТИРОВАНИЕ ПОЛЬЗОВАТЕЛЯ ЧЕРЕЗ АДМИНКУ
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['edit_id'])) {
    $user = selectOne('users', ['id' => $_GET['edit_id']]);

    $id = $user['id'];
    $admin = $user['admin'];
    $username = $user['username'];
    $email = $user['email'];
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update-user'])) {

    if (!validateCsrfToken($_POST)) {
        array_push($errMsg, "Ошибка проверки безопасности (CSRF).");
    }
    else {
        $id = $_POST['id'];
        $mail = trim($_POST['mail']);
        $login = trim($_POST['login']);
        $passF = trim($_POST['pass-first']);
        $passS = trim($_POST['pass-second']);
        $admin = isset($_POST['admin']) ? 1 : 0;

        if ($login === '') {
            array_push($errMsg, "Не все поля заполнены!");
        }
        elseif (mb_strlen($login, 'UTF8') < 2) {
            array_push($errMsg, "Логин должен быть более 2-х символов");
        }
        elseif ($passF !== $passS) {
            array_push($errMsg, "Пароли в обеих полях должны соответствовать!");
        }
        else {
            $user = [
                'admin' => $admin,
                'username' => $login,
                // 'email' => $mail,
            ];

            // Обновляем пароль только если введено новое значение
            if (!empty($passF)) {
                $user['password'] = password_hash($passF, PASSWORD_DEFAULT);
            }

            update('users', $id, $user);
            header('location: ' . BASE_URL . 'admin/users/index.php');
        }
    }
}
else {
    $id = isset($user) ? $user['id'] : '';
    $admin = isset($user) ? $user['admin'] : 0;
    $username = isset($user) ? $user['username'] : '';
    $email = isset($user) ? ($user['email'] ?? '') : '';
}