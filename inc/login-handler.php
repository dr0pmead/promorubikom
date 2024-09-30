<?php
use \Firebase\JWT\JWT;
require_once 'mongodb-handler.php'; // подключение к MongoDB

// Главная функция для обработки авторизации
function handle_login_request() {
    header('Content-Type: application/json');

    // Проверка типа запроса и AJAX-действия
    if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['action']) || $_POST['action'] !== 'login_user') {
        return respond_with_error('Неправильный метод запроса');
    }

    if (!isset($_POST['h-captcha-response']) || !verify_hcaptcha($_POST['h-captcha-response'])) {
        wp_send_json_error(array('message' => 'Ошибка валидации hCaptcha.'));
        return;
    }

    // Получаем данные из POST
    $phone = isset($_POST['phone']) ? trim($_POST['phone']) : null;
    $password = isset($_POST['password']) ? trim($_POST['password']) : null;

    // Валидация ввода
    if (!$phone || !$password) {
        return respond_with_error('Необходимо ввести телефон и пароль');
    }

    // Проверяем пользователя
    $user = find_user_by_phone($phone);
    if (!$user) {
        return respond_with_error('Пользователь с таким телефоном не найден');
    }

    // Проверяем пароль
    if (!password_verify($password, $user['password'])) {
        return respond_with_error('Неверный пароль');
    }

    // Генерация JWT токена
    $jwt = generate_jwt_for_user($user);

    // Устанавливаем куки с токеном и ID пользователя
    set_cookie('auth_token', $jwt); // Устанавливаем токен в куки
    set_cookie('user_id', (string) $user['_id']); // Устанавливаем ID пользователя в куки

    // Возвращаем успешный ответ
    echo json_encode([
        'success' => true,
        'data' => [
            'token' => $jwt,
            'user_id' => (string) $user['_id'],
        ],
    ]);
    exit;
}

function set_cookie($name, $value, $days = 30) {
    $expire = time() + ($days * 24 * 60 * 60); // 30 дней
    setcookie($name, $value, $expire, "/", "", false, true); // Устанавливаем HttpOnly куки
}

// Функция поиска пользователя по номеру телефона
function find_user_by_phone($phone) {
    $db = get_mongo_connection(); // Получаем соединение с базой данных
    $collection = $db->users; // Используем коллекцию 'users', замените на нужную вам
    return $collection->findOne(['phone' => $phone]);
}

// Функция генерации JWT токена
function generate_jwt_for_user($user) {
    $key = "xyezOaQakHqIBYCTtyiOrkkpfHYJKU"; // Секретный ключ для токена
    $payload = [
        'iss' => "http://promo.rubikom.kz", // Издатель токена
        'iat' => time(), // Время создания токена
        'exp' => time() + (30 * 24 * 60 * 60), // Срок действия - 30 дней
        'user_id' => (string) $user['_id'], // ID пользователя
    ];

    // Алгоритм шифрования — указываем 'HS256'
    return Firebase\JWT\JWT::encode($payload, $key, 'HS256');
}

// Функция для отправки ошибки в формате JSON
function respond_with_error($message) {
    echo json_encode([
        'success' => false,
        'data' => ['message' => $message]
    ]);
    exit;
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

add_action('wp_ajax_logout_user', 'handle_logout_request');
add_action('wp_ajax_nopriv_logout_user', 'handle_logout_request');

add_action('wp_ajax_login_user', 'handle_login_request');
add_action('wp_ajax_nopriv_login_user', 'handle_login_request');

function verify_hcaptcha($hcaptcha_response) {
    $secret_key = 'ES_2d3cbf46ed124408a9002a88605ab990';
    $response = wp_remote_post('https://hcaptcha.com/siteverify', array(
        'body' => array(
            'secret' => $secret_key,
            'response' => $hcaptcha_response,
        ),
    ));

    $response_body = wp_remote_retrieve_body($response);
    $result = json_decode($response_body);

    return $result && $result->success;
}