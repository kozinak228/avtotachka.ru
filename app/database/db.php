<?php


if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require 'connect.php';

function tt($value)
{
    echo '<pre>';
    print_r($value);
    echo '</pre>';
}
function tte($value)
{
    echo '<pre>';
    print_r($value);
    echo '</pre>';
    exit();
}

// Проверка выполнения запроса к БД
function dbCheckError($query)
{
    $errInfo = $query->errorInfo();
    if ($errInfo[0] !== PDO::ERR_NONE) {
        echo $errInfo[2];
        exit();
    }
    return true;
}

// ==========================================
// УНИВЕРСАЛЬНЫЕ ФУНКЦИИ (без изменений)
// ==========================================

// Запрос на получение данных с одной таблицы
function selectAll($table, $params = [])
{
    global $pdo;
    $sql = "SELECT * FROM $table";

    if (!empty($params)) {
        $i = 0;
        foreach ($params as $key => $value) {
            if ($i === 0) {
                $sql = $sql . " WHERE $key=:$key";
            }
            else {
                $sql = $sql . " AND $key=:$key";
            }
            $i++;
        }
    }

    $query = $pdo->prepare($sql);
    $query->execute($params);
    dbCheckError($query);
    return $query->fetchAll();
}

// Запрос на получение записей с авторами (JOIN posts + users)
function selectAllFromPostsWithUsers($postsTable, $usersTable)
{
    global $pdo;
    $sql = "SELECT p.*, u.username FROM $postsTable AS p 
            JOIN $usersTable AS u ON p.id_user = u.id 
            ORDER BY p.id DESC";
    $query = $pdo->prepare($sql);
    $query->execute();
    dbCheckError($query);
    return $query->fetchAll();
}

// Запрос на получение одной строки с выбранной таблицы
// Запрос на получение одной строки с одной таблицы
function selectOne($table, $params = [])
{
    global $pdo;
    $sql = "SELECT * FROM $table";

    if (!empty($params)) {
        $i = 0;
        foreach ($params as $key => $value) {
            if ($i === 0) {
                $sql = $sql . " WHERE $key=:$key";
            }
            else {
                $sql = $sql . " AND $key=:$key";
            }
            $i++;
        }
    }

    $query = $pdo->prepare($sql);
    $query->execute($params);
    dbCheckError($query);
    return $query->fetch();
}

// Запись в таблицу БД
function insert($table, $params)
{
    global $pdo;
    $i = 0;
    $coll = '';
    $mask = '';
    foreach ($params as $key => $value) {
        if ($i === 0) {
            $coll = $coll . "$key";
            $mask = $mask . ":" . "$key";
        }
        else {
            $coll = $coll . ", $key";
            $mask = $mask . ", :" . "$key";
        }
        $i++;
    }

    $sql = "INSERT INTO $table ($coll) VALUES ($mask)";

    $query = $pdo->prepare($sql);
    $query->execute($params);
    dbCheckError($query);
    return $pdo->lastInsertId();
}

// Обновление строки в таблице
function update($table, $id, $params)
{
    global $pdo;
    $i = 0;
    $str = '';
    foreach ($params as $key => $value) {
        if ($i === 0) {
            $str = $str . $key . " = :" . $key;
        }
        else {
            $str = $str . ", " . $key . " = :" . $key;
        }
        $i++;
    }

    $sql = "UPDATE $table SET $str WHERE id = :id";
    $params['id'] = $id;
    $query = $pdo->prepare($sql);
    $query->execute($params);
    dbCheckError($query);
}

// Удаление строки из таблицы
function delete($table, $id)
{
    global $pdo;
    $sql = "DELETE FROM $table WHERE id = :id";
    $query = $pdo->prepare($sql);
    $query->execute(['id' => $id]);
    dbCheckError($query);
}

// Считаем количество строк в таблице
function countRow($table, $params = [])
{
    global $pdo;
    $sql = "SELECT Count(*) FROM $table";

    if (!empty($params)) {
        $i = 0;
        foreach ($params as $key => $value) {
            if ($i === 0) {
                $sql = $sql . " WHERE $key=:$key";
            }
            else {
                $sql = $sql . " AND $key=:$key";
            }
            $i++;
        }
    }

    $query = $pdo->prepare($sql);
    $query->execute($params);
    dbCheckError($query);
    return $query->fetchColumn();
}

// ==========================================
// ФУНКЦИИ ДЛЯ КАТАЛОГА АВТО
// ==========================================

// Получить все авто с именем бренда (для админки) без лимита (если где-то еще надо)
function selectAllCarsWithBrands()
{
    global $pdo;
    $sql = "SELECT c.*, b.name AS brand_name, u.username
            FROM cars AS c
            JOIN brands AS b ON c.id_brand = b.id
            JOIN users AS u ON c.id_user = u.id
            ORDER BY c.created_date DESC";
    $query = $pdo->prepare($sql);
    $query->execute();
    dbCheckError($query);
    return $query->fetchAll();
}

// Получить машины для админки с пагинацией
function selectCarsWithBrandsForAdmin($limit, $offset)
{
    global $pdo;
    $sql = "SELECT c.*, b.name AS brand_name, u.username
            FROM cars AS c
            JOIN brands AS b ON c.id_brand = b.id
            JOIN users AS u ON c.id_user = u.id
            ORDER BY c.created_date DESC 
            LIMIT $limit OFFSET $offset";
    $query = $pdo->prepare($sql);
    $query->execute();
    dbCheckError($query);
    return $query->fetchAll();
}

// Подсчет всех машин для пагинации админки
function countAllCarsForAdmin()
{
    global $pdo;
    $sql = "SELECT COUNT(*) FROM cars";
    $query = $pdo->prepare($sql);
    $query->execute();
    dbCheckError($query);
    return $query->fetchColumn();
}

// Получить авто для каталога (с пагинацией, фильтрами)
function selectCarsForCatalog($limit, $offset, $filters = [])
{
    global $pdo;
    $sql = "SELECT c.*, b.name AS brand_name
            FROM cars AS c
            JOIN brands AS b ON c.id_brand = b.id
            WHERE c.status = 1";

    $params = [];

    if (!empty($filters['brand'])) {
        $brand = intval($filters['brand']);
        $sql .= " AND c.id_brand = :brand";
        $params['brand'] = $brand;
    }
    if (!empty($filters['body_type'])) {
        $sql .= " AND c.body_type = :body_type";
        $params['body_type'] = $filters['body_type'];
    }
    if (!empty($filters['price_min'])) {
        $sql .= " AND c.price >= :price_min";
        $params['price_min'] = floatval($filters['price_min']);
    }
    if (!empty($filters['price_max'])) {
        $sql .= " AND c.price <= :price_max";
        $params['price_max'] = floatval($filters['price_max']);
    }

    $sql .= " ORDER BY c.created_date DESC LIMIT $limit OFFSET $offset";

    $query = $pdo->prepare($sql);
    $query->execute($params);
    dbCheckError($query);
    return $query->fetchAll();
}

// Подсчёт авто с фильтрами (для пагинации)
function countCars($filters = [])
{
    global $pdo;
    $sql = "SELECT COUNT(*) FROM cars AS c WHERE c.status = 1";

    $params = [];

    if (!empty($filters['brand'])) {
        $brand = intval($filters['brand']);
        $sql .= " AND c.id_brand = :brand";
        $params['brand'] = $brand;
    }
    if (!empty($filters['body_type'])) {
        $sql .= " AND c.body_type = :body_type";
        $params['body_type'] = $filters['body_type'];
    }
    if (!empty($filters['price_min'])) {
        $sql .= " AND c.price >= :price_min";
        $params['price_min'] = floatval($filters['price_min']);
    }
    if (!empty($filters['price_max'])) {
        $sql .= " AND c.price <= :price_max";
        $params['price_max'] = floatval($filters['price_max']);
    }

    $query = $pdo->prepare($sql);
    $query->execute($params);
    dbCheckError($query);
    return $query->fetchColumn();
}

// Рекомендуемые/топ авто для карусели (все с featured=1, или последние $fallbackLimit если featured нет)
function selectFeaturedCars($fallbackLimit = 5)
{
    global $pdo;
    // Берём ВСЕ авто с featured=1
    $sql = "SELECT c.*, b.name AS brand_name
            FROM cars AS c
            JOIN brands AS b ON c.id_brand = b.id
            WHERE c.status = 1 AND c.featured = 1
            ORDER BY c.created_date DESC";
    $query = $pdo->prepare($sql);
    $query->execute();
    dbCheckError($query);
    $result = $query->fetchAll();

    // Если нет featured, берём последние добавленные
    if (empty($result)) {
        $sql = "SELECT c.*, b.name AS brand_name
                FROM cars AS c
                JOIN brands AS b ON c.id_brand = b.id
                WHERE c.status = 1
                ORDER BY c.created_date DESC
                LIMIT $fallbackLimit";
        $query = $pdo->prepare($sql);
        $query->execute();
        dbCheckError($query);
        $result = $query->fetchAll();
    }

    return $result;
}

// Получить одно авто по ID (полная информация)
function selectCarById($id)
{
    global $pdo;
    $id = intval($id);
    $sql = "SELECT c.*, b.name AS brand_name, u.username
            FROM cars AS c
            JOIN brands AS b ON c.id_brand = b.id
            JOIN users AS u ON c.id_user = u.id
            WHERE c.id = :id";
    $query = $pdo->prepare($sql);
    $query->execute(['id' => $id]);
    dbCheckError($query);
    return $query->fetch();
}

// Получить фото галереи авто
function selectCarImages($carId)
{
    global $pdo;
    $carId = intval($carId);
    $sql = "SELECT * FROM car_images WHERE id_car = :id_car ORDER BY sort_order ASC";
    $query = $pdo->prepare($sql);
    $query->execute(['id_car' => $carId]);
    dbCheckError($query);
    return $query->fetchAll();
}

// Добавить фото в галерею авто
function insertCarImage($carId, $img, $sortOrder = 0)
{
    global $pdo;
    $sql = "INSERT INTO car_images (id_car, img, sort_order) VALUES (:id_car, :img, :sort_order)";
    $query = $pdo->prepare($sql);
    $query->execute(['id_car' => $carId, 'img' => $img, 'sort_order' => $sortOrder]);
    dbCheckError($query);
    return $pdo->lastInsertId();
}

// Удалить фото из галереи
function deleteCarImage($id)
{
    global $pdo;
    $sql = "DELETE FROM car_images WHERE id = :id";
    $query = $pdo->prepare($sql);
    $query->execute(['id' => intval($id)]);
    dbCheckError($query);
}

// Поиск авто по названию и описанию
function searchCars($text)
{
    $text = trim(strip_tags(stripcslashes(htmlspecialchars($text))));
    global $pdo;
    $sql = "SELECT c.*, b.name AS brand_name
            FROM cars AS c
            JOIN brands AS b ON c.id_brand = b.id
            WHERE c.status = 1
            AND (c.title LIKE :text OR c.description LIKE :text OR b.name LIKE :text)
            ORDER BY c.created_date DESC";
    $query = $pdo->prepare($sql);
    $query->execute(['text' => "%$text%"]);
    dbCheckError($query);
    return $query->fetchAll();
}

// Получить все типы кузовов (для фильтра)
function getBodyTypes()
{
    global $pdo;
    $sql = "SELECT DISTINCT body_type FROM cars WHERE status = 1 AND body_type IS NOT NULL ORDER BY body_type";
    $query = $pdo->prepare($sql);
    $query->execute();
    dbCheckError($query);
    return $query->fetchAll(PDO::FETCH_COLUMN);
}