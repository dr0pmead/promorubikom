<?php
// В functions.php добавляем новый action для AJAX запроса
add_action('wp_ajax_get_user_data', 'get_user_data');
add_action('wp_ajax_nopriv_get_user_data', 'get_user_data'); // Для неавторизованных пользователей, если нужно

function get_user_data() {
    header('Content-Type: application/json');

    // Проверка, есть ли куки с user_id
    if (!isset($_COOKIE['user_id'])) {
        echo json_encode(['success' => false, 'message' => 'Пользователь не авторизован']);
        wp_die();
    }

    // Получаем ID пользователя из куки
    $user_id = $_COOKIE['user_id'];

    try {
        // Подключаемся к MongoDB и находим пользователя по его ID
        $collection = get_mongo_connection()->users; // Используем коллекцию users
        $user = $collection->findOne(['_id' => new MongoDB\BSON\ObjectId($user_id)]); // Находим пользователя по ObjectId

        if ($user) {
            // Возвращаем данные пользователя
            echo json_encode([
                'success' => true,
                'data' => [
                    'fio' => $user['fio'],
                    'phone' => $user['phone'],
                    'age' => $user['age'],
                    'region' => $user['region'],
                    'gender' => $user['gender'] === 'male' ? 'Мужской' : 'Женский',
                ]
            ]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Пользователь не найден']);
        }
    } catch (MongoDB\Exception\Exception $e) {
        error_log('Ошибка при получении данных пользователя: ' . $e->getMessage());
        echo json_encode(['success' => false, 'message' => 'Ошибка при подключении к базе данных']);
    }

    wp_die();
}

add_action('wp_ajax_change_password', 'change_password');
add_action('wp_ajax_nopriv_change_password', 'change_password'); // Для неавторизованных пользователей (если нужно)

function change_password() {

    header('Content-Type: application/json');

    if (!isset($_COOKIE['user_id'])) {
        echo json_encode(['success' => false, 'message' => 'Пользователь не авторизован']);
        wp_die(); // Закрытие выполнения после отправки
    }

    if (!isset($_POST['new_password']) || empty($_POST['new_password'])) {
        echo json_encode(['success' => false, 'message' => 'Новый пароль не указан']);
        wp_die();
    }

    $new_password = $_POST['new_password'];
    $user_id = $_COOKIE['user_id'];

    // Подключение к MongoDB и обновление пароля
    $collection = get_mongo_connection()->users;
    $user = $collection->findOne(['_id' => new MongoDB\BSON\ObjectId($user_id)]);

    if (!$user) {
        echo json_encode(['success' => false, 'message' => 'Пользователь не найден']);
        wp_die();
    }

    // Хеширование нового пароля и обновление в MongoDB
    $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
    $collection->updateOne(
        ['_id' => new MongoDB\BSON\ObjectId($user_id)],
        ['$set' => ['password' => $hashed_password]]
    );

    error_log("Данные запроса: " . print_r($_POST, true));

    // Возвращаем успешный JSON-ответ
    echo json_encode(['success' => true, 'message' => 'Пароль успешно изменен']);
    wp_die();
}

function get_pending_tickets() {
    // Подключаемся к MongoDB
    $db = get_mongo_connection();
    $collection = $db->tickets; // Коллекция 'tickets'

    try {
        // Ищем все тикеты со статусом "approved"
        $pendingTickets = $collection->find(['status' => 'pending']);

        // Преобразуем результаты в массив
        $pending_tickets_array = iterator_to_array($pendingTickets);
        return $pending_tickets_array;
    } catch (Exception $e) {
        error_log("Ошибка при получении одобренных тикетов: " . $e->getMessage());
        return [];
    }
}


// Для удаления тикета
add_action('wp_ajax_delete_ticket', 'delete_ticket'); // Для авторизованных пользователей
add_action('wp_ajax_nopriv_delete_ticket', 'delete_ticket'); // Для неавторизованных пользователей
function delete_ticket() {
    if (!isset($_POST['ticket_id'])) {
        wp_send_json_error(['message' => 'Не указан ID тикета']);
    }

    $ticketId = sanitize_text_field($_POST['ticket_id']);

    // Подключаемся к MongoDB и удаляем тикет по его ID
    $collection = get_mongo_connection()->tickets;

    $result = $collection->deleteOne(['_id' => new MongoDB\BSON\ObjectId($ticketId)]);

    if ($result->getDeletedCount() > 0) {
        wp_send_json_success(['message' => 'Тикет успешно удален']);
    } else {
        wp_send_json_error(['message' => 'Ошибка удаления тикета']);
    }
}

// Для принятия тикета
add_action('wp_ajax_approve_ticket', 'approve_ticket'); // Для авторизованных пользователей
add_action('wp_ajax_nopriv_approve_ticket', 'approve_ticket'); // Для неавторизованных пользователей
function approve_ticket() {
    if (!isset($_POST['ticket_id'])) {
        wp_send_json_error(['message' => 'Не указан ID тикета']);
    }

    $ticketId = sanitize_text_field($_POST['ticket_id']);

    // Подключаемся к MongoDB и обновляем статус тикета
    $collection = get_mongo_connection()->tickets;

    $result = $collection->updateOne(
        ['_id' => new MongoDB\BSON\ObjectId($ticketId)],
        ['$set' => ['status' => 'approved']]
    );

    if ($result->getModifiedCount() > 0) {
        wp_send_json_success(['message' => 'Тикет успешно обновлен']);
    } else {
        wp_send_json_error(['message' => 'Ошибка изменения статуса тикета']);
    }
}

// Добавляем обработчик для удаления всех тикетов
add_action('wp_ajax_delete_all_tickets', 'delete_all_tickets');
add_action('wp_ajax_nopriv_delete_all_tickets', 'delete_all_tickets'); // Для неавторизованных пользователей (если нужно)

function delete_all_tickets() {
    // Подключаем MongoDB
    $db = get_mongo_connection();
    $ticketsCollection = $db->tickets; // Убедитесь, что это правильное название коллекции с тикетами

    try {
        // Удаляем все записи из коллекции тикетов
        $result = $ticketsCollection->deleteMany([]);
        
        if ($result->getDeletedCount() > 0) {
            wp_send_json_success(['message' => 'Все тикеты успешно удалены.']);
        } else {
            wp_send_json_error(['message' => 'Не удалось удалить тикеты. Возможно, их нет.']);
        }
    } catch (Exception $e) {
        error_log('Ошибка при удалении тикетов: ' . $e->getMessage());
        wp_send_json_error(['message' => 'Произошла ошибка при удалении тикетов.']);
    }
}

add_action('wp_ajax_start_lottery', 'start_lottery');
add_action('wp_ajax_nopriv_start_lottery', 'start_lottery');

function start_lottery() {
    // Проверка на получение количества участников
    if (!isset($_POST['participant_count'])) {
        wp_send_json_error(['message' => 'Не указано количество участников']);
        return;
    }

    $participantCount = intval($_POST['participant_count']);

    // Проверка корректности количества участников
    if (!is_numeric($participantCount) || $participantCount < 1 || $participantCount > 1000) {
        wp_send_json_error(['message' => 'Некорректное количество участников']);
        return;
    }

    // Получаем MongoDB
    $db = get_mongo_connection();
    if (!$db) {
        wp_send_json_error(['message' => 'Ошибка подключения к базе данных']);
        return;
    }

    $ticketsCollection = $db->tickets;
    $lotteryCollection = $db->lottery;

    // Ищем тикеты со статусом "approved"
    try {
        $approvedTickets = $ticketsCollection->find(['status' => 'approved'])->toArray();
    } catch (Exception $e) {
        wp_send_json_error(['message' => 'Ошибка при поиске тикетов: ' . $e->getMessage()]);
        return;
    }

    // Проверяем, достаточно ли тикетов для проведения розыгрыша
    if (count($approvedTickets) < $participantCount) {
        wp_send_json_error(['message' => 'Недостаточно тикетов для розыгрыша']);
        return;
    }

    // Получаем последний номер лотереи, если такой существует
    $lastLottery = $lotteryCollection->findOne([], ['sort' => ['_id' => -1], 'projection' => ['numberLottery' => 1]]);

    // Если есть предыдущая лотерея, увеличиваем её номер, иначе начинаем с #000001
    if ($lastLottery && isset($lastLottery['numberLottery'])) {
        $lastNumber = (int) str_replace('#', '', $lastLottery['numberLottery']);
        $newNumber = $lastNumber + 1;
    } else {
        $newNumber = 1;
    }

    // Преобразуем номер в формат #000001
    $numberLottery = '#' . str_pad($newNumber, 6, '0', STR_PAD_LEFT);

    // Перемешиваем массив и выбираем первые N тикетов
    shuffle($approvedTickets);
    $winningTickets = array_slice($approvedTickets, 0, $participantCount);

    $formattedWinningTickets = [];

    foreach ($winningTickets as $ticket) {
        // Получаем данные непосредственно из тикета
        $formattedWinningTickets[] = [
            'ticket_id' => (string) $ticket['_id'],
            'file_name' => $ticket['file_name'],
            'path' => $ticket['path_to'],
            'owner' => [
                'fio' => $ticket['fio'],
                'phone' => $ticket['phone'],
                'region' => $ticket['region']
            ]
        ];
    }

    // Текущее время + 5 часов
    $currentTimestamp = time() + (5 * 60 * 60);

    // Преобразуем в MongoDB\BSON\UTCDateTime
    $dateWithOffset = new MongoDB\BSON\UTCDateTime($currentTimestamp * 1000);

    // Создаем запись в коллекции "lottery"
    try {
        $lotteryCollection->insertOne([
            'date' => $dateWithOffset, // Текущая дата
            'participant_count' => $participantCount, // Количество участников
            'winning_tickets' => $formattedWinningTickets, // Победные тикеты
            'numberLottery' => $numberLottery // Номер лотереи
        ]);

        wp_send_json_success(['message' => 'Розыгрыш завершен']);
    } catch (Exception $e) {
        wp_send_json_error(['message' => 'Ошибка при сохранении данных розыгрыша: ' . $e->getMessage()]);
        error_log('Ошибка при сохранении данных розыгрыша: ' . $e->getMessage());
    }
}



function get_lottery_records() {
    // Подключаем MongoDB и коллекцию "lottery"
    $db = get_mongo_connection();
    $lotteryCollection = $db->lottery;

    try {
        // Ищем все записи в коллекции лотереи, отсортированные по дате
        $lotteryRecords = $lotteryCollection->find([], ['sort' => ['date' => -1]])->toArray();
        return $lotteryRecords;
    } catch (Exception $e) {
        error_log("Ошибка получения записей лотереи: " . $e->getMessage());
        return [];
    }
}

add_action('wp_ajax_get_lottery_details', 'get_lottery_details');
add_action('wp_ajax_nopriv_get_lottery_details', 'get_lottery_details'); // Если нужно для неавторизованных пользователей

function get_lottery_details() {
    if (!isset($_POST['lottery_id'])) {
        wp_send_json_error(['message' => 'Не указан ID лотереи']);
        return;
    }

    $lotteryId = sanitize_text_field($_POST['lottery_id']);

    // Подключаемся к MongoDB
    $db = get_mongo_connection();
    $lotteryCollection = $db->lottery;

    try {
        // Ищем лотерею по ID
        $lottery = $lotteryCollection->findOne(['_id' => new MongoDB\BSON\ObjectId($lotteryId)]);

        if ($lottery) {
            $formattedWinningTickets = [];

            // Форматируем выигрышные тикеты
            foreach ($lottery['winning_tickets'] as $ticket) {
                $formattedWinningTickets[] = [
                    'ticket_id' => $ticket['ticket_id'], // ticket_id теперь строка
                    'file_name' => $ticket['ticket_number'], // Используем ticket_number вместо file_name
                    'path' => htmlspecialchars($ticket['path']),
                    'owner' => [
                        'fio' => htmlspecialchars($ticket['owner']['fio']),
                        'phone' => htmlspecialchars($ticket['owner']['phone']),
                        'region' => htmlspecialchars($ticket['owner']['region'])
                    ]
                ];
            }

            // Возвращаем данные
            wp_send_json_success([
                'data' => [
                    'lottery_id' => $lotteryId,
                    'numberLottery' => $lottery['numberLottery'] ?? 'Неизвестный номер',
                    'participant_count' => $lottery['participant_count'] ?? 'Неизвестно',
                    'winning_tickets' => $formattedWinningTickets
                ]
            ]);
        } else {
            wp_send_json_error(['message' => 'Лотерея не найдена']);
        }
    } catch (Exception $e) {
        wp_send_json_error(['message' => 'Ошибка при получении данных: ' . $e->getMessage()]);
    }
}

