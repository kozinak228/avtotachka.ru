<?php
session_start();
include "../../path.php";
include "../../app/controllers/users.php";

if (!isset($_SESSION['admin']) || !$_SESSION['admin']) {
    header('location: ' . BASE_URL); exit;
}
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['apply_bulk'])) {
    $ids = $_POST['selected_ids'] ?? [];
    if (!empty($ids) && $_POST['bulk_action'] === 'delete') {
        foreach ($ids as $uid) { delete('users', (int)$uid); }
    }
    header('location: ' . BASE_URL . 'admin/users/index.php'); exit;
}
if (isset($_GET['delete_id'])) {
    delete('users', (int)$_GET['delete_id']);
    header('location: ' . BASE_URL . 'admin/users/index.php'); exit;
}

global $pdo;
$search  = trim($_GET['search'] ?? '');
$perPage = 15;
$page    = max(1, (int)($_GET['page'] ?? 1));
$offset  = ($page - 1) * $perPage;
$where   = $search ? "WHERE username LIKE :s OR email LIKE :s2" : "";
$params  = $search ? [':s'=>"%$search%",':s2'=>"%$search%"] : [];

$cnt = $pdo->prepare("SELECT COUNT(*) FROM users $where");
$cnt->execute($params);
$totalCount = (int)$cnt->fetchColumn();
$totalPages = max(1, ceil($totalCount / $perPage));

$stmt = $pdo->prepare("SELECT * FROM users $where ORDER BY id DESC LIMIT :lim OFFSET :off");
foreach ($params as $k=>$v) $stmt->bindValue($k,$v);
$stmt->bindValue(':lim',$perPage,PDO::PARAM_INT);
$stmt->bindValue(':off',$offset,PDO::PARAM_INT);
$stmt->execute();
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);
?><!doctype html>
<html lang="ru">
<head>
    <meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0-beta2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.7.2/css/all.css">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Comfortaa:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <title>Админ — Пользователи | AvtoTachka</title>
</head>
<body>
<?php include(SITE_ROOT . "/app/include/header-admin.php"); ?>
<div class="container">
    <?php include(SITE_ROOT . "/app/include/sidebar-admin.php"); ?>
    <div class="col-9">
        <h2>Управление пользователями</h2>
        <div class="d-flex gap-2 mb-3 align-items-center flex-wrap">
            <a href="<?php echo BASE_URL; ?>admin/users/create.php" class="btn btn-success"><i class="fas fa-plus"></i> Добавить</a>
            <form method="get" action="" class="d-flex gap-2 flex-grow-1" style="max-width:420px">
                <input type="text" name="search" class="form-control" placeholder="?? Логин или email..." value="<?= htmlspecialchars($search) ?>">
                <button class="btn btn-primary" type="submit"><i class="fas fa-search"></i></button>
                <?php if ($search): ?><a href="?" class="btn btn-outline-secondary"><i class="fas fa-times"></i></a><?php endif; ?>
            </form>
            <span class="text-muted small">Найдено: <?= $totalCount ?></span>
        </div>
        <form action="index.php<?= $search ? '?search='.urlencode($search) : '' ?>" method="post" id="bulkForm">
            <div class="d-flex mb-3 align-items-center">
                <select name="bulk_action" class="form-select w-auto me-2" required>
                    <option value="">Выберите действие...</option>
                    <option value="delete">Удалить выбранных</option>
                </select>
                <button type="submit" name="apply_bulk" class="btn btn-warning btn-sm">Применить</button>
            </div>
            <table class="table table-hover">
                <thead><tr><th><input type="checkbox" id="selectAll"></th><th>ID</th><th>Логин</th><th>Email</th><th>Роль</th><th>Действия</th></tr></thead>
                <tbody>
                <?php foreach ($users as $user): ?>
                <tr>
                    <td><input type="checkbox" name="selected_ids[]" value="<?= $user['id'] ?>" class="rowCheckbox"></td>
                    <td><?= $user['id'] ?></td>
                    <td><?= htmlspecialchars($user['username']) ?></td>
                    <td><?= htmlspecialchars($user['email'] ?? '') ?></td>
                    <td><?php if ($user['admin']==1): ?><span class="badge bg-success">Админ</span><?php else: ?><span class="badge bg-secondary">Пользователь</span><?php endif; ?></td>
                    <td>
                        <a href="edit.php?edit_id=<?= $user['id'] ?>" class="btn btn-sm btn-primary"><i class="fas fa-edit"></i></a>
                        <a href="javascript:void(0)" data-href="index.php?delete_id=<?= $user['id'] ?>" class="btn btn-sm btn-danger delete-btn"><i class="fas fa-trash"></i></a>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php if (empty($users)): ?><tr><td colspan="6" class="text-center text-muted py-4">Ничего не найдено</td></tr><?php endif; ?>
                </tbody>
            </table>
        </form>
        <?php if ($totalPages > 1): ?>
        <nav class="mt-4"><ul class="pagination justify-content-center">
            <?php if ($page > 1): ?><li class="page-item"><a class="page-link" href="?page=<?= $page-1 ?><?= $search ? '&search='.urlencode($search) : '' ?>">&laquo;</a></li><?php endif; ?>
            <?php for ($i = max(1,$page-3); $i <= min($totalPages,$page+3); $i++): ?>
            <li class="page-item <?= ($i==$page)?'active':'' ?>"><a class="page-link" href="?page=<?= $i ?><?= $search ? '&search='.urlencode($search) : '' ?>"><?= $i ?></a></li>
            <?php endfor; ?>
            <?php if ($page < $totalPages): ?><li class="page-item"><a class="page-link" href="?page=<?= $page+1 ?><?= $search ? '&search='.urlencode($search) : '' ?>">&raquo;</a></li><?php endif; ?>
        </ul></nav>
        <?php endif; ?>
    </div>
</div>
</div>
<div id="deleteOverlay" style="display:none;position:fixed;top:0;left:0;width:100%;height:100%;background:rgba(0,0,0,0.7);z-index:999999;justify-content:center;align-items:center;">
  <div style="background:#0a0a0a;border:2px solid #0f0;border-radius:8px;padding:30px;min-width:320px;text-align:center;color:#0f0;">
    <h4 style="margin:0 0 20px;color:#0f0;">Подтверждение</h4>
    <p style="margin:0 0 25px;color:#0f0;">Удалить пользователя?</p>
    <button id="cancelDeleteBtn" style="padding:8px 24px;margin:0 8px;background:transparent;border:1px solid #888;color:#ccc;border-radius:4px;cursor:pointer;">Отмена</button>
    <button id="doDeleteBtn" style="padding:8px 24px;margin:0 8px;background:#dc3545;border:1px solid #dc3545;color:#fff;border-radius:4px;cursor:pointer;">Удалить</button>
  </div>
</div>
<script>
document.addEventListener('DOMContentLoaded',function(){
    var s=document.getElementById('selectAll'),cb=document.querySelectorAll('.rowCheckbox');
    if(s)s.addEventListener('change',function(){cb.forEach(function(c){c.checked=s.checked;});});
    var overlay=document.getElementById('deleteOverlay'),deleteUrl='';
    document.querySelectorAll('.delete-btn').forEach(function(btn){
        btn.addEventListener('click',function(e){e.preventDefault();e.stopPropagation();deleteUrl=this.getAttribute('data-href');overlay.style.display='flex';});
    });
    document.getElementById('cancelDeleteBtn').addEventListener('click',function(){overlay.style.display='none';deleteUrl='';});
    document.getElementById('doDeleteBtn').addEventListener('click',function(){if(deleteUrl)window.location.href=deleteUrl;});
    overlay.addEventListener('click',function(e){if(e.target===overlay){overlay.style.display='none';deleteUrl='';}});
});
</script>
</body>
</html>