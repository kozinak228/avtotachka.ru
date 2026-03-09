<footer class="bg-slate-950 pt-12 pb-6 border-t border-white/10 mt-auto w-full z-10 relative">
    <div class="max-w-7xl mx-auto px-4">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-8 mb-8">
            <div class="col-span-1 md:col-span-2">
                <div class="flex items-center space-x-2 mb-4">
                    <span class="material-icons text-primary text-2xl">directions_car</span>
                    <span class="text-xl font-bold tracking-tight text-white">Avto<span class="text-primary">Tachka</span></span>
                </div>
                <p class="text-slate-400 max-w-sm mb-4 text-sm leading-snug">Ваш надежный партнер в мире премиальных
                    автомобилей из Китая. Широкий каталог, подробные характеристики, честные цены.</p>
                <div class="flex space-x-3">
                    <a href="#"
                        class="w-8 h-8 rounded-full glass hover:bg-primary transition-colors flex items-center justify-center text-white text-sm"
                        style="text-decoration:none;"><i class="fab fa-telegram"></i></a>
                    <a href="#"
                        class="w-8 h-8 rounded-full glass hover:bg-primary transition-colors flex items-center justify-center text-white text-sm"
                        style="text-decoration:none;"><i class="fab fa-whatsapp"></i></a>
                    <a href="#"
                        class="w-8 h-8 rounded-full glass hover:bg-primary transition-colors flex items-center justify-center text-white text-sm"
                        style="text-decoration:none;"><i class="fab fa-instagram"></i></a>
                    <a href="#"
                        class="w-8 h-8 rounded-full glass hover:bg-primary transition-colors flex items-center justify-center text-white text-sm"
                        style="text-decoration:none;"><i class="fab fa-youtube"></i></a>
                </div>
            </div>

            <div>
                <h4 class="text-white font-bold mb-4 text-sm">Навигация</h4>
                <ul class="space-y-2 m-0 p-0 list-none text-sm">
                    <li><a href="<?php echo BASE_URL ?>" class="text-slate-400 hover:text-primary transition-colors"
                            style="text-decoration:none;">Каталог</a></li>
                    <li><a href="<?php echo BASE_URL . 'gallery.php' ?>"
                            class="text-slate-400 hover:text-primary transition-colors"
                            style="text-decoration:none;">Галерея</a></li>
                    <li><a href="<?php echo BASE_URL . 'about.php' ?>"
                            class="text-slate-400 hover:text-primary transition-colors" style="text-decoration:none;">О
                            компании</a></li>
                    <li><a href="#" class="text-slate-400 hover:text-primary transition-colors"
                            style="text-decoration:none;">Контакты</a></li>
                </ul>
            </div>

            <div>
                <h4 class="text-white font-bold mb-4 text-sm">Контакты</h4>
                <ul class="space-y-2 m-0 p-0 list-none text-sm">
                    <li class="flex items-center space-x-2 text-slate-400">
                        <span class="material-icons text-primary text-sm">phone</span>
                        <span>+7 (999) 123-45-67</span>
                    </li>
                    <li class="flex items-center space-x-2 text-slate-400">
                        <span class="material-icons text-primary text-sm">email</span>
                        <span>info@AvtoTachka.ru</span>
                    </li>
                </ul>
            </div>
        </div>

        <div
            class="border-t border-white/10 pt-6 flex flex-col md:flex-row items-center justify-between text-slate-500 text-xs">
            <p class="m-0">&copy; <?php echo date('Y'); ?> AvtoTachka Premium. Все права защищены.</p>
            <div class="flex space-x-4 mt-3 md:mt-0">
                <a href="#" class="hover:text-white transition-colors" style="text-decoration:none;">Политика
                    конфиденциальности</a>
                <a href="#" class="hover:text-white transition-colors" style="text-decoration:none;">Условия
                    использования</a>
            </div>
        </div>
    </div>
</footer>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const togglePasswordBtns = document.querySelectorAll('.toggle-password');
        togglePasswordBtns.forEach(btn => {
            btn.addEventListener('click', function () {
                const input = document.querySelector(this.getAttribute('data-target'));
                const icon = this.querySelector('i');
                if (input) {
                    if (input.type === 'password') {
                        input.type = 'text';
                        icon.classList.remove('fa-eye');
                        icon.classList.add('fa-eye-slash');
                    } else {
                        input.type = 'password';
                        icon.classList.remove('fa-eye-slash');
                        icon.classList.add('fa-eye');
                    }
                }
            });
        });

        const favBtns = document.querySelectorAll('.fav-btn');
        favBtns.forEach(btn => {
            btn.addEventListener('click', function (e) {
                e.preventDefault();
                e.stopPropagation();
                const carId = this.getAttribute('data-id');
                const icon = this.querySelector('i') || this.querySelector('.material-icons');

                fetch('<?= BASE_URL ?>ajax_favorites.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({ car_id: carId })
                })
                    .then(response => response.json())
                    .then(data => {
                        if (data.status === 'success') {
                            if (data.action === 'added') {
                                if (icon.tagName.toLowerCase() === 'i') {
                                    icon.classList.remove('far', 'text-white');
                                    icon.classList.add('fas', 'text-primary');
                                } else {
                                    icon.textContent = 'favorite';
                                    icon.classList.remove('text-slate-400', 'text-white');
                                    icon.classList.add('text-primary');
                                }
                            } else {
                                if (icon.tagName.toLowerCase() === 'i') {
                                    icon.classList.remove('fas', 'text-primary');
                                    icon.classList.add('far', 'text-white');
                                } else {
                                    icon.textContent = 'favorite_border';
                                    icon.classList.remove('text-primary');
                                    icon.classList.add('text-slate-400');
                                }
                            }
                        } else {
                            if (data.message === 'Not authenticated') {
                                alert('Для добавления в избранное необходимо авторизоваться!');
                            }
                        }
                    })
                    .catch(error => console.error('Error:', error));
            });
        });
    });
    // AJAX LIVE SEARCH
    const searchInput = document.getElementById('headerSearchInput');
    const searchResults = document.getElementById('search-results');
    let debounceTimer;

    if (searchInput && searchResults) {
        searchInput.addEventListener('input', function () {
            const term = this.value.trim();
            clearTimeout(debounceTimer);

            if (term.length < 2) {
                searchResults.style.display = 'none';
                searchResults.innerHTML = '';
                return;
            }

            debounceTimer = setTimeout(() => {
                fetch('<?= BASE_URL ?>ajax_search.php?term=' + encodeURIComponent(term))
                    .then(response => response.json())
                    .then(data => {
                        searchResults.innerHTML = '';
                        if (data.items && data.items.length > 0) {
                            data.items.forEach(car => {
                                const item = document.createElement('a');
                                item.href = car.url;
                                item.className = 'search-result-item';

                                const imgHTML = car.img
                                    ? `<img src="${car.img}" class="search-result-img" alt="">`
                                    : `<div class="search-result-img d-flex align-items-center justify-content-center"><i class="fas fa-car text-muted"></i></div>`;

                                item.innerHTML = `
                                    ${imgHTML}
                                    <div class="search-result-info">
                                        <span class="search-result-title">${car.title}</span>
                                        <span class="search-result-meta">${car.brand}</span>
                                    </div>
                                    <div class="search-result-price">${car.price}</div>
                                `;
                                searchResults.appendChild(item);
                            });

                            if (data.total > data.items.length) {
                                const allLink = document.createElement('a');
                                allLink.href = '#';
                                allLink.className = 'search-result-item justify-content-center fw-bold';
                                allLink.style.color = '#e94560';
                                allLink.innerHTML = `Показать все результаты (${data.total})`;
                                allLink.onclick = (e) => {
                                    e.preventDefault();
                                    document.getElementById('headerSearchForm').submit();
                                };
                                searchResults.appendChild(allLink);
                            }
                            searchResults.style.display = 'block';
                        } else {
                            searchResults.innerHTML = '<div class="search-no-results">Ничего не найдено</div>';
                            searchResults.style.display = 'block';
                        }
                    })
                    .catch(err => {
                        console.error('Search error:', err);
                    });
            }, 300); // 300ms debounce
        });

        // Закрытие при клике вне области
        document.addEventListener('click', function (e) {
            if (!searchInput.contains(e.target) && !searchResults.contains(e.target)) {
                searchResults.style.display = 'none';
            }
        });

        // Показ при фокусе, если есть текст
        searchInput.addEventListener('focus', function () {
            if (this.value.trim().length >= 2 && searchResults.innerHTML !== '') {
                searchResults.style.display = 'block';
            }
        });
    }
</script>