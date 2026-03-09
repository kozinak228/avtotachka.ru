<?php session_start();
include "../../path.php";
include "../../app/controllers/users.php";
?>
<!doctype html>
<html lang="en">

<head>
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0-beta2/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-BmbxuPwQa2lc/FVzBcNJ7UAyJxM6wuqIj61tLrc4wSX0szH/Ev+nYRRuWlolflfl" crossorigin="anonymous">

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.7.2/css/all.css"
        integrity="sha384-fnmOCqbTlWIlj8LyTjo7mOUStjsKC4pOpQbqyi7RrhN7udi9RwhKkMHpvLbHG9Sr" crossorigin="anonymous">

    <!-- Custom Styling -->
    <link rel="stylesheet" href="../../assets/css/admin.css">
    <link rel="preconnect" href="https://fonts.gstatic.com">
    <link href="https://fonts.googleapis.com/css2?family=Comfortaa:wght@300;400;500;600;700&display=swap"
        rel="stylesheet">
    <title>Админ — ChinaCars</title>
</head>

<body>

    <?php include("../../app/include/header-admin.php"); ?>

    <div class="container">
        <?php include "../../app/include/sidebar-admin.php"; ?>

        <div class="posts col-9">
            <div class="button row">
                <a href="<?php echo BASE_URL . "admin/users/create.php"; ?>" class="col-2 btn btn-success">Создать</a>
                <span class="col-1"></span>
                <a href="<?php echo BASE_URL . "admin/users/index.php"; ?>"
                    class="col-3 btn btn-warning">Редактировать</a>
            </div>
            <div class="row title-table">
                <h2>Редактировать пользователя</h2>
            </div>
            <div class="row add-post">
                <div class="mb-12 col-12 col-md-12 err">
                    <!-- Вывод массива с ошибками -->
                    <?php include "../../app/helps/errorInfo.php"; ?>
                </div>
                <form action="edit.php" method="post">
                    <?= csrfField(); ?>
                    <input name="id" value="<?= $id; ?>" type="hidden">
                    <div class="col">
                        <label for="formGroupExampleInput" class="form-label">Логин</label>
                        <input name="login" value="<?= htmlspecialchars($username); ?>" type="text" class="form-control"
                            id="formGroupExampleInput" placeholder="введите ваш логин...">
                    </div>
                    <div class="col">
                        <label for="exampleInputEmail1" class="form-label">Email</label>
                        <input name="mail" value="<?= htmlspecialchars($email); ?>" type="email" class="form-control" readonly
                            id="exampleInputEmail1" aria-describedby="emailHelp" placeholder="введите ваш email...">
                    </div>
                    <div class="col mt-2 mb-2">
                        <label for="exampleInputPassword1" class="form-label">Сбросить пароль</label>
                        <div class="input-group">
                            <input name="pass-first" type="password" class="form-control" id="exampleInputPassword1"
                                placeholder="введите ваш пароль...">
                            <button class="btn btn-outline-secondary toggle-password" type="button"
                                data-target="#exampleInputPassword1">
                                <i class="fas fa-eye"></i>
                            </button>
                        </div>
                    </div>
                    <div class="col mb-2">
                        <label for="exampleInputPassword2" class="form-label">Повторите пароль</label>
                        <div class="input-group">
                            <input name="pass-second" type="password" class="form-control" id="exampleInputPassword2"
                                placeholder="повторите ваш пароль...">
                            <button class="btn btn-outline-secondary toggle-password" type="button"
                                data-target="#exampleInputPassword2">
                                <i class="fas fa-eye"></i>
                            </button>
                        </div>
                    </div>
                    <input name="admin" class="form-check-input" value="1" type="checkbox" id="flexCheckChecked">
                    <label class="form-check-label" for="flexCheckChecked">
                        Admin?
                    </label>
                    <div class="col">
                        <button name="update-user" class="btn btn-primary" type="submit">Обновить</button>
                    </div>
                </form>
            </div>

        </div>
    </div>
    </div>


            

    <!-- Optional JavaScript; choose one of the two! -->

    <!-- Option 1: Bootstrap Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0-beta2/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-b5kHyXgcpbZJO/tY9Ul7kGkf1S0CWuKcCD38l8YkeH8z8QjE0GmW1gYU5S9FOnJ0"
        crossorigin="anonymous"></script>

    <!-- Option 2: Separate Popper and Bootstrap JS -->
    <script>
        document.querySelectorAll('.toggle-password').forEach(button => {
            button.addEventListener('click', function () {
                const targetSelector = this.getAttribute('data-target');
                const targetInput = document.querySelector(targetSelector);
                const icon = this.querySelector('i');

                if (targetInput.type === 'password') {
                    targetInput.type = 'text';
                    icon.classList.remove('fa-eye');
                    icon.classList.add('fa-eye-slash');
                } else {
                    targetInput.type = 'password';
                    icon.classList.remove('fa-eye-slash');
                    icon.classList.add('fa-eye');
                }
            });
        });

        // Автоматически фокусируемся на поле пароля, если пришли с кнопкой сброса
        const urlParams = new URLSearchParams(window.location.search);
        if (urlParams.has('reset')) {
            const passInput = document.getElementById('exampleInputPassword1');
            if (passInput) {
                passInput.focus();
                passInput.scrollIntoView({ behavior: 'smooth', block: 'center' });
            }
        }
    </script>
</body>

</html>