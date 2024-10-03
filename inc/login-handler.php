<?php
use \Firebase\JWT\JWT;
require_once 'mongodb-handler.php'; // подключение к MongoDB

// Главная функция для обработки авторизации
function handle_login_request() {
    header('Content-Type: application/json');

    // Проверка типа запроса
    if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['action']) || $_POST['action'] !== 'login_user') {
        return respond_with_error('Неправильный метод запроса');
    }

    // Получаем данные из POST
    $name = isset($_POST['name']) ? trim($_POST['name']) : null;
    $password = isset($_POST['password']) ? trim($_POST['password']) : null;

    // Валидация ввода
    if (!$name || !$password) {
        return respond_with_error('Необходимо ввести телефон и пароль');
    }

    // Проверка блокировки IP-адреса для предотвращения брутфорс-атак
    $ip_address = $_SERVER['REMOTE_ADDR'];
    $failed_attempts = get_failed_attempts($ip_address); // Функция для получения количества неудачных попыток входа
    $max_attempts = 5; // Максимальное количество попыток
    $lockout_time = 15 * 60; // Время блокировки 15 минут

    if ($failed_attempts >= $max_attempts) {
        $last_attempt_time = get_last_attempt_time($ip_address); // Получаем время последней попытки
        if (time() - $last_attempt_time < $lockout_time) {
            return respond_with_error('Превышено количество попыток. Повторите через 15 минут.');
        } else {
            reset_failed_attempts($ip_address); // Сброс попыток после истечения времени блокировки
        }
    }

    // Проверяем пользователя по номеру телефона
    $user = find_user_by_phone($name);
    if (!$user) {
        log_failed_attempt($ip_address); // Логируем неудачную попытку
        return respond_with_error('Пользователь не найден');
    }

    // Проверяем пароль
    if (!password_verify($password, $user['password'])) {
        log_failed_attempt($ip_address); // Логируем неудачную попытку
        return respond_with_error('Неверный пароль');
    }

    // Генерация JWT токена
    $jwt = generate_jwt_for_user($user);

    // Устанавливаем куки с токеном и ID пользователя
    set_cookie('auth_token', $jwt); // Устанавливаем токен в куки
    set_cookie('user_id', (string)$user['_id']); // Устанавливаем ID пользователя в куки

    // Возвращаем успешный ответ
    echo json_encode([
        'success' => true,
        'data' => [
            'token' => $jwt,
            'user_id' => (string)$user['_id'],
        ],
    ]);
    exit;
}

// Вспомогательная функция для отправки ответа с ошибкой
function respond_with_error($message) {
    echo json_encode([
        'success' => false,
        'data' => [
            'message' => $message,
        ],
    ]);
    exit;
}

function set_cookie($name, $value, $days = 30) {
    $expire = time() + ($days * 24 * 60 * 60); // 30 дней
    setcookie($name, $value, $expire, "/", "", false, true); // Устанавливаем HttpOnly куки
}

// Функция поиска пользователя по номеру телефона
function find_user_by_phone($name) {
    $db = get_mongo_connection(); // Получаем соединение с базой данных
    $collection = $db->users; // Используем коллекцию 'users', замените на нужную вам
    return $collection->findOne(['name' => $name]);
}

// Функция генерации JWT токена
function generate_jwt_for_user($user) {
    $key = "xyezOaQakHqIBYCTtyiOrkkpfHYJKU"; // Секретный ключ для токена
    $payload = [
        'iss' => "https://promo.rubikom.kz", // Издатель токена
        'iat' => time(), // Время создания токена
        'exp' => time() + (30 * 24 * 60 * 60), // Срок действия - 30 дней
        'user_id' => (string) $user['_id'], // ID пользователя
    ];

    // Алгоритм шифрования — указываем 'HS256'
    return Firebase\JWT\JWT::encode($payload, $key, 'HS256');
}

// Проверка, является ли запросом на авторизацию
if (isset($_POST['action']) && $_POST['action'] === 'login_user') {
    handle_login_request();
}

function handle_logout_request() {
    header('Content-Type: application/json');

    // Проверка типа запроса
    if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['action']) || $_POST['action'] !== 'logout_user') {
        echo json_encode([
            'success' => false,
            'data' => ['message' => 'Неправильный метод запроса']
        ]);
        exit;
    }

    // Устанавливаем куки с истекшим временем
    setcookie('auth_token', '', time() - 3600, "/", "", false, true); // Удаляем токен
    setcookie('user_id', '', time() - 3600, "/", "", false, true);   // Удаляем ID пользователя

    // Возвращаем успешный ответ
    echo json_encode([
        'success' => true,
        'data' => ['message' => 'Вы успешно вышли из системы']
    ]);
    exit;
}

// Проверка, является ли запросом logout
if (isset($_POST['action']) && $_POST['action'] === 'logout_user') {
    handle_logout_request();
}

// Логирование неудачных попыток и блокировка IP-адресов
function log_failed_attempt($ip_address) {
    // Логика записи неудачных попыток в базу данных или файл
}

function get_failed_attempts($ip_address) {
    // Логика получения количества неудачных попыток по IP-адресу
}

function reset_failed_attempts($ip_address) {
    // Логика сброса счетчика неудачных попыток
}

add_action('wp_ajax_logout_user', 'handle_logout_request');
add_action('wp_ajax_nopriv_logout_user', 'handle_logout_request');

add_action('wp_ajax_login_user', 'handle_login_request');
add_action('wp_ajax_nopriv_login_user', 'handle_login_request');

