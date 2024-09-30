<?php

require_once 'mongodb-handler.php'; // подключение к MongoDB

function get_user_button_html() {
    // Проверяем, есть ли куки с user_id
    if (isset($_COOKIE['user_id'])) {
        // Подключаем MongoDB и ищем пользователя по ID
        $db = get_mongo_connection();
        $user_id = $_COOKIE['user_id'];
        $collection = $db->users; // Используем коллекцию 'users'

        try {
            $user = $collection->findOne(['_id' => new MongoDB\BSON\ObjectId($user_id)]);
            if ($user) {
                // Если пользователь найден, выводим HTML с номером телефона
                ?>
                <div x-data="{ open: false }" class="xl:flex items-center gap-3 hidden w-full ml-2 relative">
                    <div @click="open = !open" class="flex items-center py-1.5 px-3 text-nowrap bg-[#131313] text-white rounded-lg duration-150 border-[1px] hover:bg-[#222222] border-[#fff]/10 gap-3 cursor-pointer leading-[5px]">
                        <span class="font-bold text-sm"><?php echo $user['phone']; ?></span>
                        <span class="w-3 object-contain">    
                            <img src="<?php echo get_template_directory_uri(); ?>/assets/images/down_arrow.svg" alt="Logout Icon">
                        </span> 
                    </div>
                    <button id="logout-btn" class="p-1.5 bg-[#131313] hover:bg-[#222222] rounded-lg flex items-center justify-center border-[1px] border-[#fff]/10">
                        <span class="w-6 object-contain">    
                            <img src="<?php echo get_template_directory_uri(); ?>/assets/images/solar_exit-bold-duotone.svg" alt="Logout Icon">
                        </span>    
                    </button>

                    <!-- Выпадающее меню для Личный кабинет / Смена пароля -->
                    <div x-show="open" 
                         @click.away="open = false" 
                         x-transition:enter="transition ease-out duration-300"
                         x-transition:enter-start="opacity-0 transform -translate-y-4"
                         x-transition:enter-end="opacity-100 transform translate-y-0"
                         x-transition:leave="transition ease-in duration-200"
                         x-transition:leave-start="opacity-100 transform translate-y-0"
                         x-transition:leave-end="opacity-0 transform -translate-y-4"
                         class="absolute right-0 mt-2 w-[200px] bg-[#131313]/50 border-[1px] border-[#fff]/10 rounded-lg shadow-lg z-10 top-[40px] left-0 backdrop-blur-md">
                         <a href="/dashboard" class="flex px-4 py-2 text-sm text-white hover:bg-[#fff]/10 rounded-t-lg gap-3 items-center ">
                            <img src="<?php echo get_template_directory_uri(); ?>/assets/images/mdi_account.svg" alt="User Icon" class="w-6">Личный кабинет
                        </a>

                        <?php if (is_mongo_user_admin()) : ?>
                        <a href="/admin-panel" class="flex px-4 py-2 text-sm text-white hover:bg-[#fff]/10 gap-3 items-center ">
                            <img src="<?php echo get_template_directory_uri(); ?>/assets/images/fluent_ticket-diagonal-28-filled.svg" alt="Ticket Icon" class="w-6">Панель управления
                        </a>
                        <?php endif; ?>

                        <?php if (is_mongo_user_admin()) : ?>
                        <a href="/statistics" class="flex px-4 py-2 text-sm text-white hover:bg-[#fff]/10 gap-3 items-center ">
                            <img src="<?php echo get_template_directory_uri(); ?>/assets/images/lets-icons_info-duotone.svg" alt="Ticket Icon" class="w-6">Статистика
                        </a>
                        <?php endif; ?>

                        <button  class="flex w-full px-4 py-2 text-sm text-white hover:bg-[#fff]/10 rounded-b-lg items-center gap-3 " data-remodal-target="modal-change-password">
                            <img src="<?php echo get_template_directory_uri(); ?>/assets/images/fluent_password-48-regular.svg" alt="password Icon" class="w-6">Смена пароля
                        </button>
                    </div>
                </div>
                <?php
                return; // Прерываем выполнение функции после вывода HTML
            }
        } catch (Exception $e) {
            error_log("Ошибка при получении данных пользователя: " . $e->getMessage());
        }
    }

    // Если пользователя нет или не удалось найти, выводим стандартную кнопку входа
    ?>
    <div class="xl:flex items-center space-x-4 hidden ml-6">
        <button id="submit-login" class="bg-[#E53F0B] hover:bg-[#F35726] rounded-md flex items-center justify-between px-10 py-2 gap-3 duration-150 text-nowrap" data-remodal-target="modal-auth">
            <span class="text-white font-bold text-sm">Войти</span>
            <img src="<?php echo get_template_directory_uri(); ?>/assets/images/lets-icons_sign-in-squre-duotone.svg" alt="Login Icon" class="w-6">
        </button>
    </div>
    <?php
}

function get_user_button_html_mobile() {
    // Проверяем, есть ли куки с user_id
    if (isset($_COOKIE['user_id'])) {
        // Подключаем MongoDB и ищем пользователя по ID
        $db = get_mongo_connection();
        $user_id = $_COOKIE['user_id'];
        $collection = $db->users; // Используем коллекцию 'users', замените на нужную вам

        try {
            $user = $collection->findOne(['_id' => new MongoDB\BSON\ObjectId($user_id)]);
            if ($user) {
                // Если пользователь найден, выводим HTML с номером телефона
                ?>
                <div class="items-center gap-3 flex w-full flex-row md:flex-col">
                    <div class="flex items-center py-1.5 px-3 text-nowrap bg-[#131313] text-white rounded-lg border-[1px] border-[#fff]/10 gap-3 leading-[5px] w-full">
                        <span>
                            <img src="<?php echo get_template_directory_uri(); ?>/assets/images/mdi_account.svg" alt="User Icon" class="w-6">
                        </span>
                        <span class="font-bold text-sm"><?php echo $user['phone']; ?></span>
                    </div>
                    <button id="logout-btn" class="p-1.5 bg-[#131313] hover:bg-[#222222] rounded-lg flex items-center justify-center border-[1px] border-[#fff]/10 w-full gap-3">
                    <span class="font-bold text-sm "> Выход </span> <img src="<?php echo get_template_directory_uri(); ?>/assets/images/solar_exit-bold-duotone.svg" alt="Logout Icon" class="w-6">
                    </button>
                </div>
                <?php
                return; // Прерываем выполнение функции после вывода HTML
            }
        } catch (Exception $e) {
            error_log("Ошибка при получении данных пользователя: " . $e->getMessage());
        }
    }

    // Если пользователя нет или не удалось найти, выводим стандартную кнопку входа
    ?>
    <div class="flex items-center space-x-4 xl:hidden ">
        <button id="submit-login" class="bg-[#E53F0B] hover:bg-[#F35726] rounded-md flex items-center justify-between px-8 py-1 gap-3 duration-150 text-nowrap" data-remodal-target="modal-auth">
            <span class="text-white font-bold text-sm">Войти</span>
            <img src="<?php echo get_template_directory_uri(); ?>/assets/images/lets-icons_sign-in-squre-duotone.svg" alt="Login Icon" class="w-7">
        </button>
    </div>
    <?php
}


