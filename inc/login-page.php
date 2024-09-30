<?php

// Добавляем свои стили для страницы входа
function custom_login_styles() {
    ?>
    <style type="text/css">
        body.login {
            background-color: #131313; /* Изменение фона */
        }

        .login h1 a {
            background-image: url('<?php echo get_template_directory_uri(); ?>/assets/images/custom-logo.svg'); /* Логотип на странице входа */
            background-size: contain;
            width: 100%;
            height: 80px;
        }

        .login form {
            background: #232323; /* Изменение фона формы */
            border-radius: 8px;
        }

        .login label {
            color: #fff; /* Цвет текста */
        }

        .login .button-primary {
            background-color: #E53F0B; /* Кнопка входа */
            border-color: #E53F0B;
            text-shadow: none;
            box-shadow: none;
        }

        .login .button-primary:hover {
            background-color: #F35726;
        }

        .login #nav a, .login #backtoblog a {
            color: #fff !important; /* Ссылки на восстановление пароля и назад на сайт */
        }

        /* Убираем блок выбора языка */
        #login_language {
            display: none !important;
        }

        /* Ссылка "Перейти на сайт" наверх */
        .custom-site-link {
            position: absolute;
            top: 20px;
            right: 20px;
            color: #fff !important;
            text-decoration: underline;
        }
    </style>
    <?php
}
add_action('login_enqueue_scripts', 'custom_login_styles');

// Изменение ссылки на логотип
function custom_login_logo_url() {
    return home_url(); // Ссылка на главную страницу сайта
}
add_filter('login_headerurl', 'custom_login_logo_url');

// Изменение title логотипа
function custom_login_logo_url_title() {
    return 'Вернуться на главную страницу';
}
add_filter('login_headertext', 'custom_login_logo_url_title');

// Изменяем текст на кнопке "Войти"
function custom_login_button_text( $translated_text, $text, $domain ) {
    if ( 'Log In' === $text ) {
        $translated_text = __( 'Войти на сайт', 'your-text-domain' );
    }
    return $translated_text;
}
add_filter( 'gettext', 'custom_login_button_text', 20, 3 );

// Добавляем свое сообщение под формой входа
function custom_login_message() {
    return '<p class="custom-message" style="color:#fff;">Добро пожаловать! Пожалуйста, авторизуйтесь.</p>';
}
add_filter('login_message', 'custom_login_message');

// Добавляем поле hCaptcha на страницу входа
function custom_hcaptcha_field() {
    ?>
    <div class="h-captcha" data-sitekey="7fae0340-2930-422c-aefe-e4ce125e2c0a"></div>
    <script src="https://js.hcaptcha.com/1/api.js" async defer></script>
    <?php
}
add_action('login_form', 'custom_hcaptcha_field');

// Проверка hCaptcha при авторизации
function verify_hcaptcha_on_login($user, $password) {
    if (isset($_POST['h-captcha-response'])) {
        $response = wp_remote_post('https://hcaptcha.com/siteverify', array(
            'body' => array(
                'secret' => 'ваш-секретный-ключ-hcaptcha',
                'response' => $_POST['h-captcha-response'],
            )
        ));
        $response_body = wp_remote_retrieve_body($response);
        $result = json_decode($response_body);

        if (!$result->success) {
            return new WP_Error('captcha_invalid', '<strong>Ошибка:</strong> Пожалуйста, подтвердите, что вы не робот.');
        }
    } else {
        return new WP_Error('captcha_missing', '<strong>Ошибка:</strong> Пожалуйста, пройдите проверку hCaptcha.');
    }
    return $user;
}
add_filter('authenticate', 'verify_hcaptcha_on_login', 30, 2);
