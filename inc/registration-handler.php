<?php
require_once 'mongodb-handler.php'; // Подключение к MongoDB

function handle_user_registration() {
    if (isset($_POST['phone'], $_POST['fio'], $_POST['region'], $_POST['age'], $_POST['gender'])) {
        // Получение данных из формы
        $phone = sanitize_text_field($_POST['phone']);
        $fio = sanitize_text_field($_POST['fio']);
        $region = sanitize_text_field($_POST['region']);
        $age = sanitize_text_field($_POST['age']);
        $gender = sanitize_text_field($_POST['gender']);

        // Проверка, существует ли уже пользователь с таким номером телефона
        if (user_exists_by_phone($phone)) {
            wp_send_json_error(array('message' => 'Пользователь с таким номером телефона уже существует.'));
            return;
        }

        if (!isset($_POST['h-captcha-response']) || !verify_hcaptcha($_POST['h-captcha-response'])) {
            wp_send_json_error(array('message' => 'Ошибка валидации hCaptcha.'));
            return;
        }

        // Генерация случайного пароля
        $password = generate_random_password(6);

        // Сохранение данных в MongoDB с полем admin по умолчанию false
        $saved = save_user_to_mongo($phone, $fio, $region, $age, $gender, $password, false); // Передаем false для admin

        if ($saved) {
            // Отправляем успешный ответ с паролем
            wp_send_json_success(array('password' => $password));
        } else {
            wp_send_json_error(array('message' => 'Не удалось сохранить данные в базу.'));
        }
    } else {
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

