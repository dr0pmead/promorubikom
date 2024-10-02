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

function get_pending_tickets($promoaction = null) {
    // Подключаемся к MongoDB
    $db = get_mongo_connection();
    $collection = $db->tickets; // Коллекция 'tickets'

    $filter = ['status' => 'pending'];

    // Если есть фильтр по акции, добавляем его в запрос
    if ($promoaction) {
        $filter['promoaction'] = $promoaction;
    }

    try {
        // Ищем тикеты с учетом фильтра
        $pendingTickets = $collection->find($filter);

        // Преобразуем результаты в массив
        $pending_tickets_array = iterator_to_array($pendingTickets);
        return $pending_tickets_array;
    } catch (Exception $e) {
        error_log("Ошибка при получении тикетов: " . $e->getMessage());
        return [];
    }
}

add_action('wp_ajax_filter_tickets_by_promoaction', 'filter_tickets_by_promoaction');
add_action('wp_ajax_nopriv_filter_tickets_by_promoaction', 'filter_tickets_by_promoaction');

function filter_tickets_by_promoaction() {
    // Получаем значение акции из запроса
    $promoaction = isset($_POST['promoaction']) ? sanitize_text_field($_POST['promoaction']) : null;

    // Получаем тикеты по выбранной акции
    $pendingTickets = get_pending_tickets($promoaction);

    if (empty($pendingTickets)) {
        echo '<div class="nodata flex flex-col justify-center items-center text-center text-white font-bold text-xl w-full h-[400px] lg:h-[700px]">
              <img src="' . get_template_directory_uri() . '/assets/images/no-data.webp" alt="Нет данных" class="w-[45%] sm:w-[35%] md:[w-30%] lg:w-[25%] mx-auto">
              </div>';
    } else {
        foreach ($pendingTickets as $ticket) {
            $filename = htmlspecialchars($ticket['ticket_number']);
            $uploadDate = isset($ticket['upload_date']) && $ticket['upload_date'] instanceof MongoDB\BSON\UTCDateTime 
                ? date('d.m.Y H:i', $ticket['upload_date']->toDateTime()->getTimestamp()) 
                : 'Дата не указана';
            $type = isset($ticket['type']) ? $ticket['type'] : 'photo';
            $path_or_text = isset($ticket['path_or_text']) ? htmlspecialchars($ticket['path_or_text']) : ''; // Получаем path_or_text

            echo '<div class="ticket-item bg-[#131313] border-[1px] border-white/10 rounded-lg p-4 text-white w-full ' . ($type !== 'text' ? 'cursor-pointer' : '') . '"
                        data-image-path="' . ($type !== 'text' ? $path_or_text : '') . '" ' . ($type !== 'text' ? 'onclick="openImageModal(\'' . $path_or_text . '\')"' : '') . '>
                        <div class="flex items-center justify-between gap-4">
                          <div class="flex gap-3">
                              <span>
                                  <img src="' . get_template_directory_uri() . '/assets/images/lets-icons_ticket-duotone.svg" alt="Тикет" class="w-8">
                              </span>
                              <div class="flex flex-col gap-2 justify-start">';
            echo                '<span class="text-lg md:text-xl font-bold leading-3">' . ($type !== 'text' ? $filename : $filename . ' - ' . $path_or_text) . '</span>';
            echo                '<span class="text-sm text-gray-400 leading-3">' . $uploadDate . '</span>
                              </div>
                          </div>
                          <div class="flex gap-3 items-center">
                           <button id="submit-delete" class="delete-ticket-btn flex items-center justify-center px-2 sm:px-6 md:px-8 py-2 duration-150 bg-[#131313] hover:bg-red-500/10 border-[1px] border-[#fff]/10 rounded-md"
                                   data-ticket-id="' . $ticket['_id'] . '" onclick="event.stopPropagation(); deleteTicket(this)">
                                   <span class="w-5 sm:w-[80px] text-center">
                               <div class="btn-text">
                                    <span class="hidden sm:block">Удалить</span>
                                    <span class="block sm:hidden"><img src="' . get_template_directory_uri() . '/assets/images/lets-icons_trash-duotone.svg" alt="trash" class="w-6"></span>
                                    </div>
                                   <span class="btn-spinner hidden animate-spinh-5 w-5 mx-auto">
                                       <svg viewBox="0 0 16 16" xmlns="http://www.w3.org/2000/svg">
                                       <path d="M2.501 8C2.501 6.91221 2.82357 5.84884 3.42792 4.94437C4.03227 4.0399 4.89125 3.33495 5.89624 2.91867C6.90123 2.50238 8.0071 2.39347 9.074 2.60568C10.1409 2.8179 11.1209 3.34173 11.8901 4.11092C12.6593 4.8801 13.1831 5.86011 13.3953 6.92701C13.6075 7.9939 13.4986 9.09977 13.0823 10.1048C12.6661 11.1098 11.9611 11.9687 11.0566 12.5731C10.1522 13.1774 9.0888 13.5 8.001 13.5C7.80209 13.4999 7.61127 13.5788 7.47052 13.7193C7.32978 13.8599 7.25063 14.0506 7.2505 14.2495C7.25037 14.4484 7.32926 14.6392 7.46982 14.78C7.61037 14.9207 7.80109 14.9999 8 15C9.38447 15 10.7378 14.5895 11.889 13.8203C13.0401 13.0511 13.9373 11.9579 14.4672 10.6788C14.997 9.3997 15.1356 7.99224 14.8655 6.63437C14.5954 5.2765 13.9287 4.02922 12.9497 3.05026C11.9708 2.07129 10.7235 1.4046 9.36563 1.13451C8.00777 0.86441 6.6003 1.00303 5.32122 1.53285C4.04213 2.06266 2.94888 2.95987 2.17971 4.11101C1.41054 5.26216 1 6.61553 1 8C1 8.19905 1.07907 8.38994 1.21982 8.53069C1.36056 8.67143 1.55145 8.7505 1.7505 8.7505C1.94954 8.7505 2.14044 8.67143 2.28118 8.53069C2.42193 8.38994 2.501 8.19905 2.501 8Z" fill="#fff"/>
                                       </svg>
                                   </span>
                               </span>
                           </button>

                           <button id="submit-approve" class="approve-ticket-btn flex items-center justify-center px-2 sm:px-6 md:px-8 py-2 duration-150 bg-[#131313] hover:bg-green-500/10 border-[1px] border-[#fff]/10 rounded-md"
                                   data-ticket-id="' . $ticket['_id'] . '" onclick="event.stopPropagation(); approveTicket(this)">
                               <span class="w-5 h-5 sm:w-[80px] text-center flex items-center justify-center sm:h-auto">
                                   <div class="btn-text">
                                    <span class="hidden sm:block">Принять</span>
                                    <span class="block sm:hidden"><img src="' . get_template_directory_uri() . '/assets/images/approved-icon.svg" alt="trash" class="w-6"></span>
                                    </div>
                                   <span class="btn-spinner hidden animate-spin  h-5 w-5 mx-auto">
                                       <svg viewBox="0 0 16 16" xmlns="http://www.w3.org/2000/svg">
                                       <path d="M2.501 8C2.501 6.91221 2.82357 5.84884 3.42792 4.94437C4.03227 4.0399 4.89125 3.33495 5.89624 2.91867C6.90123 2.50238 8.0071 2.39347 9.074 2.60568C10.1409 2.8179 11.1209 3.34173 11.8901 4.11092C12.6593 4.8801 13.1831 5.86011 13.3953 6.92701C13.6075 7.9939 13.4986 9.09977 13.0823 10.1048C12.6661 11.1098 11.9611 11.9687 11.0566 12.5731C10.1522 13.1774 9.0888 13.5 8.001 13.5C7.80209 13.4999 7.61127 13.5788 7.47052 13.7193C7.32978 13.8599 7.25063 14.0506 7.2505 14.2495C7.25037 14.4484 7.32926 14.6392 7.46982 14.78C7.61037 14.9207 7.80109 14.9999 8 15C9.38447 15 10.7378 14.5895 11.889 13.8203C13.0401 13.0511 13.9373 11.9579 14.4672 10.6788C14.997 9.3997 15.1356 7.99224 14.8655 6.63437C14.5954 5.2765 13.9287 4.02922 12.9497 3.05026C11.9708 2.07129 10.7235 1.4046 9.36563 1.13451C8.00777 0.86441 6.6003 1.00303 5.32122 1.53285C4.04213 2.06266 2.94888 2.95987 2.17971 4.11101C1.41054 5.26216 1 6.61553 1 8C1 8.19905 1.07907 8.38994 1.21982 8.53069C1.36056 8.67143 1.55145 8.7505 1.7505 8.7505C1.94954 8.7505 2.14044 8.67143 2.28118 8.53069C2.42193 8.38994 2.501 8.19905 2.501 8Z" fill="#fff"/>
                                       </svg>
                                   </span>
                               </span>
                           </button>
                       </div>
                      </div>
                  </div>';
        }
    }

    wp_die();
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
        return;
    }

    $ticketId = sanitize_text_field($_POST['ticket_id']);

    // Подключаемся к MongoDB
    $db = get_mongo_connection();
    $ticketsCollection = $db->tickets;
    $statisticsCollection = $db->statistics;

    // Ищем тикет по ID
    $ticket = $ticketsCollection->findOne(['_id' => new MongoDB\BSON\ObjectId($ticketId)]);

    if (!$ticket) {
        wp_send_json_error(['message' => 'Тикет не найден']);
        return;
    }

    // Обновляем статус тикета в коллекции tickets
    $result = $ticketsCollection->updateOne(
        ['_id' => new MongoDB\BSON\ObjectId($ticketId)],
        ['$set' => ['status' => 'approved']]
    );

    if ($result->getModifiedCount() > 0) {
        // Дублируем тикет в коллекцию statistics
        try {
            // Копируем все поля тикета, добавляя дату копирования
            $statisticsCollection->insertOne([
                'ticket_id' => (string)$ticket['_id'],
                'ticket_number' => $ticket['ticket_number'],
                'phone' => $ticket['phone'],
                'fio' => $ticket['fio'],
                'region' => $ticket['region'],
                'age' => $ticket['age'],
                'gender' => $ticket['gender'],
                'path' => $ticket['path_to'],
                'status' => 'approved', // Статус "approved" для дубликата
                'upload_date' => $ticket['upload_date'],
            ]);

            wp_send_json_success(['message' => 'Тикет успешно обновлен и дублирован']);
        } catch (Exception $e) {
            wp_send_json_error(['message' => 'Ошибка при дублировании тикета: ' . $e->getMessage()]);
        }
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
    $lotteryName = sanitize_text_field($_POST['lottery_name']);
    $promoaction = isset($_POST['promoaction']) ? sanitize_text_field($_POST['promoaction']) : '';

    // Проверка корректности количества участников
    if (!is_numeric($participantCount) || $participantCount < 1 || $participantCount > 1000) {
        wp_send_json_error(['message' => 'Некорректное количество участников']);
        return;
    }

    // Подключаемся к MongoDB
    $db = get_mongo_connection();
    if (!$db) {
        wp_send_json_error(['message' => 'Ошибка подключения к базе данных']);
        return;
    }

    $ticketsCollection = $db->tickets;
    $lotteryCollection = $db->lottery;

    // Формируем запрос с учетом статуса, акции и участвовавших ранее
    $query = [
        'status' => 'approved',
        'participated' => ['$ne' => true] // Только те, кто еще не участвовал
    ];

    if (!empty($promoaction)) {
        $query['promoaction'] = $promoaction; // Фильтр по акции
    }

    // Ищем тикеты по условиям
    try {
        $approvedTickets = $ticketsCollection->find($query)->toArray();
    } catch (Exception $e) {
        wp_send_json_error(['message' => 'Ошибка при поиске тикетов: ' . $e->getMessage()]);
        return;
    }

    // Проверяем, достаточно ли тикетов для проведения розыгрыша
    if (count($approvedTickets) < $participantCount) {
        wp_send_json_error(['message' => 'Недостаточно тикетов для розыгрыша']);
        return;
    }
    

    // Перемешиваем массив и выбираем первые N тикетов
    shuffle($approvedTickets);
    $winningTickets = array_slice($approvedTickets, 0, $participantCount);

    $formattedWinningTickets = [];

    foreach ($winningTickets as $ticket) {
        // Помечаем тикет как участвовавший
        $ticketsCollection->updateOne(
            ['_id' => new MongoDB\BSON\ObjectId($ticket['_id'])],
            ['$set' => ['participated' => true]]
        );

        // Формируем массив данных победителей
        $formattedWinningTickets[] = [
            'ticket_id' => (string) $ticket['_id'],
            'file_name' => $ticket['file_name'],
            'path_or_text' => $ticket['path_or_text'],
            'type' => $ticket['type'],
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
            'numberLottery' => $lotteryName, // Номер лотереи
            'promoaction' => $promoaction // Сохраняем выбранную акцию
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

            
            // Дополняем данные с promoaction
            $promoaction_basename = $lottery['promoaction']; // Получаем basename (например, из MongoDB или переданного значения)
            $promoaction_title = '';

            if (!empty($promoaction_basename)) {
                // Используем WP_Query для поиска по basename (slug)
                $promoactions_query = new WP_Query(array(
                    'post_type' => 'promoactions',
                    'name'      => $promoaction_basename, // Поиск по slug (basename)
                    'posts_per_page' => 1
                ));

                // Если пост найден, получаем его заголовок
                if ($promoactions_query->have_posts()) {
                    $promoactions_query->the_post();
                    $promoaction_title = get_the_title();
                    wp_reset_postdata();
                } else {
                    $promoaction_title = 'Неизвестная акция';
                }
            }

            
            // Форматируем выигрышные тикеты
            foreach ($lottery['winning_tickets'] as $ticket) {
                $formattedWinningTickets[] = [
                    'ticket_id' => $ticket['ticket_id'], // ticket_id теперь строка
                    'file_name' => $ticket['ticket_number'], // Используем ticket_number вместо file_name
                    'path_or_text' => htmlspecialchars($ticket['path_or_text']),
                    'type' => $ticket['type'],
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
                    'winning_tickets' => $formattedWinningTickets,
                    'date' => $lottery['date']->toDateTime()->format('d.m.Y H:i'),
                    'promoaction' => $promoaction_title,
                    
                ]
            ]);
        } else {
            wp_send_json_error(['message' => 'Лотерея не найдена']);
        }
    } catch (Exception $e) {
        wp_send_json_error(['message' => 'Ошибка при получении данных: ' . $e->getMessage()]);
    }
}

function get_latest_lottery_details() {
    // Подключаемся к MongoDB
    $db = get_mongo_connection();
    $lotteryCollection = $db->lottery;

    try {
        // Ищем последнюю лотерею (по дате создания)
        $latestLottery = $lotteryCollection->findOne([], ['sort' => ['date' => -1]]);

        if ($latestLottery) {
            // Подготавливаем данные для фронтенда
            $formattedWinningTickets = [];

            foreach ($latestLottery['winning_tickets'] as $ticket) {
                $formattedWinningTickets[] = [
                    'ticket_id' => (string) $ticket['ticket_id'],
                    'path' => $ticket['path'],
                    'owner' => [
                        'fio' => $ticket['owner']['fio'],
                        'phone' => $ticket['owner']['phone'],
                        'region' => $ticket['owner']['region']
                    ]
                ];
            }

            // Возвращаем данные лотереи
            wp_send_json_success(['data' => [
                'numberLottery' => $latestLottery['numberLottery'],
                'participant_count' => $latestLottery['participant_count'],
                'winning_tickets' => $formattedWinningTickets
            ]]);
        } else {
            wp_send_json_error(['message' => 'Лотерея не найдена']);
        }
    } catch (Exception $e) {
        wp_send_json_error(['message' => 'Ошибка при запросе данных: ' . $e->getMessage()]);
    }
}

// Подключаем обработчик для AJAX
add_action('wp_ajax_get_latest_lottery_details', 'get_latest_lottery_details');
add_action('wp_ajax_nopriv_get_latest_lottery_details', 'get_latest_lottery_details');

add_action('wp_ajax_get_ticket_stats', 'get_ticket_stats');
add_action('wp_ajax_nopriv_get_ticket_stats', 'get_ticket_stats');

function get_ticket_stats() {
    // Получаем ID акции из запроса
    $promoaction = isset($_POST['promoaction']) ? sanitize_text_field($_POST['promoaction']) : '';

    // Получаем MongoDB
    $db = get_mongo_connection();
    $ticketsCollection = $db->tickets;

    // Формируем запрос для всех тикетов
    $query = [];
    if (!empty($promoaction)) {
        $query['promoaction'] = $promoaction;
    }

    // Получаем общее количество тикетов
    $total_tickets = $ticketsCollection->countDocuments($query);

    // Формируем запрос для участников (approved и participated = false)
    $query['status'] = 'approved';
    $query['participated'] = ['$ne' => true]; // Только те, кто еще не участвовал

    // Получаем количество участников
    $participant_count = $ticketsCollection->countDocuments($query);

    // Возвращаем данные в формате JSON
    wp_send_json_success([
        'total_tickets' => $total_tickets,
        'participant_count' => $participant_count,
    ]);
}

add_action('wp_ajax_filter_lotteries_by_promoaction', 'filter_lotteries_by_promoaction');
add_action('wp_ajax_nopriv_filter_lotteries_by_promoaction', 'filter_lotteries_by_promoaction'); // Если нужна поддержка для неавторизованных пользователей

function filter_lotteries_by_promoaction() {
    if (!isset($_POST['promoaction'])) {
        wp_send_json_error(['message' => 'Не указана акция']);
        return;
    }

    $promoaction = sanitize_text_field($_POST['promoaction']);

    // Подключаемся к MongoDB
    $db = get_mongo_connection();
    $lotteryCollection = $db->lottery;

    // Если выбрана конкретная акция, фильтруем по ней, иначе выбираем все
    $query = [];
    if (!empty($promoaction)) {
        $query['promoaction'] = $promoaction;
    }

    try {
        $lotteries = $lotteryCollection->find($query)->toArray();
    } catch (Exception $e) {
        wp_send_json_error(['message' => 'Ошибка при поиске лотерей: ' . $e->getMessage()]);
        return;
    }

    // Если лотерей нет, возвращаем пустой ответ
    if (empty($lotteries)) {
        echo '<div class="nodata flex flex-col justify-center items-center text-center text-white font-bold text-xl w-full h-[400px] lg:h-[650px]">
                <img src="' . get_template_directory_uri() . '/assets/images/no-data.webp" alt="Нет данных" class="w-[45%] sm:w-[35%] md:[w-30%] lg:w-[25%] mx-auto">
              </div>';
        wp_die();
    }

    // Выводим отфильтрованные лотереи
    foreach ($lotteries as $lottery) {
        echo '<div class="lottery-item bg-[#131313] border-[1px] border-white/10 rounded-lg p-4 text-white w-full cursor-pointer duration-150"
              data-id="' . $lottery['_id'] . '">
              <div class="flex items-center justify-between">
                  <div class="flex flex-col gap-3">
                      <span class="text-xl font-bold leading-3">' . htmlspecialchars($lottery['numberLottery'] ?? 'Неизвестный номер') . '</span>
                      <div class="text-sm text-gray-400">Количество участников: ' . intval($lottery['participant_count'] ?? 0) . '</div>
                  </div>
                  <button class="p-2 md:py-2 md:px-6  text-white font-bold rounded-md bg-[#131313] border-[1px] border-white/10 hover:bg-[#222222]"
                          @click="openDetails = true" onclick="fetchLotteryDetails(\'' . $lottery['_id'] . '\')">
                      <span class="md:flex hidden">Посмотреть детали</span>
                      <span class="md:hidden flex items-center"><img src="' . get_template_directory_uri() . '/assets/images/tabler_list-details.svg" alt="party" class="w-6"></span>
                  </button>
              </div>
          </div>';
    }

    wp_die(); // Останавливаем выполнение PHP скрипта
}