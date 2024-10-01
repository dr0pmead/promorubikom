<?php
require_once 'mongodb-handler.php'; // Подключение к MongoDB

function handle_ticket_registration() {
    // Проверяем время отправки формы для защиты от автоматизированных отправок
    if (isset($_POST['form_time']) && is_form_submitted_too_fast(intval($_POST['form_time']))) {
        wp_send_json_error(array('message' => 'Форма отправлена слишком быстро. Попробуйте снова.'));
        return;
    }

    // Проверяем наличие всех необходимых данных
    if (isset($_POST['phone'], $_POST['fio'], $_POST['region'], $_POST['age'], $_POST['gender']) && isset($_FILES['receipt_image'])) {
        $phone = sanitize_text_field($_POST['phone']);
        $fio = sanitize_text_field($_POST['fio']);
        $region = sanitize_text_field($_POST['region']);
        $age = sanitize_text_field($_POST['age']);
        $gender = sanitize_text_field($_POST['gender']);
        $uploaded_file = $_FILES['receipt_image'];

        // Проверка частоты запросов по номеру телефона
        if (is_rate_limited($phone)) {
            wp_send_json_error(array('message' => 'Слишком много запросов. Попробуйте позже.'));
            return;
        }

        // Убедимся, что директория для загрузки файлов существует
        $upload_dir = wp_upload_dir();
        $upload_path = $upload_dir['basedir'] . '/receipts/';

        if (!file_exists($upload_path)) {
            mkdir($upload_path, 0755, true); // Создаём папку, если её нет
        }

        // Генерация имени файла и загрузка файла
        $file_name = sanitize_file_name($uploaded_file['name']);
        $file_path = $upload_path . $file_name;

        if (move_uploaded_file($uploaded_file['tmp_name'], $file_path)) {
            $path_to = '/wp-content/uploads/receipts/' . $file_name; // Путь для сохранения в базе без абсолютного URL

            // Сохранение данных в MongoDB в коллекцию "tickets"
            $saved = save_ticket_to_mongo($phone, $fio, $region, $age, $gender, $path_to);

            if ($saved) {
                wp_send_json_success(array('message' => 'Чек успешно зарегистрирован.'));
            } else {
                wp_send_json_error(array('message' => 'Не удалось сохранить данные в базу.'));
            }
        } else {
            wp_send_json_error(array('message' => 'Ошибка загрузки файла.'));
        }
    } else {
        wp_send_json_error(array('message' => 'Не все поля заполнены или отсутствует файл.'));
    }
}

// Функция для сохранения данных в MongoDB
function save_ticket_to_mongo($phone, $fio, $region, $age, $gender, $path_to) {
    $db = get_mongo_connection();
    $collection = $db->tickets;

    // Генерация случайного номера тикета
    $random_number = str_pad(rand(10, 1000000), 7, '0', STR_PAD_LEFT);
    $ticket = '#' . $random_number;

    // Текущее время + 5 часов (для соответствующего временного пояса)
    $currentTimestamp = time() + (5 * 60 * 60);

    // Преобразуем в MongoDB\BSON\UTCDateTime
    $dateWithOffset = new MongoDB\BSON\UTCDateTime($currentTimestamp * 1000);

    // Данные для сохранения
    $ticket_data = [
        'ticket_number' => $ticket,
        'phone' => $phone,
        'fio' => $fio,
        'region' => $region,
        'age' => $age,
        'gender' => $gender,
        'path_to' => $path_to,
        'status' => 'pending',
        'upload_date' => $dateWithOffset // Добавляем временную метку
    ];

    // Сохраняем в коллекцию
    $insertResult = $collection->insertOne($ticket_data);

    return $insertResult->isAcknowledged(); // Проверяем, прошло ли сохранение успешно
}

// Функция для проверки времени отправки формы (анти-спам)
function is_form_submitted_too_fast($form_time) {
    $current_time = time();
    $minimum_time = 3; // Минимум 3 секунды
    return ($current_time - $form_time < $minimum_time);
}

// Проверка частоты запросов
function is_rate_limited($phone) {
    $transient_name = 'rate_limit_' . md5($phone);
    
    if (get_transient($transient_name)) {
        return true; // Ограничение частоты
    } else {
        set_transient($transient_name, true, 60); // 60 секунд лимит
        return false;
    }
}

// Подключаем обработчик для AJAX
add_action('wp_ajax_nopriv_register_user', 'handle_ticket_registration');
add_action('wp_ajax_register_user', 'handle_ticket_registration');