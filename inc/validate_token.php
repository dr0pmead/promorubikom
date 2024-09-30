<?php

use \Firebase\JWT\JWT;
use \Firebase\JWT\Key;

function validate_auth_token() {
    // Проверяем наличие токена в куки
    if (!isset($_COOKIE['auth_token'])) {
        return false;
    }

    $token = $_COOKIE['auth_token'];
    $secret_key = "xyezOaQakHqIBYCTtyiOrkkpfHYJKU"; // Ваш секретный ключ

    try {
        // Декодируем токен и проверяем его подлинность
        $decoded = JWT::decode($token, new Key($secret_key, 'HS256'));

        // Проверка срока действия токена (exp)
        if ($decoded->exp < time()) {
            return false;
        }

        // Проверка наличия user_id
        if (!isset($decoded->user_id)) {
            return false;
        }

        // Если токен валидный, возвращаем данные пользователя
        return $decoded;

    } catch (Exception $e) {
        error_log('Ошибка валидации JWT токена: ' . $e->getMessage());
        return false;
    }
}
