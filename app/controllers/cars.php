<?php

include_once SITE_ROOT . "/app/database/db.php";
include_once SITE_ROOT . "/app/helps/csrf_helper.php";
include_once SITE_ROOT . "/app/helps/image_helper.php";
if (!$_SESSION) {
    header('location: ' . BASE_URL . 'log.php');
}

$errMsg = [];
$id = '';
$title = '';
$description = '';
$img = '';
$brand = '';

$brands = selectAll('brands');

// Пагинация для админки
$page = isset($_GET['page']) ? intval($_GET['page']) : 1;
if ($page < 1)
    $page = 1;
$perPage = 10;
$offset = ($page - 1) * $perPage;

$totalCarsAdm = countAllCarsForAdmin();
$totalPagesAdm = ceil($totalCarsAdm / $perPage);

$carsAdm = selectCarsWithBrandsForAdmin($perPage, $offset);

// Массовые действия
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['apply_bulk'])) {
    if (!empty($_POST['selected_ids']) && isset($_POST['bulk_action'])) {
        $action = $_POST['bulk_action'];
        $ids = $_POST['selected_ids'];

        if ($action === 'delete') {
            global $pdo;
            foreach ($ids as $del_id) {
                // Полная каскадная очистка (как в одиночном удалении)
                $car = selectOne('cars', ['id' => $del_id]);
                if ($car) {
                    if (!empty($car['img']) && file_exists(ROOT_PATH . "/assets/images/cars/" . $car['img'])) {
                        unlink(ROOT_PATH . "/assets/images/cars/" . $car['img']);
                    }
                    $carImages = selectCarImages($del_id);
                    if ($carImages) {
                        foreach ($carImages as $image) {
                            if (!empty($image['img']) && file_exists(ROOT_PATH . "/assets/images/cars/" . $image['img'])) {
                                unlink(ROOT_PATH . "/assets/images/cars/" . $image['img']);
                            }
                            deleteCarImage($image['id']);
                        }
                    }
                    $sql = "DELETE FROM comments WHERE page = :page";
                    $query = $pdo->prepare($sql);
                    $query->execute(['page' => $del_id]);

                    delete('cars', $del_id);
                }
            }
        }
        elseif ($action === 'publish') {
            foreach ($ids as $publish_id) {
                update('cars', $publish_id, ['status' => 1]);
            }
        }
        elseif ($action === 'draft') {
            foreach ($ids as $draft_id) {
                update('cars', $draft_id, ['status' => 0]);
            }
        }
    }
    header('location: ' . BASE_URL . 'admin/cars/index.php');
    exit;
}

// Создание авто
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_car'])) {
    if (!validateCsrfToken($_POST)) {
        array_push($errMsg, "Ошибка проверки безопасности (CSRF).");
    }
    else {
        // Главное фото
        if (!empty($_POST['cropped_img'])) {
            $base64 = $_POST['cropped_img'];
            $base64 = preg_replace('#^data:image/\w+;base64,#i', '', $base64);
            $imgData = base64_decode($base64);
            $fileName = time() . '_' . uniqid() . '.jpg';
            if (!is_dir(ROOT_PATH . "/assets/images/cars")) {
                mkdir(ROOT_PATH . "/assets/images/cars", 0777, true);
            }
            $targetPath = ROOT_PATH . "/assets/images/cars/" . $fileName;
            file_put_contents($targetPath, $imgData);
            $_POST['img'] = $fileName;
        }
        elseif (!empty($_FILES['img']['name'])) {
            if (!is_dir(ROOT_PATH . "/assets/images/cars")) {
                mkdir(ROOT_PATH . "/assets/images/cars", 0777, true);
            }
            $uploadedName = uploadAndCropImage($_FILES['img'], ROOT_PATH . "/assets/images/cars", 800, 600);
            if ($uploadedName) {
                $_POST['img'] = $uploadedName;
            }
            else {
                array_push($errMsg, "Ошибка загрузки! Файл должен быть формата JPG, PNG или WebP.");
            }
        }
        else {
            $_POST['img'] = '';
        }

        $title = trim($_POST['title']);
        $description = trim($_POST['description']);
        $brand = trim($_POST['brand']);
        $publish = isset($_POST['publish']) ? 1 : 0;
        $featured = isset($_POST['featured']) ? 1 : 0;

        $price = floatval($_POST['price'] ?? 0);
        $year = intval($_POST['year'] ?? date('Y'));
        $mileage = intval($_POST['mileage'] ?? 0);
        $engine_volume = floatval($_POST['engine_volume'] ?? 0);
        $horsepower = intval($_POST['horsepower'] ?? 0);

        if ($title === '' || $brand === '') {
            array_push($errMsg, "Не все обязательные поля заполнены!");
        }
        elseif (mb_strlen($title, 'UTF8') < 3) {
            array_push($errMsg, "Название авто должно быть более 3-х символов");
        }
        elseif ($price < 0) {
            array_push($errMsg, "Цена не может быть отрицательной");
        }
        elseif ($year < 1900 || $year > date('Y') + 1) {
            array_push($errMsg, "Некорректный год выпуска");
        }
        elseif ($mileage < 0) {
            array_push($errMsg, "Пробег не может быть отрицательным");
        }
        elseif ($engine_volume < 0 || $engine_volume > 15) {
            array_push($errMsg, "Некорректный объем двигателя");
        }
        elseif ($horsepower < 0 || $horsepower > 2000) {
            array_push($errMsg, "Некорректная мощность двигателя");
        }
        else {
            $car = [
                'id_user' => $_SESSION['id'],
                'id_brand' => $brand,
                'title' => $title,
                'price' => $price,
                'year' => $year,
                'mileage' => $mileage,
                'engine_type' => $_POST['engine_type'] ?? '',
                'engine_volume' => $engine_volume,
                'horsepower' => $horsepower,
                'transmission' => $_POST['transmission'] ?? '',
                'drive_type' => $_POST['drive_type'] ?? '',
                'body_type' => $_POST['body_type'] ?? '',
                'color' => $_POST['color'] ?? '',
                'description' => $description,
                'img' => $_POST['img'],
                'status' => $publish,
                'featured' => $featured
            ];

            $carId = insert('cars', $car);

            // Загрузка дополнительных фото (галерея)
            if (!empty($_FILES['gallery']['name'][0])) {
                if (!is_dir(ROOT_PATH . "/assets/images/cars")) {
                    mkdir(ROOT_PATH . "/assets/images/cars", 0777, true);
                }
                foreach ($_FILES['gallery']['name'] as $key => $galleryName) {
                    if (!empty($galleryName)) {
                        $galleryFile = [
                            'name' => $_FILES['gallery']['name'][$key],
                            'type' => $_FILES['gallery']['type'][$key],
                            'tmp_name' => $_FILES['gallery']['tmp_name'][$key],
                            'error' => $_FILES['gallery']['error'][$key],
                            'size' => $_FILES['gallery']['size'][$key],
                        ];
                        $uploadedName = uploadAndCropImage($galleryFile, ROOT_PATH . "/assets/images/cars", 800, 600);
                        if ($uploadedName) {
                            insertCarImage($carId, $uploadedName, $key);
                        }
                    }
                }
            }

            header('location: ' . BASE_URL . 'admin/cars/index.php');
            exit();
        }
    }
}
else {
    $id = '';
    $title = '';
    $description = '';
    $publish = '';
    $brand = '';
}

// Загрузка данных авто для редактирования
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['id'])) {
    $car = selectOne('cars', ['id' => $_GET['id']]);
    $id = $car['id'];
    $title = $car['title'];
    $description = $car['description'];
    $brand = $car['id_brand'];
    $publish = $car['status'];
}

// Сохранение редактирования авто
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_car'])) {

    if (!validateCsrfToken($_POST)) {
        array_push($errMsg, "Ошибка проверки безопасности (CSRF).");
    }
    else {
        $id = $_POST['id'];
        $title = trim($_POST['title']);
        $description = trim($_POST['description']);
        $brand = trim($_POST['brand']);
        $publish = isset($_POST['publish']) ? 1 : 0;
        $featured = isset($_POST['featured']) ? 1 : 0;

        // Новое главное фото
        if (!empty($_POST['cropped_img'])) {
            $base64 = $_POST['cropped_img'];
            $base64 = preg_replace('#^data:image/\w+;base64,#i', '', $base64);
            $imgData = base64_decode($base64);
            $fileName = time() . '_' . uniqid() . '.jpg';
            if (!is_dir(ROOT_PATH . "/assets/images/cars")) {
                mkdir(ROOT_PATH . "/assets/images/cars", 0777, true);
            }
            $targetPath = ROOT_PATH . "/assets/images/cars/" . $fileName;
            file_put_contents($targetPath, $imgData);
            $_POST['img'] = $fileName;
        }
        elseif (!empty($_FILES['img']['name'])) {
            if (!is_dir(ROOT_PATH . "/assets/images/cars")) {
                mkdir(ROOT_PATH . "/assets/images/cars", 0777, true);
            }
            $uploadedName = uploadAndCropImage($_FILES['img'], ROOT_PATH . "/assets/images/cars", 800, 600);
            if ($uploadedName) {
                $_POST['img'] = $uploadedName;
            }
            else {
                array_push($errMsg, "Ошибка загрузки! Файл должен быть формата JPG, PNG или WebP.");
            }
        }
        else {
            $_POST['img'] = $_POST['current_img'] ?? '';
        }

        $price = floatval($_POST['price'] ?? 0);
        $year = intval($_POST['year'] ?? date('Y'));
        $mileage = intval($_POST['mileage'] ?? 0);
        $engine_volume = floatval($_POST['engine_volume'] ?? 0);
        $horsepower = intval($_POST['horsepower'] ?? 0);

        if ($title === '' || $brand === '') {
            array_push($errMsg, "Не все обязательные поля заполнены!");
        }
        elseif (mb_strlen($title, 'UTF8') < 3) {
            array_push($errMsg, "Название авто должно быть более 3-х символов");
        }
        elseif ($price < 0) {
            array_push($errMsg, "Цена не может быть отрицательной");
        }
        elseif ($year < 1900 || $year > date('Y') + 1) {
            array_push($errMsg, "Некорректный год выпуска");
        }
        elseif ($mileage < 0) {
            array_push($errMsg, "Пробег не может быть отрицательным");
        }
        elseif ($engine_volume < 0 || $engine_volume > 15) {
            array_push($errMsg, "Некорректный объем двигателя");
        }
        elseif ($horsepower < 0 || $horsepower > 2000) {
            array_push($errMsg, "Некорректная мощность двигателя");
        }
        else {
            $car = [
                'id_user' => $_SESSION['id'],
                'id_brand' => $brand,
                'title' => $title,
                'price' => $price,
                'year' => $year,
                'mileage' => $mileage,
                'engine_type' => $_POST['engine_type'] ?? '',
                'engine_volume' => $engine_volume,
                'horsepower' => $horsepower,
                'transmission' => $_POST['transmission'] ?? '',
                'drive_type' => $_POST['drive_type'] ?? '',
                'body_type' => $_POST['body_type'] ?? '',
                'color' => $_POST['color'] ?? '',
                'description' => $description,
                'img' => $_POST['img'],
                'status' => $publish,
                'featured' => $featured
            ];

            update('cars', $id, $car);

            // Загрузка новых фото в галерею
            if (!empty($_FILES['gallery']['name'][0])) {
                if (!is_dir(ROOT_PATH . "/assets/images/cars")) {
                    mkdir(ROOT_PATH . "/assets/images/cars", 0777, true);
                }
                foreach ($_FILES['gallery']['name'] as $key => $galleryName) {
                    if (!empty($galleryName)) {
                        $galleryFile = [
                            'name' => $_FILES['gallery']['name'][$key],
                            'type' => $_FILES['gallery']['type'][$key],
                            'tmp_name' => $_FILES['gallery']['tmp_name'][$key],
                            'error' => $_FILES['gallery']['error'][$key],
                            'size' => $_FILES['gallery']['size'][$key],
                        ];
                        $uploadedName = uploadAndCropImage($galleryFile, ROOT_PATH . "/assets/images/cars", 800, 600);
                        if ($uploadedName) {
                            insertCarImage($id, $uploadedName, $key);
                        }
                    }
                }
            }

            header('location: ' . BASE_URL . 'admin/cars/index.php');
            exit();
        }
    }
}
else {
    if (isset($_POST['title'])) {
        $title = $_POST['title'];
        $description = $_POST['description'];
        $publish = isset($_POST['publish']) ? 1 : 0;
        $brand = $_POST['brand'];
    }
}

// Публикация / снятие с публикации
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['pub_id'])) {
    $id = $_GET['pub_id'];
    $publish = $_GET['publish'];
    update('cars', $id, ['status' => $publish]);
    header('location: ' . BASE_URL . 'admin/cars/index.php');
    exit();
}

// Удаление авто
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_id'])) {
    if (!isset($_SESSION['admin']) || !$_SESSION['admin']) {
        header('location: ' . BASE_URL);
        exit;
    }
    $id = $_POST['delete_id'];

    // Получаем данные авто
    $car = selectOne('cars', ['id' => $id]);
    if ($car) {
        // 1. Удаляем главное фото с диска
        if (!empty($car['img']) && file_exists(ROOT_PATH . "/assets/images/cars/" . $car['img'])) {
            unlink(ROOT_PATH . "/assets/images/cars/" . $car['img']);
        }

        // 2. Получаем галерею и удаляем все фото с диска
        $carImages = selectCarImages($id);
        if ($carImages) {
            foreach ($carImages as $image) {
                if (!empty($image['img']) && file_exists(ROOT_PATH . "/assets/images/cars/" . $image['img'])) {
                    unlink(ROOT_PATH . "/assets/images/cars/" . $image['img']);
                }
                // Удаляем запись фотогалереи из БД (хотя каскадно можно и не делать, но лучше очистить)
                deleteCarImage($image['id']);
            }
        }

        // 3. Удаляем комментарии, привязанные к этому авто
        // Это требует прямого SQL запроса или создания новой функции deleteCommentsByPage, 
        // но можно использовать PDO напрямую тут для скорости
        global $pdo;
        $sql = "DELETE FROM comments WHERE page = :page";
        $query = $pdo->prepare($sql);
        $query->execute(['page' => $id]);
    }

    // 4. Удаляем само авто
    delete('cars', $id);
    header('location: ' . BASE_URL . 'admin/cars/index.php');
}

// Удаление фото из галереи (ручное, при редактировании)
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['del_img_id'])) {
    $imgId = $_GET['del_img_id'];
    $carId = $_GET['car_id'] ?? '';

    // Сначала получаем имя файла, чтобы удалить с диска
    global $pdo;
    $sql = "SELECT img FROM car_images WHERE id = :id";
    $query = $pdo->prepare($sql);
    $query->execute(['id' => $imgId]);
    $imageRecord = $query->fetch();

    if ($imageRecord && !empty($imageRecord['img'])) {
        $imgPath = ROOT_PATH . "/assets/images/cars/" . $imageRecord['img'];
        if (file_exists($imgPath)) {
            unlink($imgPath);
        }
    }

    deleteCarImage($imgId);
    header('location: ' . BASE_URL . 'admin/cars/edit.php?id=' . $carId);
}
