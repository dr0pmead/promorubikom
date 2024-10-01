<?php
require_once 'mongodb-handler.php'; // Подключение к MongoDB

function handle_user_registration() {
    error_log('Запуск регистрации...');

    // Проверяем, все ли данные формы получены
    if (isset($_POST['phone'], $_POST['fio'], $_POST['region'], $_POST['age'], $_POST['gender'], $_POST['h-captcha-response'])) {
        
        // Логируем полученные данные
        error_log('Полученные данные формы: ' . print_r($_POST, true));

        // Получаем данные формы
        $phone = sanitize_text_field($_POST['phone']);
        $fio = sanitize_text_field($_POST['fio']);
        $region = sanitize_text_field($_POST['region']);
        $age = sanitize_text_field($_POST['age']);
        $gender = sanitize_text_field($_POST['gender']);
        $hcaptcha_response = sanitize_text_field($_POST['h-captcha-response']);

        // Проверка hCaptcha с логированием
        if (!verify_hcaptcha_on_registration($hcaptcha_response)) {
            error_log('Проверка hCaptcha не пройдена.');
            wp_send_json_error(array('message' => 'Проверка hCaptcha не пройдена. Пожалуйста, подтвердите, что вы не робот.'));
            return;
        }

        // Проверка, существует ли уже пользователь с таким номером телефона
        if (user_exists_by_phone($phone)) {
            error_log('Пользователь с номером телефона уже существует: ' . $phone);
            wp_send_json_error(array('message' => 'Пользователь с таким номером телефона уже существует.'));
            return;
        }

        // Генерация случайного пароля
        $password = generate_random_password(6);
        error_log('Сгенерированный пароль: ' . $password);

        // Сохранение данных в MongoDB с логированием
        $saved = save_user_to_mongo($phone, $fio, $region, $age, $gender, $password, false);
        error_log('Сохранение в MongoDB: ' . ($saved ? 'Успех' : 'Ошибка'));

        if ($saved) {
            // Отправляем успешный ответ с паролем
            wp_send_json_success(array('password' => $password));
        } else {
            error_log('Не удалось сохранить данные в базу MongoDB.');
            wp_send_json_error(array('message' => 'Не удалось сохранить данные в базу.'));
        }
    } else {
        error_log('Не все поля заполнены.');
        wp_send_json_error(array('message' => 'Не все поля заполнены.'));
    }
}

function user_exists_by_phone($phone) {
    $db = get_mongo_connection();
    $collection = $db->users;

    $user = $collection->findOne(['phone' => $phone]);

    return $user !== null; // Возвращаем true, если пользователь найден, иначе false
}

// Генерация случайного пароля
function generate_random_password($length = 6) {
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $charactersLength = strlen($characters);
    $randomString = '';
    for ($i = 0; $i < $length; $i++) {
        $randomString .= $characters[rand(0, $charactersLength - 1)];
    }
    return $randomString;
}

// Подключаем обработчик для AJAX
add_action('wp_ajax_nopriv_register_user', 'handle_user_registration');
add_action('wp_ajax_register_user', 'handle_user_registration');
