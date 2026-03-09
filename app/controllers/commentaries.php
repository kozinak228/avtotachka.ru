<?php
// Контроллер комментариев
include_once SITE_ROOT . "/app/database/db.php";

// Bulk actions from admin
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['apply_bulk']) && isset($_POST['bulk_action'])) {
    if (!isset($_SESSION['admin']) || !$_SESSION['admin']) {
        header('location: ' . BASE_URL);
        exit;
    }
    $ids = $_POST['selected_ids'] ?? [];
    $action = $_POST['bulk_action'];
    if (!empty($ids)) {
        foreach ($ids as $cid) {
            $cid = (int)$cid;
            if ($action === 'delete') {
                delete('comments', $cid);
            }
            elseif ($action === 'publish') {
                update('comments', $cid, ['status' => 1]);
            }
            elseif ($action === 'hide') {
                update('comments', $cid, ['status' => 0]);
            }
        }
    }
    header('location: ' . BASE_URL . 'admin/comments/index.php');
    exit;
}

// Инициализация переменных
$page = (int)($_GET['id'] ?? $_GET['post'] ?? 0);
$email = '';
$comment = '';
$errMsg = [];
$comments = [];

// ── 1. СОЗДАНИЕ КОММЕНТАРИЯ ──────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['goComment'])) {

    // page берём из скрытого поля формы (надёжнее, чем GET)
    if (!empty($_POST['page'])) {
        $page = (int)$_POST['page'];
    }

    $email = trim($_POST['email'] ?? '');
    $comment = trim($_POST['comment'] ?? '');

    // Если залогинен и email не прислан — берём логин из сессии
    if ($email === '' && isset($_SESSION['login'])) {
        $email = $_SESSION['login'];
    }

    if ($email === '' || $comment === '') {
        $errMsg[] = "Не все поля заполнены!";
    }
    elseif (mb_strlen($comment, 'UTF-8') < 10) {
        $errMsg[] = "Комментарий должен быть длиннее 10 символов";
    }
    else {
        insert('comments', [
            'status' => 1,
            'page' => $page,
            'email' => $email,
            'comment' => $comment,
        ]);
        // После вставки — редирект, чтобы не задвоить при F5
        header('location: ' . BASE_URL . 'single.php?id=' . $page . '#comments');
        exit;
    }
}

// ── 2. ЗАГРУЗКА КОММЕНТАРИЕВ ─────────────────────────────────────────────────
$comments = selectAll('comments', ['page' => $page, 'status' => 1]);

// ── 3. УДАЛЕНИЕ (admin) ──────────────────────────────────────────────────────
if (isset($_POST['delete_id']) || isset($_GET['delete_id'])) {
    if (!isset($_SESSION['admin']) || !$_SESSION['admin']) {
        header('location: ' . BASE_URL);
        exit;
    }
    $deleteId = (int)($_POST['delete_id'] ?? $_GET['delete_id']);
    delete('comments', $deleteId);
    header('location: ' . BASE_URL . 'admin/comments/index.php');
    exit;
}

// ── 4. ПУБЛИКАЦИЯ / СНЯТИЕ (admin) ───────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['pub_id'])) {
    update('comments', (int)$_GET['pub_id'], ['status' => (int)$_GET['publish']]);
    header('location: ' . BASE_URL . 'admin/comments/index.php');
    exit;
}

// ── 5. ПОЛУЧЕНИЕ ДАННЫХ ДЛЯ РЕДАКТИРОВАНИЯ (admin) ──────────────────────────
$id = 0;
$text1 = '';
$pub = 0;
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['edit_id'])) {
    $oneComment = selectOne('comments', ['id' => (int)$_GET['edit_id']]);
    if ($oneComment) {
        $id = $oneComment['id'];
        $email = $oneComment['email'];
        $text1 = $oneComment['comment'];
        $pub = $oneComment['status'];
    }
}

// ── 6. СОХРАНЕНИЕ РЕДАКТИРОВАНИЯ (admin) ─────────────────────────────────────
$text = '';
$publish = 0;
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_comment'])) {
    $id = (int)$_POST['id'];
    $text = trim($_POST['content'] ?? '');
    $publish = isset($_POST['publish']) ? 1 : 0;

    if ($text === '') {
        $errMsg[] = "Комментарий не имеет содержимого текста";
    }
    else {
        update('comments', $id, ['comment' => $text, 'status' => $publish]);
        header('location: ' . BASE_URL . 'admin/comments/index.php');
        exit;
    }
}

// Для admin-панели: все комментарии с пагинацией
$pageCmd = isset($_GET['page']) ? intval($_GET['page']) : 1;
if ($pageCmd < 1)
    $pageCmd = 1;
$perPageCmd = 30;
$offsetCmd = ($pageCmd - 1) * $perPageCmd;

$totalCommentsAdm = countRow('comments');
$totalPagesCmd = ceil($totalCommentsAdm / $perPageCmd);

global $pdo;
$sql = "SELECT * FROM comments ORDER BY id DESC LIMIT $perPageCmd OFFSET $offsetCmd";
$query = $pdo->prepare($sql);
$query->execute();
$commentsForAdm = $query->fetchAll();