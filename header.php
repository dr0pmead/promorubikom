<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo('charset'); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">
    <title><?php bloginfo('name'); ?></title>
    <?php wp_head(); ?>
    <script src="//unpkg.com/alpinejs" defer></script>
</head>
<body <?php body_class(); ?>>

<div id="preloader" class="flex flex-col gap-12 bg-[#131313]">
    <div class="logo-container">
        <!-- Логотип -->
        <img src="<?php echo get_template_directory_uri(); ?>/assets/images/logo.svg" alt="Логотип" class="logo w-48">
    </div>
    <!-- Анимация с точками -->
    <div class="dots-container">
        <span class="dot dot1"></span>
        <span class="dot dot2"></span>
        <span class="dot dot3"></span>
    </div>
</div>

<header id="header" class="w-full h-[100px] md:h-[130px] flex items-center justify-between fixed top-0 left-0 duration-150 z-50 px-8 md:px-14 lg:px-16 xl:px-8 2xl:px-24 3xl:px-28">

    <!-- Логотип -->
    <div class="flex items-center justify-between w-full">
        <div class="flex w-full justify-between items-center gap-6">
        <a href="<?php echo home_url(); ?>">
            <img src="<?php echo get_template_directory_uri(); ?>/assets/images/logo.svg" alt="Logo" class="w-32 max-w-32 object-contain">
        </a>
        

        <!-- Меню для десктопа -->
        <nav class="hidden xl:flex flex-1 mx-8">
            <?php
            wp_nav_menu( array(
                'theme_location' => 'primary',
                'container'      => 'ul',
                'menu_class'     => 'flex text-lg sm:gap-3 md:gap-4 xl:gap-6 lg:text-md duration-150',
                'walker'         => new Custom_Walker_Nav_Menu(), 
            ) );
            ?>
        </nav>
        </div>

        <?php echo do_shortcode('[gtranslate]'); ?>
        <!-- Кнопка выбора языка с выпадающим меню -->
    </div>

    <!-- Кнопка входа -->
    <div id="login-container">
        <?php echo get_user_button_html(); ?>
    </div>


    <!-- Кнопка мобильного меню -->
    <div class="xl:hidden flex items-center">
        <button id="mobile-menu-button" class="focus:outline-none">
            <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
            </svg>
        </button>
    </div>
</header>

    <!-- Мобильное меню -->
    <div id="mobile-menu" class="xl:hidden fixed inset-0 bg-[#131313] bg-opacity-75 z-40 duration-500 ease-in-out opacity-0 translate-x-[200px] backdrop-blur-md px-8 pointer-events-none">
        <div class="flex flex-col items-center justify-center h-full text-white text-lg space-y-4">
            <nav class="w-full">
                <?php
                wp_nav_menu( array(
                    'theme_location' => 'header-menu',
                    'container'      => 'ul',
                    'menu_class'     => 'flex flex-col gap-3 text-2xl',
                    'walker'         => new Custom_Walker_Nav_Menu_Mobile(), 
                ) );
                ?>
            </nav>

            <!-- Языковая опция в мобильном меню -->
            <div class="w-full justify-start">
                <div id="login-container">
                    <?php echo get_user_button_html_mobile(); ?>
                </div>
                </div>
                </div>
            </div>
    </div>

    <!-- Окно авторизации -->
    <div class="auth-modal bg-[#131313] border-[1px] border-[#fff]/10 p-8 rounded-lg text-center max-w-md mx-auto remodal" data-remodal-id="modal-auth" role="dialog" aria-labelledby="modalTitle" aria-describedby="modalDesc">
        <h1 class="text-2xl font-bold text-white mb-6">Авторизация</h1>

        <!-- Блок для отображения ошибки -->
        <div id="login-error" class="text-red-500 text-sm mb-4 hidden"></div>

        <form id="login-form">
            <!-- Поле для ввода телефона -->
            <div class="mb-6">
                <input type="name" name="name" placeholder="Имя пользователя" class="w-full px-4 py-3 font-bold border-[1px] border-[#fff]/10 bg-[#131313] text-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-orange-600">
            </div>

            <!-- Поле для ввода пароля -->
            <div class="mb-6">
                <input type="password" name="password" placeholder="Введите пароль" class="w-full px-4 py-3 font-bold border-[1px] border-[#fff]/10 bg-[#131313] text-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-orange-600">
            </div>

            <!-- Кнопка входа -->
            <button id="submit-login" class="disabled:bg-[#E53F0B]/50 bg-[#E53F0B] hover:bg-[#F35726] text-white px-6 py-3 rounded-md transition-colors font-bold font-xl w-full flex items-center justify-center">
                <span class="btn-text">Войти</span>
                <span class="btn-spinner hidden animate-spin fill-white h-5 w-5">
                    <svg viewBox="0 0 16 16" xmlns="http://www.w3.org/2000/svg">
                        <path d="M2.501 8C2.501 6.91221 2.82357 5.84884 3.42792 4.94437C4.03227 4.0399 4.89125 3.33495 5.89624 2.91867C6.90123 2.50238 8.0071 2.39347 9.074 2.60568C10.1409 2.8179 11.1209 3.34173 11.8901 4.11092C12.6593 4.8801 13.1831 5.86011 13.3953 6.92701C13.6075 7.9939 13.4986 9.09977 13.0823 10.1048C12.6661 11.1098 11.9611 11.9687 11.0566 12.5731C10.1522 13.1774 9.0888 13.5 8.001 13.5C7.80209 13.4999 7.61127 13.5788 7.47052 13.7193C7.32978 13.8599 7.25063 14.0506 7.2505 14.2495C7.25037 14.4484 7.32926 14.6392 7.46982 14.78C7.61037 14.9207 7.80109 14.9999 8 15C9.38447 15 10.7378 14.5895 11.889 13.8203C13.0401 13.0511 13.9373 11.9579 14.4672 10.6788C14.997 9.3997 15.1356 7.99224 14.8655 6.63437C14.5954 5.2765 13.9287 4.02922 12.9497 3.05026C11.9708 2.07129 10.7235 1.4046 9.36563 1.13451C8.00777 0.86441 6.6003 1.00303 5.32122 1.53285C4.04213 2.06266 2.94888 2.95987 2.17971 4.11101C1.41054 5.26216 1 6.61553 1 8C1 8.19905 1.07907 8.38994 1.21982 8.53069C1.36056 8.67143 1.55145 8.7505 1.7505 8.7505C1.94954 8.7505 2.14044 8.67143 2.28118 8.53069C2.42193 8.38994 2.501 8.19905 2.501 8Z"/>
                    </svg>
                </span>
            </button>
        </form>
    </div>

    <!-- Модальное окно для смены пароля -->
    <div class="modal-change-password bg-[#131313] border-[1px] border-[#fff]/10 p-8 rounded-lg text-center max-w-lg mx-auto remodal" data-remodal-id="modal-change-password">
            <h1 class="text-2xl font-bold text-white mb-6">Смена пароля</h1>
            
            <div id="password-message" class="text-sm text-white mb-4 hidden"></div>

            <form id="change-password-form">
                <!-- Поле для нового пароля -->
                <div class="mb-6">
                    <input type="password" id="new-password" name="new-password" placeholder="Введите новый пароль" class="w-full px-4 py-3 font-bold border-[1px] border-[#fff]/10 bg-[#131313] text-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-orange-600">
                </div>

                <!-- Поле для подтверждения пароля -->
                <div class="mb-6">
                    <input type="password" id="confirm-password" name="confirm-password" placeholder="Подтвердите новый пароль" class="w-full px-4 py-3 font-bold border-[1px] border-[#fff]/10 bg-[#131313] text-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-orange-600">
                </div>

                <!-- Кнопка для отправки -->
                <button type="submit" id="submit-password" class="disabled:bg-[#E53F0B]/50 bg-[#E53F0B] hover:bg-[#F35726] mt-6 text-white px-6 py-3 rounded-md w-full transition-colors font-bold flex items-center justify-center">
                    <span class="btn-text">Сохранить</span>
                    <span class="btn-spinner hidden animate-spin fill-white h-5 w-5">
                        <svg viewBox="0 0 16 16" xmlns="http://www.w3.org/2000/svg">
                            <path d="M2.501 8C2.501 6.91221 2.82357 5.84884 3.42792 4.94437C4.03227 4.0399 4.89125 3.33495 5.89624 2.91867C6.90123 2.50238 8.0071 2.39347 9.074 2.60568C10.1409 2.8179 11.1209 3.34173 11.8901 4.11092C12.6593 4.8801 13.1831 5.86011 13.3953 6.92701C13.6075 7.9939 13.4986 9.09977 13.0823 10.1048C12.6661 11.1098 11.9611 11.9687 11.0566 12.5731C10.1522 13.1774 9.0888 13.5 8.001 13.5C7.80209 13.4999 7.61127 13.5788 7.47052 13.7193C7.32978 13.8599 7.25063 14.0506 7.2505 14.2495C7.25037 14.4484 7.32926 14.6392 7.46982 14.78C7.61037 14.9207 7.80109 14.9999 8 15C9.38447 15 10.7378 14.5895 11.889 13.8203C13.0401 13.0511 13.9373 11.9579 14.4672 10.6788C14.997 9.3997 15.1356 7.99224 14.8655 6.63437C14.5954 5.2765 13.9287 4.02922 12.9497 3.05026C11.9708 2.07129 10.7235 1.4046 9.36563 1.13451C8.00777 0.86441 6.6003 1.00303 5.32122 1.53285C4.04213 2.06266 2.94888 2.95987 2.17971 4.11101C1.41054 5.26216 1 6.61553 1 8C1 8.19905 1.07907 8.38994 1.21982 8.53069C1.36056 8.67143 1.55145 8.7505 1.7505 8.7505C1.94954 8.7505 2.14044 8.67143 2.28118 8.53069C2.42193 8.38994 2.501 8.19905 2.501 8Z"/>
                        </svg>
                    </span>
                </button>
            </form>
    </div>

    <!-- Модальное окно для рандомайзера -->
    <div class="remodal bg-[#131313] border-[1px] border-[#fff]/10 p-8 rounded-lg text-center max-w-md mx-auto" data-remodal-id="modal-randomizer" role="dialog" aria-labelledby="modalTitle" aria-describedby="modalDesc" data-remodal-options="hashTracking: false, closeOnOutsideClick: false">
        <h1 id="modalTitle" class="text-2xl font-bold text-white mb-6">Рандомайзер</h1>

        <!-- Поле для ввода количества участников -->
        <input type="number" id="randomizer-participant-count" class="w-full px-4 py-3 font-bold border-[1px] border-[#fff]/10 bg-[#131313] text-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-orange-600 mb-6" placeholder="Количество участников" min="1">

        <!-- Поле для ввода количества победителей -->
        <input type="number" id="randomizer-winner-count" class="w-full px-4 py-3 font-bold border-[1px] border-[#fff]/10 bg-[#131313] text-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-orange-600 mb-6" placeholder="Количество победителей" min="1">

        <!-- Кнопка "Разыграть" -->
        <button id="start-randomizer" class="bg-[#E53F0B] hover:bg-[#F35726] text-white px-6 py-3 rounded-md w-full transition-colors font-bold">
            Разыграть
        </button>

        <!-- Контейнер для отображения победителей, скрыт по умолчанию -->
        <div id="randomizer-result" class="text-white mt-6 hidden"></div>
    </div>



<?php wp_footer(); ?>

<script>

jQuery(document).ready(function($) {
        // Проверяем куки при загрузке страницы
        if (getCookie('user_id')) {
            $.ajax({
                url: ajax_object.ajax_url,
                method: 'POST',
                data: { action: 'get_user_button_html' }, // Действие в WordPress
                success: function(response) {
                    // Заменяем содержимое контейнера на ответ с HTML кнопки
                    $('#login-container').html(response);
                }
            });
        }

        // Функция для получения куки
        function getCookie(name) {
            let matches = document.cookie.match(new RegExp(
                "(?:^|; )" + name.replace(/([\.$?*|{}\(\)\[\]\\\/\+^])/g, '\\$1') + "=([^;]*)"
            ));
            return matches ? decodeURIComponent(matches[1]) : undefined;
        }
        
        jQuery(document).on('click', '#logout-btn', function () {
            $.ajax({
                url: '<?php echo admin_url('admin-ajax.php'); ?>',
            type: 'POST',
            data: { action: 'logout_user' }, // Указываем действие logout
            success: function (response) {
                if (response.success) {
                    // Перезагрузка страницы после успешного logout
                    window.location.reload();
                } else {
                    alert('Ошибка при выходе: ' + response.data.message);
                }
            },
            error: function () {
                alert('Ошибка сервера при попытке выхода');
            }
        });
    });
});

jQuery(document).ready(function($) {
    $('#change-password-form').on('submit', function(e) {
        e.preventDefault(); // Останавливаем стандартную отправку формы
        
        var newPassword = $('#new-password').val();
        var confirmPassword = $('#confirm-password').val();
        
        // Скрываем сообщения
        $('#password-message').addClass('hidden').removeClass('text-red-500 text-green-500');
        
        // Валидация: проверяем, что пароли совпадают
        if (newPassword !== confirmPassword) {
            $('#password-message').text('Пароли не совпадают').removeClass('hidden').addClass('text-red-500');
            return;
        }
        
        // Показываем спиннер
        $('#submit-password .btn-text').prop('disabled', true);
        $('#submit-password .btn-text').addClass('hidden');
        $('#submit-password .btn-spinner').removeClass('hidden');
        
        // Отправка запроса на сервер для смены пароля
        $.ajax({
            url: '<?php echo admin_url("admin-ajax.php"); ?>', // admin-ajax.php для WordPress
            type: 'POST',
            data: {
                action: 'change_password',
                new_password: newPassword
            },
            success: function(response) {
                if (response.success) {
                    // Выводим сообщение об успешной смене пароля
                    $('#password-message').text('Пароль успешно изменен').removeClass('hidden').addClass('text-green-500');
                    
                    // Сбрасываем поля
                    $('#new-password').val('');
                    $('#confirm-password').val('');
                    
                    // Закрываем модальное окно через 2 секунды
                    setTimeout(function() {
                        var remodalInstance = $('[data-remodal-id="modal-change-password"]').remodal();
                        remodalInstance.close();
                    }, 2000);
                } else {
                    $('#password-message').text('Ошибка: ' + response.message).removeClass('hidden').addClass('text-red-500');
                }

                // Возвращаем кнопку в исходное состояние
                $('#submit-password .btn-text').prop('disabled', false);
                $('#submit-password .btn-text').removeClass('hidden');
                $('#submit-password .btn-spinner').addClass('hidden');
            },
            error: function() {
                $('#password-message').text('Ошибка отправки данных на сервер').removeClass('hidden').addClass('text-red-500');
                
                // Восстанавливаем кнопку
                $('#submit-password .btn-text').prop('disabled', false);
                $('#submit-password .btn-text').removeClass('hidden');
                $('#submit-password .btn-spinner').addClass('hidden');
            }
        });
    });
});

jQuery(document).ready(function($) {
    $('#login-form').on('submit', function(e) {
        e.preventDefault(); // Останавливаем стандартную отправку формы

        var formData = $(this).serialize(); // Собираем данные формы
        var csrfToken = $('input[name="csrf_token"]').val(); // Получаем CSRF токен

        // Проверка на пустые поля
        var name = $('input[name="name"]').val();
        var password = $('input[name="password"]').val();
        
        if (!name || !password) {
            $('#login-error').text('Пожалуйста, заполните все поля.').fadeIn();
            return;
        }

        // Показываем спиннер и скрываем текст кнопки
        $('#submit-login .btn-text').prop('disabled', true);
        $('#submit-login .btn-text').addClass('hidden');
        $('#submit-login .btn-spinner').removeClass('hidden');

        $.ajax({
            url: ajax_object.ajax_url, // Используем переменную ajax_object, переданную через wp_localize_script
            type: 'POST',
            data: formData + '&action=login_user', // Добавляем action
            success: function(response) {
                if (response.success) {
                    // Авторизация прошла успешно
                    window.location.href = '/admin-panel'; // Перенаправление на dashboard
                } else {
                    // Показываем сообщение об ошибке
                    $('#login-error').text('Ошибка авторизации: ' + response.data.message).fadeIn();
                }
            },
            error: function() {
                // Показываем общую ошибку
                $('#login-error').text('Ошибка отправки данных на сервер.').fadeIn();
            },
            complete: function() {
                // Восстанавливаем кнопку и убираем спиннер
                $('#submit-login .btn-text').prop('disabled', false);
                $('#submit-login .btn-text').removeClass('hidden');
                $('#submit-login .btn-spinner').addClass('hidden');
            }
        });
    });
});

window.addEventListener('load', function() {
    var preloader = document.getElementById('preloader');
    preloader.classList.add('hidden'); // Добавляем класс для плавного затухания
});

document.addEventListener('DOMContentLoaded', function () {
    // Ищем оригинальный селект GTranslate
    const originalSelect = document.querySelector('.gt_selector');

    if (originalSelect) {
        // Скрываем оригинальный селект
        originalSelect.style.display = 'none';

        // Получаем все опции из оригинального селекта
        const options = originalSelect.querySelectorAll('option');

        // Получаем выбранный язык по умолчанию
        let selectedLanguage = originalSelect.options[originalSelect.selectedIndex].text;

        // Создаем кастомное выпадающее меню для десктопа
        const customDropdownDesktop = document.createElement('div');
        customDropdownDesktop.classList.add('w-[115px]');
        customDropdownDesktop.innerHTML = `
            <div x-data="{ open: false, selectedLang: '${selectedLanguage}' }" class="relative hidden xl:block">
                <button @click="open = !open" class="text-sm p-2 rounded-lg flex gap-2 items-center group max-w-lg">
                    <span id="desktop-lang" x-text="selectedLang" class="text-white font-bold text-sm group-hover:opacity-[100%] opacity-[60%] duration-150 uppercase leading-[5px] text-nowrap"></span>
                    <span>
                      <img src="<?php echo get_template_directory_uri(); ?>/assets/images/material-symbols_language.svg" alt="Language Icon" class="duration-150 w-8 max-w-8 w-auto object-contain group-hover:opacity-[100%] opacity-[60%]">
                    </span>
                </button>

                <!-- Всплывающее меню с языками -->
                <div x-show="open" @click.away="open = false"
                    x-transition:enter="transition ease-out duration-200"
                    x-transition:enter-start="opacity-0 translate-y-2"
                    x-transition:enter-end="opacity-100 translate-y-0"
                    x-transition:leave="transition ease-in duration-150"
                    x-transition:leave-start="opacity-100 translate-y-0"
                    x-transition:leave-end="opacity-0 translate-y-2"
                    class="absolute right-0 mt-2 w-72 bg-[#131313]/50 backdrop-blur-md border-[1px] border-[#fff]/10 text-gray-300 rounded-md shadow-lg z-50">
                    <ul id="custom-lang-options-desktop" class="grid grid-cols-2 p-4">
                        <!-- Языки будут динамически добавлены сюда -->
                    </ul>
                </div>
            </div>
        `;

        // Создаем кастомное выпадающее меню для мобильного
        const customDropdownMobile = document.createElement('div');
        customDropdownMobile.classList.add('w-[115px]');
        customDropdownMobile.innerHTML = `
            <div x-data="{ open: false, selectedLang: '${selectedLanguage}' }" class="relative block xl:hidden">
                <button @click="open = !open" class="text-sm p-2 rounded-lg flex gap-2 items-center group" @click.stop>
                    <span id="mobile-lang" x-text="selectedLang" class="text-white font-bold text-sm group-hover:opacity-[100%] opacity-[60%] duration-150 uppercase leading-[5px]"></span>
                    <span>
                      <img src="<?php echo get_template_directory_uri(); ?>/assets/images/material-symbols_language.svg" alt="Language Icon" class="duration-150 w-8 max-w-8 w-auto object-contain group-hover:opacity-[100%] opacity-[60%]">
                    </span>
                </button>

                <!-- Всплывающее меню с языками -->
                <div x-show="open" @click.away="open = false"
                    x-transition:enter="transition ease-out duration-200"
                    x-transition:enter-start="opacity-0 translate-y-2"
                    x-transition:enter-end="opacity-100 translate-y-0"
                    x-transition:leave="transition ease-in duration-150"
                    x-transition:leave-start="opacity-100 translate-y-0"
                    x-transition:leave-end="opacity-0 translate-y-2"
                    class="absolute right-0 mt-2 w-72 bg-[#131313]/50 backdrop-blur-md border-[1px] border-[#fff]/10 text-gray-300 rounded-md shadow-lg z-50">
                    <ul id="custom-lang-options-mobile" class="grid grid-cols-2 p-4">
                        <!-- Языки будут динамически добавлены сюда -->
                    </ul>
                </div>
            </div>
        `;

        // Вставляем кастомный dropdown для десктопа перед оригинальным select
        originalSelect.parentNode.insertBefore(customDropdownDesktop, originalSelect);
        // Вставляем кастомный dropdown для мобильного перед оригинальным select
        originalSelect.parentNode.insertBefore(customDropdownMobile, originalSelect);

        // Сюда будем добавлять опции
        const customOptionsContainerDesktop = customDropdownDesktop.querySelector('#custom-lang-options-desktop');
        const customOptionsContainerMobile = customDropdownMobile.querySelector('#custom-lang-options-mobile');

        // Динамически создаем пункты меню на основе оригинального select
        options.forEach(option => {
            if (option.value) {
                const customOptionDesktop = document.createElement('li');
                const customOptionMobile = document.createElement('li');

                customOptionDesktop.classList.add('px-4', 'py-2', 'hover:bg-[#262626]', 'flex', 'items-center', 'gap-2', 'cursor-pointer', 'rounded-md');
                customOptionMobile.classList.add('px-4', 'py-2', 'hover:bg-[#262626]', 'flex', 'items-center', 'gap-2', 'cursor-pointer', 'rounded-md');

                customOptionDesktop.textContent = option.text;
                customOptionMobile.textContent = option.text;

                customOptionDesktop.setAttribute('data-value', option.value);
                customOptionMobile.setAttribute('data-value', option.value);

                // Обработчик клика на кастомную опцию для десктопа
                customOptionDesktop.addEventListener('click', function () {
                    originalSelect.value = option.value;
                    originalSelect.dispatchEvent(new Event('change')); // Имитируем выбор языка
                    
                    // Обновляем выбранный язык в десктопном меню
                    const langSpanDesktop = document.getElementById('desktop-lang');
                    langSpanDesktop.innerText = option.text;

                    // Обновляем выбранный язык в мобильном меню
                    const langSpanMobile = document.getElementById('mobile-lang');
                    langSpanMobile.innerText = option.text;
                });

                // Обработчик клика на кастомную опцию для мобильного
                customOptionMobile.addEventListener('click', function () {
                    originalSelect.value = option.value;
                    originalSelect.dispatchEvent(new Event('change')); // Имитируем выбор языка
                    
                    // Обновляем выбранный язык в десктопном меню
                    const langSpanDesktop = document.getElementById('desktop-lang');
                    langSpanDesktop.innerText = option.text;

                    // Обновляем выбранный язык в мобильном меню
                    const langSpanMobile = document.getElementById('mobile-lang');
                    langSpanMobile.innerText = option.text;
                });

                // Добавляем кастомные опции в контейнеры
                customOptionsContainerDesktop.appendChild(customOptionDesktop);
                customOptionsContainerMobile.appendChild(customOptionMobile);
            }
        });
    }
});

let menuOpen = false; // Переменная, которая отслеживает состояние меню


document.getElementById('mobile-menu-button').addEventListener('click', function () {
    const mobileMenu = document.getElementById('mobile-menu');
    const body = document.getElementsByTagName('body')[0];

    if (!menuOpen) {
        // Открыть меню
        mobileMenu.classList.remove('opacity-0', 'translate-x-[200px]');
        mobileMenu.classList.toggle('pointer-events-none');
        mobileMenu.classList.add('opacity-1');
        body.classList.add('overflow-hidden');
        menuOpen = true; // Меню открыто
    } else {
        // Закрыть меню
        mobileMenu.classList.remove('opacity-1');
        mobileMenu.classList.toggle('pointer-events-none');
        mobileMenu.classList.add('opacity-0', 'translate-x-[200px]');
        body.classList.remove('overflow-hidden');
        menuOpen = false; // Меню закрыто
    }
});

// Закрытие меню при клике вне его
document.addEventListener('click', function (event) {
    const mobileMenu = document.getElementById('mobile-menu');
    const mobileMenuButton = document.getElementById('mobile-menu-button');

    if (menuOpen && !mobileMenu.contains(event.target) && !mobileMenuButton.contains(event.target)) {
        const body = document.getElementsByTagName('body')[0];
        mobileMenu.classList.remove('opacity-1');
        mobileMenu.classList.add('opacity-0', 'translate-x-[500px]');
        mobileMenu.classList.toggle('pointer-events-none');
        body.classList.remove('overflow-hidden');
        menuOpen = false;
    }
});


</script>

</body>
</html>
