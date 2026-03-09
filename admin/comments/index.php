<?php
session_start();
include "../../path.php";
include "../../app/controllers/commentaries.php";

$search = trim($_GET['search'] ?? '');
if ($search !== '') {
    global $pdo;
    $pageCmd    = max(1, (int)($_GET['page'] ?? 1));
    $perPageCmd = 30;
    $offsetCmd  = ($pageCmd - 1) * $perPageCmd;
    $s = "%$search%";
    $cnt = $pdo->prepare("SELECT COUNT(*) FROM comments WHERE comment LIKE :s OR email LIKE :s2");
    $cnt->execute([':s'=>$s,':s2'=>$s]);
    $totalCommentsAdm = (int)$cnt->fetchColumn();
    $totalPagesCmd    = max(1, ceil($totalCommentsAdm / $perPageCmd));
    $q = $pdo->prepare("SELECT * FROM comments WHERE comment LIKE :s OR email LIKE :s2 ORDER BY id DESC LIMIT :lim OFFSET :off");
    $q->bindValue(':s',$s); $q->bindValue(':s2',$s);
    $q->bindValue(':lim',$perPageCmd,PDO::PARAM_INT); $q->bindValue(':off',$offsetCmd,PDO::PARAM_INT);
    $q->execute();
    $commentsForAdm = $q->fetchAll(PDO::FETCH_ASSOC);
}
?><!doctype html>
<html lang="ru">
<head>
    <meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0-beta2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.7.2/css/all.css">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Comfortaa:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <title>Админ — Комментарии | ChinaCars</title>
</head>
<body>
<?php include(SITE_ROOT . "/app/include/header-admin.php"); ?>
<div class="container">
    <?php include(SITE_ROOT . "/app/include/sidebar-admin.php"); ?>
    <div class="col-9">
        <h2>Управление комментариями</h2>
        <div class="d-flex gap-2 mb-3 align-items-center flex-wrap">
            <form method="get" action="" class="d-flex gap-2 flex-grow-1" style="max-width:450px">
                <input type="text" name="search" class="form-control" placeholder="?? Поиск по тексту, email..." value="<?= htmlspecialchars($search) ?>">
                <button class="btn btn-primary" type="submit"><i class="fas fa-search"></i></button>
                <?php if ($search): ?><a href="?" class="btn btn-outline-secondary"><i class="fas fa-times"></i></a><?php endif; ?>
            </form>
            <span class="text-muted small">Найдено: <?= $totalCommentsAdm ?></span>
        </div>
        <form action="index.php<?= $search ? '?search='.urlencode($search) : '' ?>" method="post" id="bulkForm">
            <div class="d-flex mb-3 align-items-center">
                <select name="bulk_action" class="form-select w-auto me-2" required>
                    <option value="">Выберите действие...</option>
                    <option value="publish">Опубликовать выбранные</option>
                    <option value="hide">Скрыть выбранные</option>
                    <option value="delete">Удалить выбранные</option>
                </select>
                <button type="submit" name="apply_bulk" class="btn btn-warning btn-sm">Применить</button>
            </div>
            <table class="table table-hover">
                <thead><tr><th><input type="checkbox" id="selectAll"></th><th>ID</th><th>Текст</th><th>Автор</th><th>Статус</th><th>Действия</th></tr></thead>
                <tbody>
                <?php foreach ($commentsForAdm as $comment): ?>
                <tr>
                    <td><input type="checkbox" name="selected_ids[]" value="<?= $comment['id'] ?>" class="rowCheckbox"></td>
                    <td><?= $comment['id'] ?></td>
                    <td><?= mb_strlen($comment['comment'],'UTF-8') > 60 ? htmlspecialchars(mb_substr($comment['comment'],0,60,'UTF-8')).'...' : htmlspecialchars($comment['comment']) ?></td>
                    <td><?= htmlspecialchars(explode('@',$comment['email'])[0]) ?>@</td>
                    <td><?php if ($comment['status']): ?>
                        <a href="edit.php?publish=0&pub_id=<?= $comment['id'] ?>" class="badge bg-success text-decoration-none">Опубликован</a>
                    <?php else: ?>
                        <a href="edit.php?publish=1&pub_id=<?= $comment['id'] ?>" class="badge bg-warning text-decoration-none">Скрыт</a>
                    <?php endif; ?></td>
                    <td>
                        <a href="edit.php?edit_id=<?= $comment['id'] ?>" class="btn btn-sm btn-primary"><i class="fas fa-edit"></i></a>
                        <a href="javascript:void(0)" data-href="edit.php?delete_id=<?= $comment['id'] ?>" class="btn btn-sm btn-danger delete-btn"><i class="fas fa-trash"></i></a>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php if (empty($commentsForAdm)): ?><tr><td colspan="6" class="text-center text-muted py-4">Ничего не найдено</td></tr><?php endif; ?>
                </tbody>
            </table>
        </form>
        <?php if ($totalPagesCmd > 1): ?>
        <nav class="mt-4"><ul class="pagination justify-content-center">
            <?php if ($pageCmd > 1): ?><li class="page-item"><a class="page-link" href="?page=<?= $pageCmd-1 ?><?= $search ? '&search='.urlencode($search) : '' ?>">&laquo;</a></li><?php endif; ?>
            <?php for ($i = max(1,$pageCmd-4); $i <= min($totalPagesCmd,$pageCmd+4); $i++): ?>
            <li class="page-item <?= ($i==$pageCmd)?'active':'' ?>"><a class="page-link" href="?page=<?= $i ?><?= $search ? '&search='.urlencode($search) : '' ?>"><?= $i ?></a></li>
            <?php endfor; ?>
            <?php if ($pageCmd < $totalPagesCmd): ?><li class="page-item"><a class="page-link" href="?page=<?= $pageCmd+1 ?><?= $search ? '&search='.urlencode($search) : '' ?>">&raquo;</a></li><?php endif; ?>
        </ul></nav>
        <?php endif; ?>
    </div>
</div>
</div>
<div id="deleteOverlay" style="display:none;position:fixed;top:0;left:0;width:100%;height:100%;background:rgba(0,0,0,0.7);z-index:999999;justify-content:center;align-items:center;">
  <div style="background:#0a0a0a;border:2px solid #0f0;border-radius:8px;padding:30px;min-width:320px;text-align:center;color:#0f0;">
    <h4 style="margin:0 0 20px;color:#0f0;">Подтверждение</h4>
    <p style="margin:0 0 25px;color:#0f0;">Удалить комментарий?</p>
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