<?php
include "../../path.php";
include SITE_ROOT . "/app/controllers/cars.php";
include SITE_ROOT . "/app/controllers/users.php";

if (!isset($_GET['id']) && !isset($_POST['id'])) {
    header('location: ' . BASE_URL . 'admin/cars/index.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['id'])) {
    $car = selectOne('cars', ['id' => $_GET['id']]);
    $carImages = selectCarImages($_GET['id']);
} elseif (isset($_POST['id'])) {
    $car = selectOne('cars', ['id' => $_POST['id']]);
    $carImages = selectCarImages($_POST['id']);
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
    <link href="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.13/cropper.min.css" rel="stylesheet">
    <title>Редактировать авто | AvtoTachka</title>
</head>

<body>

    <?php include(SITE_ROOT . "/app/include/header-admin.php"); ?>
    <div class="container">
        <?php include(SITE_ROOT . "/app/include/sidebar-admin.php"); ?>
        <div class="col-9">
            <h2>Редактировать:
                <?= $car['title'] ?>
            </h2>

            <?php if (!empty($errMsg)): ?>
                <div class="alert alert-danger">
                    <?php foreach ($errMsg as $err): ?>
                        <p>
                            <?= $err ?>
                        </p>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <form action="edit.php" method="post" enctype="multipart/form-data">
                <?= csrfField() ?>
                <input type="hidden" name="id" value="<?= $car['id'] ?>">
                <input type="hidden" name="current_img" value="<?= $car['img'] ?>">

                <div class="row">
                    <div class="mb-3 col-md-8">
                        <label class="form-label">Название авто *</label>
                        <input type="text" name="title" class="form-control" value="<?= $car['title'] ?>" required>
                    </div>
                    <div class="mb-3 col-md-4">
                        <label class="form-label">Бренд *</label>
                        <select name="brand" class="form-select" required>
                            <?php foreach ($brands as $b): ?>
                                <option value="<?= $b['id'] ?>" <?= ($car['id_brand'] == $b['id']) ? 'selected' : '' ?>>
                                    <?= $b['name'] ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div class="row">
                    <div class="mb-3 col-md-4">
                        <label class="form-label">Цена (&#8381;)</label>
                        <input type="number" name="price" class="form-control" step="0.01" value="<?= $car['price'] ?>">
                    </div>
                    <div class="mb-3 col-md-4">
                        <label class="form-label">Год выпуска</label>
                        <input type="number" name="year" class="form-control" value="<?= $car['year'] ?>" min="2000"
                            max="2030">
                    </div>
                    <div class="mb-3 col-md-4">
                        <label class="form-label">Пробег (км)</label>
                        <input type="number" name="mileage" class="form-control" value="<?= $car['mileage'] ?>">
                    </div>
                </div>

                <div class="row">
                    <div class="mb-3 col-md-3">
                        <label class="form-label">Тип двигателя</label>
                        <select name="engine_type" class="form-select">
                            <?php foreach (['Бензин', 'Дизель', 'Электро', 'Гибрид'] as $et): ?>
                                <option value="<?= $et ?>" <?= ($car['engine_type'] == $et) ? 'selected' : '' ?>>
                                    <?= $et ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3 col-md-3">
                        <label class="form-label">Объём (л)</label>
                        <input type="number" name="engine_volume" class="form-control" step="0.1"
                            value="<?= $car['engine_volume'] ?>">
                    </div>
                    <div class="mb-3 col-md-3">
                        <label class="form-label">Мощность (л.с.)</label>
                        <input type="number" name="horsepower" class="form-control" value="<?= $car['horsepower'] ?>">
                    </div>
                    <div class="mb-3 col-md-3">
                        <label class="form-label">КПП</label>
                        <select name="transmission" class="form-select">
                            <?php foreach (['АКПП', 'МКПП', 'Робот', 'Вариатор'] as $tr): ?>
                                <option value="<?= $tr ?>" <?= ($car['transmission'] == $tr) ? 'selected' : '' ?>>
                                    <?= $tr ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div class="row">
                    <div class="mb-3 col-md-4">
                        <label class="form-label">Привод</label>
                        <select name="drive_type" class="form-select">
                            <?php foreach (['Передний', 'Задний', 'Полный'] as $dt): ?>
                                <option value="<?= $dt ?>" <?= ($car['drive_type'] == $dt) ? 'selected' : '' ?>>
                                    <?= $dt ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3 col-md-4">
                        <label class="form-label">Тип кузова</label>
                        <select name="body_type" class="form-select">
                            <?php foreach (['Кроссовер', 'Седан', 'Хэтчбек', 'Лифтбек', 'Универсал', 'Внедорожник', 'Минивэн'] as $bt): ?>
                                <option value="<?= $bt ?>" <?= ($car['body_type'] == $bt) ? 'selected' : '' ?>>
                                    <?= $bt ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3 col-md-4">
                        <label class="form-label">Цвет</label>
                        <input type="text" name="color" class="form-control" value="<?= $car['color'] ?>">
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label">Описание</label>
                    <textarea name="description" class="form-control" rows="5"><?= $car['description'] ?></textarea>
                </div>

                <div class="mb-3">
                    <label class="form-label">Текущее фото</label>
                    <div>
                        <?php if ($car['img']): ?>
                            <img src="<?= BASE_URL ?>assets/images/cars/<?= $car['img'] ?>" width="200"
                                class="img-thumbnail" alt="">
                        <?php else: ?>
                            <p>Нет фото</p>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label">Заменить главное фото (выберите файл для обрезки)</label>
                    <input type="file" id="imgInput" name="img" class="form-control" accept="image/*">
                    <input type="hidden" name="cropped_img" id="cropped_img">
                </div>

                <div class="mb-3" id="cropper-container"
                    style="display:none; max-width: 100%; height: 400px; background: #eee;">
                    <img id="image-to-crop" src="" style="max-width: 100%;">
                </div>

                <!-- Текущая галерея -->
                <?php if (!empty($carImages)): ?>
                    <div class="mb-3">
                        <label class="form-label">Фотогалерея (перетащите фото для изменения порядка)</label>
                        <div class="row" id="sortableGallery" style="margin-left: -5px; margin-right: -5px;">
                            <?php foreach ($carImages as $image): ?>
                                <div class="col-6 col-md-3 mb-3 text-center gallery-item" data-id="<?= $image['id'] ?>"
                                    style="cursor: grab; padding: 5px;">
                                    <div class="card h-100 p-2 shadow-sm"
                                        style="background-color: var(--card-bg); border-color: var(--border-color);">
                                        <img src="<?= BASE_URL ?>assets/images/cars/<?= $image['img'] ?>"
                                            class="img-fluid rounded mb-2" alt=""
                                            style="object-fit: cover; height: 120px; width: 100%;">
                                        <a href="<?= BASE_URL ?>admin/cars/index.php?del_img_id=<?= $image['id'] ?>&car_id=<?= $car['id'] ?>"
                                            class="btn btn-sm btn-danger mt-auto mx-auto w-75"
                                            onclick="return confirm('Удалить фото?')"><i class="fas fa-trash"></i> Удалить</a>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                        <div id="sortErrorBlock" class="text-danger mt-2" style="display: none;"></div>
                    </div>
                <?php endif; ?>

                <div class="mb-3">
                    <label class="form-label">Добавить фото в галерею</label>
                    <input type="file" name="gallery[]" class="form-control" accept="image/*" multiple>
                </div>

                <div class="form-check mb-3">
                    <input class="form-check-input" type="checkbox" name="publish" id="publish" <?= ($car['status'] == 1) ? 'checked' : '' ?>>
                    <label class="form-check-label" for="publish">Опубликовать</label>
                </div>

                <div class="form-check mb-3">
                    <input class="form-check-input" type="checkbox" name="featured" id="featured"
                        <?= (!empty($car['featured']) && $car['featured'] == 1) ? 'checked' : '' ?>>
                    <label class="form-check-label" for="featured">Показывать в карусели (лучшие предложения)</label>
                </div>

                <input type="hidden" name="edit_car" value="1">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> Сохранить
                </button>
                <a href="<?= BASE_URL ?>admin/cars/index.php" class="btn btn-secondary">Отмена</a>
            </form>
        </div>
    </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.13/cropper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sortablejs@latest/Sortable.min.js"></script>
    <script>
        // Sortable Gallery
        const sortableGallery = document.getElementById('sortableGallery');
        if (sortableGallery) {
            new Sortable(sortableGallery, {
                animation: 150,
                ghostClass: 'bg-light',
                onEnd: function () {
                    const itemEls = sortableGallery.querySelectorAll('.gallery-item');
                    let order = [];
                    itemEls.forEach((el, index) => {
                        order.push({
                            id: el.getAttribute('data-id'),
                            sort_order: index
                        });
                    });

                    // AJAX для сохранения сортировки
                    fetch('<?= BASE_URL ?>admin/cars/ajax_sort_gallery.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json'
                        },
                        body: JSON.stringify({ order: order })
                    })
                        .then(response => response.json())
                        .then(data => {
                            if (!data.success) {
                                const errBlock = document.getElementById('sortErrorBlock');
                                errBlock.textContent = "Ошибка при сохранении порядка: " + (data.error || 'Неизвестная ошибка');
                                errBlock.style.display = 'block';
                                setTimeout(() => errBlock.style.display = 'none', 3000);
                            }
                        })
                        .catch(error => console.error('Error:', error));
                }
            });
        }

        // Cropper
        let cropper;
        const imgInput = document.getElementById('imgInput');
        const imageToCrop = document.getElementById('image-to-crop');
        const cropperContainer = document.getElementById('cropper-container');
        const croppedImgInput = document.getElementById('cropped_img');
        const form = document.querySelector('form');

        imgInput.addEventListener('change', function (e) {
            const files = e.target.files;
            if (files && files.length > 0) {
                const file = files[0];
                const reader = new FileReader();
                reader.onload = function (event) {
                    imageToCrop.src = event.target.result;
                    cropperContainer.style.display = 'block';
                    if (cropper) {
                        cropper.destroy();
                    }
                    cropper = new Cropper(imageToCrop, {
                        aspectRatio: 4 / 3,
                        viewMode: 1,
                        autoCropArea: 1,
                    });
                };
                reader.readAsDataURL(file);
            }
        });

        form.addEventListener('submit', function (e) {
            if (cropper) {
                e.preventDefault();
                const canvas = cropper.getCroppedCanvas({
                    width: 800,
                    height: 600,
                });
                croppedImgInput.value = canvas.toDataURL('image/jpeg', 0.7);
                // очищаем оригинальный input file чтобы не отправлять большой файл
                const dataTransfer = new DataTransfer();
                imgInput.files = dataTransfer.files;
                cropper.destroy();
                cropper = null;
                form.submit();
            }
        });
    </script>
</body>

</html>