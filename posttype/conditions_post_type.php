<?php
// Регистрация Custom Post Type для Условий
function create_conditions_post_type() {
    $labels = array(
        'name'               => 'Условия',
        'singular_name'      => 'Условие',
        'menu_name'          => 'Условия',
        'name_admin_bar'     => 'Условие',
        'add_new'            => 'Добавить новое',
        'add_new_item'       => 'Добавить новое условие',
        'new_item'           => 'Новое условие',
        'edit_item'          => 'Редактировать условие',
        'view_item'          => 'Просмотреть условие',
        'all_items'          => 'Все условия',
        'search_items'       => 'Поиск условий',
        'not_found'          => 'Условий не найдено',
    );
    $args = array(
        'labels'             => $labels,
        'public'             => true,
        'show_in_menu'       => true,
        'supports'           => array( 'title', 'thumbnail' ), // Убедитесь, что 'thumbnail' включено
        'menu_position'      => 5,
        'show_in_rest'       => true, // Включить для поддержки Gutenberg
        'menu_icon'          => 'dashicons-list-view', // Иконка меню
    );
    register_post_type( 'conditions', $args );
}
add_action( 'init', 'create_conditions_post_type' );


// Добавляем метабоксы для Custom Post Type "Условия"
function add_conditions_meta_boxes() {

    add_meta_box(
        'condition_description',
        'Описание условия',
        'condition_description_callback',
        'conditions',
        'normal',
        'high'
    );
}
add_action( 'add_meta_boxes', 'add_conditions_meta_boxes' );


// Выводим поле для кастомного описания
function condition_description_callback( $post ) {
    wp_nonce_field( basename( __FILE__ ), 'condition_nonce' );
    $condition_description = get_post_meta( $post->ID, 'condition_description', true );
    ?>
    <div>
        <textarea id="condition_description" name="condition_description" rows="4" style="width: 100%;"><?php echo esc_textarea( $condition_description ); ?></textarea>
    </div>
    <?php
}
