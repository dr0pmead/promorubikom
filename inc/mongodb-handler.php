<?php

// Подключение к MongoDB через Composer
function get_mongo_connection() {
    try {
        // Подключаемся к MongoDB через клиент
        $client = new MongoDB\Client("mongodb://localhost:27017/promoRubikom?retryWrites=true&w=majority");
        return $client->promoRubikom; // Замените 'your_database_name' на вашу базу данных
    } catch (MongoDB\Exception\Exception $e) {
        error_log('Ошибка подключения к MongoDB: ' . $e->getMessage());
        wp_die('Ошибка подключения к базе данных MongoDB');
    }
}

function save_user_to_mongo($phone, $fio, $region, $age, $gender, $password, $admin) {
    try {
        $db = get_mongo_connection();
        // Выбираем коллекцию 'users'
        $collection = $db->selectCollection('users');

        // Создаем документ с данными пользователя
        $user = [
            'phone'   => $phone,
            'fio'     => $fio,
            'region'  => $region,
            'age'     => $age,
            'gender'  => $gender,
            'password' => password_hash($password, PASSWORD_DEFAULT), // Хэшируем пароль
            'admin' => $admin,
            'tickets' => [] // Пустой массив для тикетов
        ];

        // Вставляем документ в коллекцию
        $result = $collection->insertOne($user);

        if ($result->getInsertedCount() === 1) {
            return true; // Успешная вставка
        } else {
            return false; // Неудачная вставка
        }

    } catch (MongoDB\Exception\Exception $e) {
        error_log('Ошибка сохранения данных в MongoDB: ' . $e->getMessage());
        wp_die('Ошибка сохранения данных в базу данных MongoDB');
    }
}
