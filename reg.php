<?php
include "path.php";
include "app/controllers/users.php";
?>
<html lang="ru" class="<?= ($_SESSION['theme'] ?? 'dark') === 'dark' ? 'dark' : '' ?>">

<head>
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!-- Tailwind CSS (Stitch Integration) -->
    <script src="https://cdn.tailwindcss.com?plugins=forms,typography"></script>
    <script>
        tailwind.config = {
            darkMode: "class",
            theme: {
                extend: {
                    colors: {
                        primary: "#e11d48", // Vibrant Red
                        "background-light": "#f8fafc",
                        "background-dark": "#0f172a",
                        accent: "#3b82f6", // Vibrant Blue
                    },
                    fontFamily: {
                        display: ["Outfit", "sans-serif"],
                        sans: ["Outfit", "sans-serif"],
                    },
                    borderRadius: {
                        DEFAULT: "0.75rem",
                    },
                    animation: {
                        'pulse-slow': 'pulse 3s cubic-bezier(0.4, 0, 0.6, 1) infinite',
                    }
                },
            },
        };
    </script>
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet" />
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;700&display=swap" rel="stylesheet" />

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0-beta2/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-BmbxuPwQa2lc/FVzBcNJ7UAyJxM6wuqIj61tLrc4wSX0szH/Ev+nYRRuWlolflfl" crossorigin="anonymous">

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.7.2/css/all.css"
        integrity="sha384-fnmOCqbTlWIlj8LyTjo7mOUStjsKC4pOpQbqyi7RrhN7udi9RwhKkMHpvLbHG9Sr" crossorigin="anonymous">

    <!-- Custom Styling -->
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="preconnect" href="https://fonts.gstatic.com">
    <link href="https://fonts.googleapis.com/css2?family=Comfortaa:wght@300;400;500;600;700&display=swap"
        rel="stylesheet">
    <title>My blog</title>
</head>

<body
    class="bg-slate-50 dark:bg-background-dark text-slate-900 dark:text-slate-100 <?= ($_SESSION['theme'] ?? 'dark') === 'dark' ? 'dark-theme' : '' ?>">

    <?php include("app/include/header.php"); ?>

    <!-- Background Decoration -->
    <div
        class="fixed inset-0 z-0 bg-animated-gradient <?= ($_SESSION['theme'] ?? 'dark') === 'dark' ? 'theme-dark-gradient' : 'theme-light-gradient' ?>">
    </div>

    <!-- START FORM -->
    <main class="relative z-10 flex-1 flex items-center justify-center px-4 py-16 mt-16 min-h-[80vh]">
        <div class="w-full max-w-lg">
            <div
                class="glass-card rounded-xl p-8 lg:p-10 shadow-2xl relative overflow-hidden bg-white/5 dark:bg-black/20">
                <!-- Subtle pattern overlay -->
                <div
                    class="absolute inset-0 opacity-[0.03] pointer-events-none bg-[url('https://www.transparenttextures.com/patterns/carbon-fibre.png')]">
                </div>
                <div class="relative z-10">
                    <div class="mb-8 text-center">
                        <h2 class="text-3xl font-bold tracking-tight mb-2 font-display">Регистрация</h2>
                        <p class="text-slate-500 dark:text-slate-400 text-sm">Создайте аккаунт для полного доступа.</p>
                    </div>

                    <div class="mb-4 text-center err">
                        <?php if (!empty($errMsg)):
                            foreach ($errMsg as $e): ?>
                                <p class="text-primary text-sm"><?= $e ?></p>
                            <?php endforeach; endif; ?>
                    </div>

                    <form action="reg.php" method="post" class="space-y-6">
                        <?= csrfField() ?>

                        <div>
                            <label
                                class="block text-xs font-bold uppercase tracking-widest text-slate-500 dark:text-slate-400 mb-2 ml-1"
                                for="formGroupExampleInput">Ваш логин</label>
                            <div class="relative group">
                                <span
                                    class="material-icons absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 group-focus-within:text-primary transition-colors">person</span>
                                <input name="login" value="<?= htmlspecialchars($login ?? '') ?>" type="text"
                                    class="w-full bg-white/50 dark:bg-slate-900/50 border border-slate-300 dark:border-slate-700 rounded-lg py-3 pl-12 pr-4 text-slate-900 dark:text-slate-100 placeholder:text-slate-500 focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary transition-all"
                                    id="formGroupExampleInput" placeholder="введите ваш логин...">
                            </div>
                        </div>

                        <div>
                            <label
                                class="block text-xs font-bold uppercase tracking-widest text-slate-500 dark:text-slate-400 mb-2 ml-1"
                                for="exampleInputEmail1">Email</label>
                            <div class="relative group">
                                <span
                                    class="material-icons absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 group-focus-within:text-primary transition-colors">mail</span>
                                <input name="mail" value="<?= htmlspecialchars($email ?? '') ?>" type="email"
                                    class="w-full bg-white/50 dark:bg-slate-900/50 border border-slate-300 dark:border-slate-700 rounded-lg py-3 pl-12 pr-4 text-slate-900 dark:text-slate-100 placeholder:text-slate-500 focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary transition-all"
                                    id="exampleInputEmail1" placeholder="введите ваш email...">
                            </div>
                            <div class="form-text text-[10px] uppercase tracking-wider text-slate-400 mt-2 ml-1">Ваш
                                email адрес не будет использован для спама!</div>
                        </div>

                        <div>
                            <label
                                class="block text-xs font-bold uppercase tracking-widest text-slate-500 dark:text-slate-400 mb-2 ml-1"
                                for="exampleInputPassword1">Пароль</label>
                            <div class="relative group">
                                <span
                                    class="material-icons absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 group-focus-within:text-primary transition-colors">lock</span>
                                <input name="pass-first" type="password"
                                    class="w-full bg-white/50 dark:bg-slate-900/50 border border-slate-300 dark:border-slate-700 rounded-lg py-3 pl-12 pr-12 text-slate-900 dark:text-slate-100 placeholder:text-slate-500 focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary transition-all"
                                    id="exampleInputPassword1" placeholder="введите пароль...">
                                <button
                                    class="absolute right-4 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600 dark:text-slate-500 dark:hover:text-slate-300 toggle-password"
                                    type="button" data-target="#exampleInputPassword1">
                                    <span class="material-icons text-[20px]">visibility</span>
                                </button>
                            </div>
                        </div>

                        <div>
                            <label
                                class="block text-xs font-bold uppercase tracking-widest text-slate-500 dark:text-slate-400 mb-2 ml-1"
                                for="exampleInputPassword2">Повторите пароль</label>
                            <div class="relative group">
                                <span
                                    class="material-icons absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 group-focus-within:text-primary transition-colors">lock</span>
                                <input name="pass-second" type="password"
                                    class="w-full bg-white/50 dark:bg-slate-900/50 border border-slate-300 dark:border-slate-700 rounded-lg py-3 pl-12 pr-12 text-slate-900 dark:text-slate-100 placeholder:text-slate-500 focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary transition-all"
                                    id="exampleInputPassword2" placeholder="повторите пароль...">
                                <button
                                    class="absolute right-4 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600 dark:text-slate-500 dark:hover:text-slate-300 toggle-password"
                                    type="button" data-target="#exampleInputPassword2">
                                    <span class="material-icons text-[20px]">visibility</span>
                                </button>
                            </div>
                        </div>

                        <button type="submit" name="button-reg"
                            class="w-full bg-primary hover:bg-primary/90 text-white py-4 rounded-lg font-bold tracking-widest uppercase text-sm transition-all shadow-lg shadow-primary/30 flex items-center justify-center gap-2 group mt-6">
                            Зарегистрироваться
                            <span
                                class="material-icons transition-transform group-hover:translate-x-1">arrow_forward</span>
                        </button>
                    </form>

                    <div class="mt-8 pt-6 border-t border-slate-200 dark:border-slate-700/50 text-center">
                        <p class="text-sm text-slate-500 dark:text-slate-400">
                            Уже есть аккаунт?
                            <a href="<?= BASE_URL ?>log.php"
                                class="text-primary font-bold hover:underline underline-offset-4 ml-1 transition-all">Войти</a>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </main>
    <!-- END FORM -->

    <!-- footer -->
    <?php include("app/include/footer.php"); ?>
    <!-- // footer -->


    <!-- Optional JavaScript; choose one of the two! -->

    <!-- Option 1: Bootstrap Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0-beta2/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-b5kHyXgcpbZJO/tY9Ul7kGkf1S0CWuKcCD38l8YkeH8z8QjE0GmW1gYU5S9FOnJ0"
        crossorigin="anonymous"></script>

    <!-- Option 2: Separate Popper and Bootstrap JS -->
    <!--
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.6.0/dist/umd/popper.min.js" integrity="sha384-KsvD1yqQ1/1+IA7gi3P0tyJcT3vR+NdBTt13hSJ2lnve8agRGXTTyNaBYmCR/Nwi" crossorigin="anonymous"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0-beta2/dist/js/bootstrap.min.js" integrity="sha384-nsg8ua9HAw1y0W1btsyWgBklPnCUAFLuTMS2G72MMONqmOymq585AcH49TLBQObG" crossorigin="anonymous"></script>
-->
</body>

</html>