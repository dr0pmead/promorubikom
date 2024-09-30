<?php

require_once __DIR__ . '/vendor/autoload.php';
require_once get_template_directory() . '/inc/mongodb-lib/src/Client.php';
require_once get_template_directory() . '/inc/mongodb-handler.php';
require_once get_template_directory() . '/inc/registration-handler.php';
require_once get_template_directory() . '/inc/login-handler.php';
require_once get_template_directory() . '/inc/login-button.php';
require_once get_template_directory() . '/inc/user_data.php';
require_once get_template_directory() . '/inc/load_tickets.php';
require_once get_template_directory() . '/inc/validate_token.php';
require_once get_template_directory() . '/inc/charts.php';
require_once get_template_directory() . '/inc/export_to_excel.php';
require_once get_template_directory() . '/posttype/region_post_type.php';
require_once get_template_directory() . '/posttype/conditions_post_type.php';


function mytheme_enqueue_styles() {
    wp_enqueue_script('tailwind', 'https://cdn.tailwindcss.com', array(), null, false);
    wp_enqueue_style( 'remodal-css', get_template_directory_uri() . '/assets/css/remodal.css' );
    wp_enqueue_style( 'remodal-default-theme', get_template_directory_uri() . '/assets/css/remodal-default-theme.css' );
    wp_enqueue_script( 'remodal-js', get_template_directory_uri() . '/assets/js/remodal.min.js', array('jquery'), null, true );
    wp_enqueue_script('jscookie', 'https://cdnjs.cloudflare.com/ajax/libs/jquery-cookie/1.4.1/jquery.cookie.min.js', array('jquery'), null, true);
    wp_enqueue_script('hcaptcha', 'https://js.hcaptcha.com/1/api.js', array('jquery'), null, true);
    wp_enqueue_script( 'mainmenu-js', get_template_directory_uri() . '/assets/js/mainmenu.js', array('jquery'), null, true );
    wp_enqueue_style('checkbox-style', get_template_directory_uri() . '/assets/css/checkbox.css');
    wp_enqueue_style('tailwind-style', get_template_directory_uri() . '/assets/css/style.css');
    wp_enqueue_script('login-handler', get_template_directory_uri() . '/assets/js/login.js', ['jquery'], null, true);
    wp_enqueue_script('preloader-js', get_template_directory_uri() . '/js/preloader.js', array(), null, true);
    wp_localize_script('login-handler', 'ajax_object', array(
        'ajax_url' => admin_url('admin-ajax.php')
    ));
}

add_action('wp_enqueue_scripts', 'mytheme_enqueue_styles');

function restrict_dashboard_access() {
    // Проверяем, если пользователь на странице dashboard и не авторизован
    if (is_page('dashboard') && !isset($_COOKIE['user_id'])) {
        wp_redirect(home_url()); // Перенаправляем на главную
        exit;
    }
}
add_action('template_redirect', 'restrict_dashboard_access');

function filter_dashboard_menu_item($items, $args) {
    // Проверяем, есть ли куки 'user_id' (авторизован ли пользователь)
    if (!isset($_COOKIE['user_id'])) {
        // Если куки нет, скрываем пункт меню с ссылкой на dashboard
        $items = preg_replace('/<li.*?dashboard.*?<\/li>/i', '', $items);
    }
    return $items;
}
add_filter('wp_nav_menu_items', 'filter_dashboard_menu_item', 10, 2);

function restrict_statistics_access() {
    // Проверяем, если пользователь на странице dashboard и не авторизован
    if (is_page('statistics') && !isset($_COOKIE['user_id'])) {
        wp_redirect(home_url()); // Перенаправляем на главную
        exit;
    }
}
add_action('template_redirect', 'restrict_statistics_access');

function filter_statistics_menu_item($items, $args) {
    // Проверяем, есть ли куки 'user_id' (авторизован ли пользователь)
    if (!isset($_COOKIE['user_id'])) {
        // Если куки нет, скрываем пункт меню с ссылкой на dashboard
        $items = preg_replace('/<li.*?statistics.*?<\/li>/i', '', $items);
    }
    return $items;
}
add_filter('wp_nav_menu_items', 'filter_statistics_menu_item', 10, 2);


function check_auth_before_request() {
    // Проверяем путь запроса, если это защищенные маршруты
    if (is_page(['dashboard', 'profile', 'protected-endpoint', 'statistics'])) {
        $auth = validate_auth_token();

        if (!$auth) {
            wp_redirect(home_url()); // Перенаправляем на главную, если токен недействителен
            exit;
        }
    }
}
add_action('template_redirect', 'check_auth_before_request');

function enqueue_apexcharts() {
    wp_enqueue_script( 'apexcharts', 'https://cdn.jsdelivr.net/npm/apexcharts', null, null, true );
}

add_action( 'wp_enqueue_scripts', 'enqueue_apexcharts' );


function enqueue_custom_ajax_script() {
    wp_localize_script('custom-js', 'ajax_object', array(
        'ajax_url' => admin_url('admin-ajax.php'),
    ));
}
add_action('wp_enqueue_scripts', 'enqueue_custom_ajax_script');

add_action('wp_ajax_protected_action', 'handle_protected_action');
add_action('wp_ajax_nopriv_protected_action', 'handle_protected_action');

function handle_protected_action() {
    // Проверяем токен перед выполнением запроса
    $auth = validate_auth_token();

    if (!$auth) {
        wp_send_json_error(['message' => 'Unauthorized'], 403);
        exit;
    }

    // Логика вашего запроса, если токен валидный
    wp_send_json_success(['message' => 'Success']);
}

function enqueue_inputmask() {
    // Подключаем Inputmask
    wp_enqueue_script('inputmask', 'https://cdnjs.cloudflare.com/ajax/libs/jquery.inputmask/5.0.7/jquery.inputmask.min.js', array('jquery'), null, true);
    
    // Подключаем собственный скрипт для активации масок
    wp_enqueue_script('inputmask-init', get_template_directory_uri() . '/assets/js/inputmask-init.js', array('jquery', 'inputmask'), null, true);
}
add_action('wp_enqueue_scripts', 'enqueue_inputmask');

function ajax_get_user_button_html() {
    echo get_user_button_html(); // Возвращаем HTML для кнопки
    wp_die(); // Завершаем AJAX запрос
}

// Регистрируем обработчик для AJAX запросов
add_action('wp_ajax_get_user_button_html', 'ajax_get_user_button_html');
add_action('wp_ajax_nopriv_get_user_button_html', 'ajax_get_user_button_html'); // Для неавторизованных пользователей


function theme_setup() {
    // Поддержка меню
    register_nav_menus( array(
        'primary' => 'Основное меню',
        'footer'  => 'Меню в подвале'
    ) );

    // Поддержка виджетов
    add_theme_support( 'widgets' );
    add_theme_support( 'post-thumbnails' );
}
add_action( 'after_setup_theme', 'theme_setup' );

function modify_menu_for_non_admin_users($items, $args) {
    // Проверка, если пользователь авторизован
    if (isset($_COOKIE['user_id'])) {
        // Получаем ID пользователя из куки
        $user_id = $_COOKIE['user_id'];
        $collection = get_mongo_connection()->users;
        $user = $collection->findOne(['_id' => new MongoDB\BSON\ObjectId($user_id)]);

        // Если пользователь найден и он не администратор
        if ($user && (!isset($user['admin']) || !$user['admin'])) {
            // Убираем пункт меню, который ведет на страницу /check-ticket
            foreach ($items as $key => $item) {
                if (strpos($item->url, '/admin-panel') !== false) {
                    unset($items[$key]);
                }
            }
            foreach ($items as $key => $item) {
                if (strpos($item->url, '/statistics') !== false) {
                    unset($items[$key]);
                }
            }
        }
    } else {
        // Если пользователь не авторизован, скрываем пункт /check-ticket
        foreach ($items as $key => $item) {
            if (strpos($item->url, '/admin-panel') !== false) {
                unset($items[$key]);
            }
        }
        foreach ($items as $key => $item) {
            if (strpos($item->url, '/statistics') !== false) {
                unset($items[$key]);
            }
        }
    }
    
    return $items;
}
add_filter('wp_nav_menu_objects', 'modify_menu_for_non_admin_users', 10, 2);

function is_mongo_user_admin() {
    if (isset($_COOKIE['user_id'])) {
        // Подключаем MongoDB и ищем пользователя по ID
        $db = get_mongo_connection();
        $user_id = $_COOKIE['user_id'];
        $collection = $db->users;

        try {
            $user = $collection->findOne(['_id' => new MongoDB\BSON\ObjectId($user_id)]);
            if ($user && isset($user['admin']) && $user['admin'] === true) {
                return true; // Пользователь является администратором
            }
        } catch (Exception $e) {
            error_log("Ошибка при получении данных пользователя: " . $e->getMessage());
        }
    }

    return false; // Пользователь не администратор
}

// Кастомный Walker_Nav_Menu
class Custom_Walker_Nav_Menu extends Walker_Nav_Menu {
    
    function start_el(&$output, $item, $depth = 0, $args = array(), $id = 0) {
        $classes = empty($item->classes) ? array() : (array) $item->classes;

        // Добавляем класс для активных пунктов меню
        if (in_array('current-menu-item', $classes)) {
            $active_class = 'opacity-[100%]';
        } else {
            $active_class = 'opacity-[60%]';
        }

        // Генерация атрибутов ссылки
        $attributes = '';
        $attributes .= !empty($item->url) ? ' href="' . esc_url($item->url) . '"' : '';
        $attributes .= !empty($item->attr_title) ? ' title="' . esc_attr($item->attr_title) . '"' : '';
        $attributes .= ' class="duration-150 text-white font-bold uppercase text-sm tracking-wider leading-[5px] ' . $active_class . ' hover:opacity-[100%]"';

        // Формирование HTML для пункта меню
        $item_output = '<li class="group">';
        $item_output .= '<a' . $attributes . '>';
        $item_output .= apply_filters('the_title', $item->title, $item->ID);
        $item_output .= '</a>';
        $item_output .= '</li>';

        // Объединение всего вместе
        $output .= $item_output;
    }
}

// Кастомный Walker_Nav_Menu
class Custom_Walker_Nav_Menu_Mobile extends Walker_Nav_Menu {
    
    function start_el(&$output, $item, $depth = 0, $args = array(), $id = 0) {
        $classes = empty($item->classes) ? array() : (array) $item->classes;

        // Добавляем класс для активных пунктов меню
        if (in_array('current-menu-item', $classes)) {
            $active_class = 'opacity-[100%]';
        } else {
            $active_class = 'opacity-[60%]';
        }

        // Генерация атрибутов ссылки
        $attributes = '';
        $attributes .= !empty($item->url) ? ' href="' . esc_url($item->url) . '"' : '';
        $attributes .= !empty($item->attr_title) ? ' title="' . esc_attr($item->attr_title) . '"' : '';
        $attributes .= ' class="duration-150 text-white font-bold uppercase text-2xl tracking-wider leading-[5px] ' . $active_class . ' hover:opacity-[100%]"';

        // Формирование HTML для пункта меню
        $item_output = '<li class="group">';
        $item_output .= '<a' . $attributes . '>';
        $item_output .= apply_filters('the_title', $item->title, $item->ID);
        $item_output .= '</a>';
        $item_output .= '</li>';

        // Объединение всего вместе
        $output .= $item_output;
    }
}

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

function allow_svg_upload($mimes) {
    $mimes['svg'] = 'image/svg+xml';
    return $mimes;
}
add_filter('upload_mimes', 'allow_svg_upload');

// Безопасная обработка SVG
function sanitize_svg($file) {
    if ($file['type'] == 'image/svg+xml') {
        $file['ext']  = 'svg';
        $file['type'] = 'image/svg+xml';
    }
    return $file;
}
add_filter('wp_check_filetype_and_ext', 'sanitize_svg', 10, 4);

function fix_svg_mime_type($data, $file, $filename, $mimes) {
    $ext = isset($data['ext']) ? $data['ext'] : '';
    if ('svg' === $ext) {
        $data['type'] = 'image/svg+xml';
        $data['ext'] = 'svg';
    }
    return $data;
}
add_filter('wp_check_filetype_and_ext', 'fix_svg_mime_type', 10, 4);