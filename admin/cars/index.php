<?php
session_start();
include "../../path.php";
include SITE_ROOT . "/app/controllers/cars.php";
include SITE_ROOT . "/app/controllers/users.php";

$search = trim($_GET['search'] ?? '');
if ($search !== '') {
    global $pdo;
    $page    = max(1, (int)($_GET['page'] ?? 1));
    $perPage = 10;
    $offset  = ($page - 1) * $perPage;
    $s = "%$search%";
    $cnt = $pdo->prepare("SELECT COUNT(*) FROM cars c LEFT JOIN brands b ON c.id_brand=b.id WHERE c.title LIKE :s OR b.name LIKE :s2");
    $cnt->execute([':s'=>$s,':s2'=>$s]);
    $totalCarsAdm  = (int)$cnt->fetchColumn();
    $totalPagesAdm = max(1, ceil($totalCarsAdm / $perPage));
    $q = $pdo->prepare("SELECT c.*, b.name as brand_name, u.username FROM cars c LEFT JOIN brands b ON c.id_brand=b.id LEFT JOIN users u ON c.id_user=u.id WHERE c.title LIKE :s OR b.name LIKE :s2 ORDER BY c.id DESC LIMIT :lim OFFSET :off");
    $q->bindValue(':s',$s); $q->bindValue(':s2',$s);
    $q->bindValue(':lim',$perPage,PDO::PARAM_INT); $q->bindValue(':off',$offset,PDO::PARAM_INT);
    $q->execute();
    $carsAdm = $q->fetchAll(PDO::FETCH_ASSOC);
}
?><!doctype html>
<html lang="ru">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0-beta2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.7.2/css/all.css">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Comfortaa:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <title>Админ — Автомобили | AvtoTachka</title>
</head>
<body>
<?php include(SITE_ROOT . "/app/include/header-admin.php"); ?>
<div class="container">
    <?php include(SITE_ROOT . "/app/include/sidebar-admin.php"); ?>
    <div class="col-9">
        <h2>Управление автомобилями</h2>
        <div class="d-flex gap-2 mb-3 align-items-center flex-wrap">
            <a href="<?php echo BASE_URL; ?>admin/cars/create.php" class="btn btn-success">
                <i class="fas fa-plus"></i> Добавить авто
            </a>
            <form method="get" action="" class="d-flex gap-2 flex-grow-1" style="max-width:450px">
                <input type="text" name="search" class="form-control" placeholder="?? Поиск по названию, бренду..." value="<?= htmlspecialchars($search) ?>">
                <button class="btn btn-primary" type="submit"><i class="fas fa-search"></i></button>
                <?php if ($search): ?><a href="?" class="btn btn-outline-secondary"><i class="fas fa-times"></i></a><?php endif; ?>
            </form>
            <span class="text-muted small">Найдено: <?= $totalCarsAdm ?></span>
        </div>
        <form action="index.php<?= $search ? '?search='.urlencode($search) : '' ?>" method="post" id="bulkForm">
            <div class="d-flex mb-3 align-items-center">
                <select name="bulk_action" class="form-select w-auto me-2" required>
                    <option value="">Выберите действие...</option>
                    <option value="publish">Опубликовать выбранные</option>
                    <option value="draft">Убрать в черновик</option>
                    <option value="delete">Удалить выбранные</option>
                </select>
                <button type="submit" name="apply_bulk" class="btn btn-warning btn-sm">Применить</button>
            </div>
            <table class="table table-hover">
                <thead><tr>
                    <th><input type="checkbox" id="selectAll"></th>
                    <th>ID</th><th>Фото</th><th>Название</th><th>Бренд</th>
                    <th>Цена</th><th>Добавил</th><th>Статус</th><th>Действия</th>
                </tr></thead>
                <tbody>
                <?php foreach ($carsAdm as $car): ?>
                <tr>
                    <td><input type="checkbox" name="selected_ids[]" value="<?= $car['id'] ?>" class="rowCheckbox"></td>
                    <td><?= $car['id'] ?></td>
                    <td><?php if ($car['img']): ?><img src="<?= BASE_URL ?>assets/images/cars/<?= $car['img'] ?>" width="60" alt=""><?php else: ?><i class="fas fa-car"></i><?php endif; ?></td>
                    <td><?= htmlspecialchars($car['title']) ?></td>
                    <td><?= htmlspecialchars($car['brand_name'] ?? '') ?></td>
                    <td><?= number_format($car['price'], 0, '', ' ') ?> &#8381;</td>
                    <td><?= htmlspecialchars($car['username'] ?? '') ?></td>
                    <td>
                        <?php if ($car['status'] == 1): ?>
                            <a href="<?= BASE_URL ?>admin/cars/index.php?pub_id=<?= $car['id'] ?>&publish=0" class="badge bg-success text-decoration-none">Опубликован</a>
                        <?php else: ?>
                            <a href="<?= BASE_URL ?>admin/cars/index.php?pub_id=<?= $car['id'] ?>&publish=1" class="badge bg-warning text-decoration-none">Черновик</a>
                        <?php endif; ?>
                    </td>
                    <td>
                        <a href="<?= BASE_URL ?>admin/cars/edit.php?id=<?= $car['id'] ?>" class="btn btn-sm btn-primary"><i class="fas fa-edit"></i></a>
                        <a href="javascript:void(0)" data-href="<?= BASE_URL ?>admin/cars/index.php?delete_id=<?= $car['id'] ?>" class="btn btn-sm btn-danger delete-btn"><i class="fas fa-trash"></i></a>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php if (empty($carsAdm)): ?><tr><td colspan="9" class="text-center text-muted py-4">Ничего не найдено</td></tr><?php endif; ?>
                </tbody>
            </table>
        </form>
        <?php if ($totalPagesAdm > 1): ?>
        <nav class="mt-4"><ul class="pagination justify-content-center">
            <?php if ($page > 1): ?><li class="page-item"><a class="page-link" href="?page=<?= $page-1 ?><?= $search ? '&search='.urlencode($search) : '' ?>">&laquo;</a></li><?php endif; ?>
            <?php for ($i = max(1,$page-3); $i <= min($totalPagesAdm,$page+3); $i++): ?>
            <li class="page-item <?= ($i==$page)?'active':'' ?>"><a class="page-link" href="?page=<?= $i ?><?= $search ? '&search='.urlencode($search) : '' ?>"><?= $i ?></a></li>
            <?php endfor; ?>
            <?php if ($page < $totalPagesAdm): ?><li class="page-item"><a class="page-link" href="?page=<?= $page+1 ?><?= $search ? '&search='.urlencode($search) : '' ?>">&raquo;</a></li><?php endif; ?>
        </ul></nav>
        <?php endif; ?>
    </div>
</div>
</div>
<div id="deleteOverlay" style="display:none;position:fixed;top:0;left:0;width:100%;height:100%;background:rgba(0,0,0,0.7);z-index:999999;justify-content:center;align-items:center;">
  <div style="background:#0a0a0a;border:2px solid #0f0;border-radius:8px;padding:30px;min-width:320px;text-align:center;color:#0f0;">
    <h4 style="margin:0 0 20px;color:#0f0;">Подтверждение</h4>
    <p style="margin:0 0 25px;color:#0f0;">Удалить автомобиль?</p>
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