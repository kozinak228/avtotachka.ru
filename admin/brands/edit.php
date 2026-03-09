<?php
include "../../path.php";
include SITE_ROOT . "/app/controllers/brands.php";
include SITE_ROOT . "/app/controllers/users.php";

if (!isset($_GET['id']) && !isset($_POST['id'])) {
    header('location: ' . BASE_URL . 'admin/brands/index.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['id'])) {
    $brand = selectOne('brands', ['id' => $_GET['id']]);
}
?>
<!doctype html>
<html lang="ru">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0-beta2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.7.2/css/all.css">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Comfortaa:wght@300;400;500;600;700&display=swap"
        rel="stylesheet">
    <title>Редактировать бренд | AvtoTachka</title>
</head>

<body>

    <?php include(SITE_ROOT . "/app/include/header-admin.php"); ?>
    <div class="container">
        <?php include(SITE_ROOT . "/app/include/sidebar-admin.php"); ?>
        <div class="col-9">
            <h2>Редактировать бренд:
                <?= $brand['name'] ?>
            </h2>

            <?php if ($errMsg): ?>
                <div class="alert alert-danger">
                    <?= $errMsg ?>
                </div>
            <?php endif; ?>

            <form action="edit.php" method="post" enctype="multipart/form-data">
                <input type="hidden" name="id" value="<?= $brand['id'] ?>">
                <input type="hidden" name="current_logo" value="<?= $brand['logo'] ?>">

                <div class="mb-3">
                    <label class="form-label">Название бренда *</label>
                    <input type="text" name="name" class="form-control" value="<?= $brand['name'] ?>" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Страна</label>
                    <input type="text" name="country" class="form-control" value="<?= $brand['country'] ?>">
                </div>
                <div class="mb-3">
                    <label class="form-label">Текущий логотип</label>
                    <div>
                        <?php if ($brand['logo']): ?>
                            <img src="<?= BASE_URL ?>assets/images/brands/<?= $brand['logo'] ?>" width="80"
                                class="img-thumbnail" alt="">
                        <?php else: ?>
                            <p>Нет логотипа</p>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label">Заменить логотип</label>
                    <input type="file" name="logo" class="form-control" accept="image/*">
                </div>
                <button type="submit" name="brand-edit" class="btn btn-primary">
                    <i class="fas fa-save"></i> Сохранить
                </button>
                <a href="<?= BASE_URL ?>admin/brands/index.php" class="btn btn-secondary">Отмена</a>
            </form>
        </div>
    </div>
    </div>

</body>

</html>