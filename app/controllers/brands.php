<?php
include_once SITE_ROOT . "/app/database/db.php";

$errMsg = '';
$id = '';
$name = '';
$logo = '';
$country = '';

$brands = selectAll('brands');

// Создание бренда
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['brand-create'])) {
    $name = isset($_POST['name']) ? trim($_POST['name']) : '';
    $country = isset($_POST['country']) ? trim($_POST['country']) : 'Китай';

    // Загрузка логотипа
    $logoName = '';
    if (!empty($_FILES['logo']['name'])) {
        $imgName = time() . "_" . $_FILES['logo']['name'];
        $fileTmpName = $_FILES['logo']['tmp_name'];
        $fileType = $_FILES['logo']['type'];
        $destination = ROOT_PATH . "/assets/images/brands/" . $imgName;

        if (strpos($fileType, 'image') !== false) {
            if (!is_dir(ROOT_PATH . "/assets/images/brands")) {
                mkdir(ROOT_PATH . "/assets/images/brands", 0777, true);
            }
            move_uploaded_file($fileTmpName, $destination);
            $logoName = $imgName;
        }
    }

    if ($name === '') {
        $errMsg = "Название бренда не может быть пустым!";
    }
    elseif (mb_strlen($name, 'UTF8') < 2) {
        $errMsg = "Название бренда должно быть более 2-х символов";
    }
    else {
        $existence = selectOne('brands', ['name' => $name]);
        if ($existence && $existence['name'] === $name) {
            $errMsg = "Такой бренд уже есть в базе";
        }
        else {
            $brand = [
                'name' => $name,
                'logo' => $logoName,
                'country' => $country
            ];
            $id = insert('brands', $brand);
            header('location: ' . BASE_URL . 'admin/brands/index.php');
        }
    }
}
else {
    $name = '';
    $country = '';
}

// Апдейт бренда
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['id'])) {
    $id = $_GET['id'];
    $brand = selectOne('brands', ['id' => $id]);
    $id = $brand['id'];
    $name = $brand['name'];
    $logo = $brand['logo'];
    $country = $brand['country'];
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['brand-edit'])) {
    $name = trim($_POST['name']);
    $country = trim($_POST['country']);

    // Загрузка нового логотипа
    $logoName = $_POST['current_logo'] ?? '';
    if (!empty($_FILES['logo']['name'])) {
        $imgName = time() . "_" . $_FILES['logo']['name'];
        $fileTmpName = $_FILES['logo']['tmp_name'];
        $fileType = $_FILES['logo']['type'];
        $destination = ROOT_PATH . "/assets/images/brands/" . $imgName;

        if (strpos($fileType, 'image') !== false) {
            if (!is_dir(ROOT_PATH . "/assets/images/brands")) {
                mkdir(ROOT_PATH . "/assets/images/brands", 0777, true);
            }
            move_uploaded_file($fileTmpName, $destination);
            $logoName = $imgName;
        }
    }

    if ($name === '') {
        $errMsg = "Название бренда не может быть пустым!";
    }
    elseif (mb_strlen($name, 'UTF8') < 2) {
        $errMsg = "Название бренда должно быть более 2-х символов";
    }
    else {
        $brand = [
            'name' => $name,
            'logo' => $logoName,
            'country' => $country
        ];
        $id = $_POST['id'];
        update('brands', $id, $brand);
        header('location: ' . BASE_URL . 'admin/brands/index.php');
    }
}

// Удаление бренда
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['del_id'])) {
    $id = $_GET['del_id'];
    delete('brands', $id);
    header('location: ' . BASE_URL . 'admin/brands/index.php');
}
