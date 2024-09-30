<?php
// Получаем ID пользователя из URL
$user_id = isset($_GET['user_id']) ? intval($_GET['user_id']) : 0;

// Получаем код для пользователя
$password = get_transient('show_password_' . $user_id);

// Если код существует, показываем его
if ($password) {
    echo '<div class="password-display">';
    echo '<h2>Ваш пароль: ' . $password . '</h2>';
    echo '<p>Код действителен в течение 15 секунд.</p>';
    echo '</div>';

    // Удаляем код через 15 секунд
    wp_clear_scheduled_hook('delete_password_' . $user_id);
} else {
    echo '<p>Пароль уже недействителен или срок действия истек.</p>';
}
