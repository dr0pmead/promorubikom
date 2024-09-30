<?php

add_action('wp_ajax_upload_check', 'upload_check');
add_action('wp_ajax_nopriv_upload_check', 'upload_check'); // Если нужно для неавторизованных пользователей

function upload_check() {
    if (!isset($_COOKIE['user_id'])) {
        echo json_encode(['success' => false, 'message' => 'Пользователь не авторизован']);
        wp_die();
    }

    if (!isset($_FILES['file']) || $_FILES['file']['error'] !== UPLOAD_ERR_OK) {
        echo json_encode(['success' => false, 'message' => 'Ошибка загрузки файла']);
        wp_die();
    }

    $file = $_FILES['file'];
    $allowedTypes = ['image/jpeg', 'image/png', 'application/pdf'];
    $maxSize = 10 * 1024 * 1024; // 10 МБ

    if (!in_array($file['type'], $allowedTypes) || $file['size'] > $maxSize) {
        echo json_encode(['success' => false, 'message' => 'Неверный формат файла или слишком большой размер']);
        wp_die();
    }

    $user_id = $_COOKIE['user_id'];
    $collection = get_mongo_connection()->tickets;

    // Генерация случайного номера тикета
    $random_number = str_pad(rand(10, 1000000), 7, '0', STR_PAD_LEFT);
    $file_name = '#' . $random_number;

    // Сохранение файла
    $upload_dir = wp_upload_dir()['basedir'] . '/checks/';
    if (!file_exists($upload_dir)) {
        mkdir($upload_dir, 0777, true);
    }
    $file_path = '/wp-content/uploads/checks/' . basename($file['name']);
    move_uploaded_file($file['tmp_name'], $upload_dir . basename($file['name']));

    // Текущее время + 5 часов
    $currentTimestamp = time() + (5 * 60 * 60);

    // Преобразуем в MongoDB\BSON\UTCDateTime
    $dateWithOffset = new MongoDB\BSON\UTCDateTime($currentTimestamp * 1000);

    // Добавление тикета в коллекцию tickets
    $new_ticket = [
        'owner_id' => new MongoDB\BSON\ObjectId($user_id),
        'file_name' => $file_name,
        'path' => $file_path,
        'upload_date' => $dateWithOffset,
        'status' => 'pending'
    ];

    $collection->insertOne($new_ticket);

    echo json_encode(['success' => true, 'message' => 'Тикет добавлен']);
    wp_die();
}

function get_user_tickets() {
    if (isset($_COOKIE['user_id'])) {
        // Подключаемся к MongoDB и получаем коллекцию тикетов
        $db = get_mongo_connection();
        $user_id = $_COOKIE['user_id'];
        $collection = $db->tickets; // Используем коллекцию 'tickets'

        try {
            // Находим все тикеты пользователя
            $tickets = $collection->find(['owner_id' => new MongoDB\BSON\ObjectId($user_id)]);

            // Преобразуем результаты в массив
            $tickets_array = iterator_to_array($tickets);
            return $tickets_array;
        } catch (Exception $e) {
            error_log("Ошибка при получении тикетов пользователя: " . $e->getMessage());
        }
    }

    // Если пользователь не найден или тикетов нет, возвращаем пустой массив
    return [];
}

