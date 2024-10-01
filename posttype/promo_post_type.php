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
        'promoactions',
        'normal',
        'default'
    );
}

add_action('add_meta_boxes', 'promo_conditions_meta_box');

// Рендерим метабокс
function render_promo_conditions_meta_box($post) {
    // Получаем существующие условия (если они есть)
    $conditions = get_post_meta($post->ID, 'promo_conditions', true);
    $selected_type = get_post_meta($post->ID, 'promo_condition_type', true);

    wp_nonce_field('save_promo_conditions', 'promo_conditions_nonce');
    ?>
    <div id="promo-conditions-container">
        <?php
        if (!empty($conditions)) {
            foreach ($conditions as $condition) {
                ?>
                <div class="promo-condition">
                    <input type="text" name="promo_conditions[]" value="<?php echo esc_attr($condition); ?>" class="widefat" />
                    <button type="button" class="remove-condition button">-</button>
                </div>
                <?php
            }
        }
        ?>
    </div>

    <button type="button" id="add-condition" class="button">Добавить условие +</button>

    <h3>Тип условия</h3>
    <label><input type="radio" name="promo_condition_type" value="text" <?php checked($selected_type, 'text'); ?> /> Текст</label>
    <label><input type="radio" name="promo_condition_type" value="photo" <?php checked($selected_type, 'photo'); ?> /> Фото</label>

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
    if (!isset($_POST['promo_conditions_nonce']) || !wp_verify_nonce($_POST['promo_conditions_nonce'], 'save_promo_conditions')) {
        return;
    }

    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
        return;
    }

    if (!current_user_can('edit_post', $post_id)) {
        return;
    }

    if (isset($_POST['promo_conditions'])) {
        $conditions = array_map('sanitize_text_field', $_POST['promo_conditions']);
        update_post_meta($post_id, 'promo_conditions', $conditions);
    }

    if (isset($_POST['promo_condition_type'])) {
        update_post_meta($post_id, 'promo_condition_type', sanitize_text_field($_POST['promo_condition_type']));
    }
}

add_action('save_post', 'save_promo_conditions');
