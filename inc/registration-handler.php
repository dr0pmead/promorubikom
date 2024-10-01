<?php
require_once 'mongodb-handler.php'; // Подключение к MongoDB

function handle_ticket_registration() {
    // Проверяем nonce, время отправки формы и наличие данных
    if (isset($_POST['phone'], $_POST['fio'], $_POST['region'], $_POST['age'], $_POST['gender'], $_POST['promoaction'])) {
        $phone = sanitize_text_field($_POST['phone']);
        $fio = sanitize_text_field($_POST['fio']);
        $region = sanitize_text_field($_POST['region']);
        $age = sanitize_text_field($_POST['age']);
        $gender = sanitize_text_field($_POST['gender']);
        $promoaction = sanitize_text_field($_POST['promoaction']); // ID акции

        // Проверка, требуется ли загружать файл или текст
        if (isset($_FILES['receipt_image']) && !empty($_FILES['receipt_image']['name'])) {
            $uploaded_file = $_FILES['receipt_image'];

            // Логика загрузки файла и сохранение пути в БД
            $upload_dir = wp_upload_dir();
            $upload_path = $upload_dir['basedir'] . '/receipts/';
            if (!file_exists($upload_path)) {
                mkdir($upload_path, 0755, true);
            }
            $file_name = sanitize_file_name($uploaded_file['name']);
            $file_path = $upload_path . $file_name;
            if (move_uploaded_file($uploaded_file['tmp_name'], $file_path)) {
                $path_to = '/wp-content/uploads/receipts/' . $file_name;

                // Логика сохранения данных с путём до изображения в MongoDB
                $saved = save_ticket_to_mongo($phone, $fio, $region, $age, $gender, $path_to, $promoaction);
                if ($saved) {
                    wp_send_json_success(array('message' => 'Чек успешно зарегистрирован.'));
                } else {
                    wp_send_json_error(array('message' => 'Не удалось сохранить данные в базу.'));
                }
            } else {
                wp_send_json_error(array('message' => 'Ошибка загрузки файла.'));
            }
        } elseif (isset($_POST['custom_text']) && !empty($_POST['custom_text'])) {
            $custom_text = sanitize_text_field($_POST['custom_text']);

            // Логика сохранения текста в MongoDB
            $saved = save_ticket_to_mongo($phone, $fio, $region, $age, $gender, $custom_text, $promoaction);
            if ($saved) {
                wp_send_json_success(array('message' => 'Участие успешно зарегистрировано.'));
            } else {
                wp_send_json_error(array('message' => 'Не удалось сохранить данные в базу.'));
            }
        } else {
            wp_send_json_error(array('message' => 'Не все поля заполнены.'));
        }
    } else {
        wp_send_json_error(array('message' => 'Не все поля заполнены.'));
    }
}

function save_ticket_to_mongo($phone, $fio, $region, $age, $gender, $path_or_text, $promoaction) {
    $db = get_mongo_connection();
    $collection = $db->tickets;

    // Генерация случайного номера тикета
    $ticket = '#' . str_pad(rand(10, 1000000), 7, '0', STR_PAD_LEFT);

    // Данные для сохранения
    $ticket_data = [
        'ticket_number' => $ticket,
        'phone' => $phone,
        'fio' => $fio,
        'region' => $region,
        'age' => $age,
        'gender' => $gender,
        'promoaction' => $promoaction,
        'path_or_text' => $path_or_text,
        'status' => 'pending',
        'upload_date' => new MongoDB\BSON\UTCDateTime(time() * 1000)
    ];

    // Сохраняем данные
    $insertResult = $collection->insertOne($ticket_data);
    return $insertResult->isAcknowledged();
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