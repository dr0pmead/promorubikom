<?php

// Регистрация пользовательского пост-тайпа для акций
function register_promo_post_type() {
    $labels = array(
        'name'               => 'Акции',
        'singular_name'      => 'Акция',
        'menu_name'          => 'Акции',
        'name_admin_bar'     => 'Акция',
        'add_new'            => 'Добавить новую',
        'add_new_item'       => 'Добавить новую акцию',
        'new_item'           => 'Новая акция',
        'edit_item'          => 'Редактировать акцию',
        'view_item'          => 'Просмотреть акцию',
        'all_items'          => 'Все акции',
        'search_items'       => 'Поиск акций',
        'not_found'          => 'Акций не найдено.',
        'not_found_in_trash' => 'Акций в корзине не найдено.'
    );

    $args = array(
        'labels'             => $labels,
        'public'             => true,
        'has_archive'        => true,
        'rewrite'            => array('slug' => 'promoactions'),
        'supports'           => array('title', 'editor', 'thumbnail'),
        'show_in_rest'       => true,
    );

    register_post_type('promoactions', $args);
}

add_action('init', 'register_promo_post_type');

// Добавляем метабокс для условий акций
function promo_conditions_meta_box() {
    add_meta_box(
        'promo_conditions',
        'Условия акции',
        'render_promo_conditions_meta_box',
        'promoactions', // Ваш тип поста 'promoactions'
        'normal',
        'default'
    );
}
add_action('add_meta_boxes', 'promo_conditions_meta_box');

// Добавляем метабокс для типа условия акции в сайдбар
function promo_condition_type_meta_box() {
    add_meta_box(
        'promo_condition_type',
        'Тип условия акции',
        'render_promo_condition_type_meta_box',
        'promoactions',
        'side',  // Выводим в сайдбаре
        'default'
    );
}
add_action('add_meta_boxes', 'promo_condition_type_meta_box');

// Рендерим метабокс для типа условия акции
function render_promo_condition_type_meta_box($post) {
    $selected_type = get_post_meta($post->ID, 'promo_condition_type', true);
    
    // Защита nonce
    wp_nonce_field('save_promo_condition_type', 'promo_condition_type_nonce');
    
    ?>
    <label><input type="radio" name="promo_condition_type" value="text" <?php checked($selected_type, 'text'); ?> /> Текст</label>
    <br>
    <label><input type="radio" name="promo_condition_type" value="photo" <?php checked($selected_type, 'photo'); ?> /> Фото</label>
    <?php
}

// Сохраняем тип условия при сохранении поста
function save_promo_condition_type($post_id) {
    // Проверяем nonce
    if (!isset($_POST['promo_condition_type_nonce']) || !wp_verify_nonce($_POST['promo_condition_type_nonce'], 'save_promo_condition_type')) {
        return;
    }

    // Проверяем автосохранение
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
        return;
    }

    // Проверяем права доступа
    if (!current_user_can('edit_post', $post_id)) {
        return;
    }

    // Сохраняем тип условия
    if (isset($_POST['promo_condition_type'])) {
        update_post_meta($post_id, 'promo_condition_type', sanitize_text_field($_POST['promo_condition_type']));
    }
}
add_action('save_post', 'save_promo_condition_type');

// Рендерим метабокс для условий акции
function render_promo_conditions_meta_box($post) {
    // Получаем существующие условия (если они есть)
    $conditions = get_post_meta($post->ID, 'promo_conditions', true) ?: []; // Если пусто, задаем массив
    wp_nonce_field('save_promo_conditions', 'promo_conditions_nonce');
    ?>
    <div id="promo-conditions-container">
        <?php
        // Если условий ещё нет, добавляем хотя бы одно пустое поле
        if (empty($conditions)) {
            ?>
            <div class="promo-condition">
                <input type="text" name="promo_conditions[]" value="" class="widefat" />
                <button type="button" class="remove-condition button">-</button>
            </div>
            <?php
        } else {
            // Выводим существующие условия
            foreach ($conditions as $condition) {
                ?>
                <div class="promo-condition flex flex-col">
                    <input type="text" name="promo_conditions[]" value="<?php echo esc_attr($condition); ?>" class="widefat" />
                    <button type="button" class="remove-condition button">-</button>
                </div>
                <?php
            }
        }
        ?>
    </div>

    <button type="button" id="add-condition" class="button">Добавить условие</button>

    <script type="text/javascript">
        (function($) {
            $('#add-condition').on('click', function() {
                var conditionHTML = '<div class="promo-condition"><input type="text" name="promo_conditions[]" value="" class="widefat" /><button type="button" class="remove-condition button">-</button></div>';
                $('#promo-conditions-container').append(conditionHTML);
            });

            $(document).on('click', '.remove-condition', function() {
                $(this).parent('.promo-condition').remove();
            });
        })(jQuery);
    </script>
    <?php
}

// Сохраняем условия при сохранении поста
function save_promo_conditions($post_id) {
    // Проверяем nonce
    if (!isset($_POST['promo_conditions_nonce']) || !wp_verify_nonce($_POST['promo_conditions_nonce'], 'save_promo_conditions')) {
        return;
    }

    // Проверяем автосохранение
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
        return;
    }

    // Проверяем права доступа
    if (!current_user_can('edit_post', $post_id)) {
        return;
    }

    // Логируем сохранение условий
    if (isset($_POST['promo_conditions'])) {
        $conditions = array_map('sanitize_text_field', $_POST['promo_conditions']); // Очистка условий
        update_post_meta($post_id, 'promo_conditions', $conditions);
    }
}
add_action('save_post', 'save_promo_conditions');

// Добавляем метабокс для отображения на главной странице
function display_on_homepage_meta_box() {
    add_meta_box(
        'display_on_homepage', // Идентификатор метабокса
        'Отображать на главной', // Заголовок метабокса
        'render_display_on_homepage_meta_box', // Функция рендера
        'promoactions', // Ваш тип поста 'promoactions'
        'side', // В сайдбаре
        'default' // Приоритет
    );
}
add_action('add_meta_boxes', 'display_on_homepage_meta_box');

// Рендеринг метабокса
function render_display_on_homepage_meta_box($post) {
    // Получаем текущее значение мета-поля, если оно не установлено, по умолчанию 'no'
    $is_checked = get_post_meta($post->ID, '_display_on_homepage', true) === 'yes' ? 'yes' : 'no';

    // Защита nonce
    wp_nonce_field('save_display_on_homepage', 'display_on_homepage_nonce');
    ?>
    <label for="display_on_homepage">
        <input type="checkbox" name="display_on_homepage" id="display_on_homepage" value="yes" <?php checked($is_checked, 'yes'); ?> />
        Отображать на главной странице
    </label>
    <?php
}

// Сохранение значения чекбокса
function save_display_on_homepage($post_id) {
    // Проверяем nonce
    if (!isset($_POST['display_on_homepage_nonce']) || !wp_verify_nonce($_POST['display_on_homepage_nonce'], 'save_display_on_homepage')) {
        error_log("Неверный nonce для поста $post_id");
        return;
    }

    // Проверяем автосохранение
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
        error_log("Автосохранение для поста $post_id");
        return;
    }

    // Проверяем права доступа
    if (!current_user_can('edit_post', $post_id)) {
        error_log("Нет прав доступа для редактирования поста $post_id");
        return;
    }

    // Сохраняем значение чекбокса и логируем
    if (isset($_POST['display_on_homepage']) && $_POST['display_on_homepage'] === 'yes') {
        update_post_meta($post_id, '_display_on_homepage', 'yes');
        error_log("Значение чекбокса для поста $post_id сохранено как 'yes'");
    } else {
        update_post_meta($post_id, '_display_on_homepage', 'no');
        error_log("Значение чекбокса для поста $post_id сохранено как 'no'");
    }
}

add_action('save_post', 'save_display_on_homepage');