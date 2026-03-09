<nav class="fixed top-0 w-full z-50 glass border-b border-white/10">
    <div class="max-w-7xl mx-auto px-4 h-20 flex items-center justify-between">
        <div class="flex items-center space-x-2">
            <span class="material-icons text-primary text-3xl">directions_car</span>
            <a href="<?php echo BASE_URL ?>"
                class="text-2xl font-bold tracking-tight text-slate-800 dark:text-white hover:text-slate-900 dark:hover:text-white transition-colors"
                style="text-decoration:none;">Avto<span class="text-primary">Tachka</span></a>
        </div>
        <div class="hidden md:flex items-center space-x-8 font-medium">
            <a class="text-slate-700 dark:text-white hover:text-primary dark:hover:text-primary transition-colors"
                style="text-decoration:none;" href="<?php echo BASE_URL ?>">Каталог</a>
            <a class="text-slate-700 dark:text-white hover:text-primary dark:hover:text-primary transition-colors"
                style="text-decoration:none;" href="<?php echo BASE_URL . 'gallery.php' ?>">Галерея</a>
            <a class="text-slate-700 dark:text-white hover:text-primary dark:hover:text-primary transition-colors"
                style="text-decoration:none;" href="<?php echo BASE_URL . 'about.php' ?>">О нас</a>
        </div>
        <div class="flex items-center space-x-4">
            <div class="relative hidden lg:block">
                <form action="<?= BASE_URL ?>search.php" method="post" id="headerSearchForm" autocomplete="off"
                    class="m-0">
                    <input name="search-term" id="headerSearchInput"
                        class="bg-black/5 dark:bg-white/10 border-slate-200 dark:border-white/20 rounded-full px-4 py-1.5 w-64 focus:ring-primary focus:border-primary text-sm backdrop-blur-md text-slate-800 dark:text-white placeholder-slate-500 dark:placeholder-slate-300 transition-all focus:w-72"
                        placeholder="Поиск авто..." type="text" />
                    <button type="submit"
                        class="material-icons absolute right-3 top-1.5 text-slate-400 text-sm hover:text-primary bg-transparent border-none">search</button>
                </form>
                <div id="search-results" class="absolute w-full mt-1 bg-[#fff] dark:bg-slate-800 rounded-lg shadow-lg"
                    style="display:none; z-index:100;"></div>
            </div>

            <div class="relative group py-4">
                <a href="<?= isset($_SESSION['id']) ? BASE_URL . 'profile.php' : BASE_URL . 'log.php' ?>"
                    class="flex items-center justify-center p-2 rounded-full hover:bg-black/5 dark:hover:bg-white/10 transition-colors text-slate-700 dark:text-white border-none bg-transparent focus:outline-none material-icons"
                    style="text-decoration:none;">
                    person_outline
                </a>
                <!-- Dropdown menu -->
                <div
                    class="absolute right-0 top-[100%] mt-[-10px] pt-4 w-48 opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-300 transform origin-top-right z-50">
                    <div
                        class="bg-[#fff] dark:bg-slate-800 rounded-lg shadow-xl py-1 border border-slate-200 dark:border-slate-700 overflow-hidden">
                        <?php if (isset($_SESSION['id'])): ?>
                            <div
                                class="px-4 py-2 border-b border-slate-200 dark:border-slate-700 text-sm font-bold text-slate-800 dark:text-white">
                                Привет, <?php echo htmlspecialchars($_SESSION['login']); ?>
                            </div>
                            <?php if ($_SESSION['admin']): ?>
                                <a href="<?php echo BASE_URL . 'admin/cars/index.php'; ?>"
                                    class="block px-4 py-2 text-sm text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-700 hover:text-primary transition-colors"
                                    style="text-decoration:none;">Админ панель</a>
                            <?php endif; ?>
                            <a href="<?php echo BASE_URL . 'profile.php'; ?>"
                                class="block px-4 py-2 text-sm text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-700 hover:text-primary transition-colors"
                                style="text-decoration:none;">Личный кабинет</a>
                            <a href="<?php echo BASE_URL . "logout.php"; ?>"
                                class="block px-4 py-2 text-sm text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-700 hover:text-primary transition-colors"
                                style="text-decoration:none;">Выход</a>
                        <?php else: ?>
                            <a href="<?php echo BASE_URL . "log.php"; ?>"
                                class="block px-4 py-2 text-sm text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-700 hover:text-primary transition-colors"
                                style="text-decoration:none;">Войти</a>
                            <a href="<?php echo BASE_URL . "reg.php"; ?>"
                                class="block px-4 py-2 text-sm text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-700 hover:text-primary transition-colors"
                                style="text-decoration:none;">Регистрация</a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Compare Button -->
            <a href="<?= BASE_URL ?>compare.php"
                class="relative material-icons p-2 rounded-full hover:bg-black/5 dark:hover:bg-white/10 transition-colors text-slate-700 dark:text-white border-none bg-transparent focus:outline-none"
                style="text-decoration:none;" title="Сравнение автомобилей">
                balance
                <?php
                $compareCount = isset($_SESSION['compare']) ? count($_SESSION['compare']) : 0;
                ?>
                <span id="compareBadge"
                    class="absolute top-0 right-0 flex items-center justify-center w-4 h-4 text-[10px] font-bold text-white bg-primary rounded-full <?= $compareCount > 0 ? '' : 'hidden' ?>">
                    <?= $compareCount ?>
                </span>
            </a>

            <button
                class="material-icons p-2 rounded-full hover:bg-black/5 dark:hover:bg-white/10 transition-colors text-slate-700 dark:text-white border-none bg-transparent focus:outline-none"
                id="theme-toggle">dark_mode</button>
        </div>
    </div>
</nav>

<script>
    // Theme toggle logic (integrated from Stitch)
    document.addEventListener('DOMContentLoaded', () => {
        const toggle = document.getElementById('theme-toggle');
        const html = document.documentElement;

        // Let PHP handle initial theme via body class, but sync with Tailwind HTML class
        if (document.body.classList.contains('dark-theme')) {
            html.classList.add('dark');
            if (toggle) toggle.textContent = 'light_mode';
        } else {
            html.classList.remove('dark');
            if (toggle) toggle.textContent = 'dark_mode';
        }

        if (toggle) {
            toggle.addEventListener('click', () => {
                if (html.classList.contains('dark')) {
                    html.classList.remove('dark');
                    document.body.classList.remove('dark-theme');
                    toggle.textContent = 'dark_mode';

                    // Ajax request to update theme in session/db if needed (handled by profile or search script usually)
                    // We'll fake a simple request just to set session theme
                    fetch('<?= BASE_URL ?>app/controllers/theme.php?theme=light').catch(e => console.log(e));
                } else {
                    html.classList.add('dark');
                    document.body.classList.add('dark-theme');
                    toggle.textContent = 'light_mode';

                    fetch('<?= BASE_URL ?>app/controllers/theme.php?theme=dark').catch(e => console.log(e));
                }
            });
        }
    });

    // Magnetic Button Effect
    document.addEventListener('mousemove', (e) => {
        document.querySelectorAll('.magnetic-btn').forEach(btn => {
            const rect = btn.getBoundingClientRect();
            // Check if mouse is near the button
            if (e.clientX >= rect.left - 50 && e.clientX <= rect.right + 50 &&
                e.clientY >= rect.top - 50 && e.clientY <= rect.bottom + 50) {
                const x = e.clientX - rect.left - rect.width / 2;
                const y = e.clientY - rect.top - rect.height / 2;
                btn.style.transform = `translate(${x * 0.2}px, ${y * 0.2}px) scale(1.05)`;
            } else {
                btn.style.transform = `translate(0, 0) scale(1)`;
            }
        });
    });
</script>