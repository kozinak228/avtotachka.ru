<?php
include "../../path.php";
include SITE_ROOT . "/app/controllers/brands.php";
include SITE_ROOT . "/app/controllers/users.php";
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
    <title>Добавить бренд | AvtoTachka</title>
</head>

<body>

    <?php include(SITE_ROOT . "/app/include/header-admin.php"); ?>
    <div class="container">
        <?php include(SITE_ROOT . "/app/include/sidebar-admin.php"); ?>
        <div class="col-9">
            <h2>Добавить бренд</h2>

            <?php if ($errMsg): ?>
                <div class="alert alert-danger">
                    <?= $errMsg ?>
                </div>
            <?php endif; ?>

            <form action="create.php" method="post" enctype="multipart/form-data">
                <div class="mb-3">
                    <label class="form-label">Название бренда *</label>
                    <input type="text" name="name" class="form-control" value="<?= $name ?>" required
                        placeholder="Например: Chery">
                </div>
                <div class="mb-3">
                    <label class="form-label">Страна</label>
                    <input type="text" name="country" class="form-control" value="Китай">
                </div>
                <div class="mb-3">
                    <label class="form-label">Логотип</label>
                    <input type="file" name="logo" class="form-control" accept="image/*">
                </div>
                <button type="submit" name="brand-create" class="btn btn-success">
                    <i class="fas fa-plus"></i> Добавить
                </button>
                <a href="<?= BASE_URL ?>admin/brands/index.php" class="btn btn-secondary">Отмена</a>
            </form>
        </div>
    </div>
    </div>

</body>

</html>