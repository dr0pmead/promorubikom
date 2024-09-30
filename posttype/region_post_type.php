<?php

// Регистрация кастомного пост-типа "Регионы"
function register_region_post_type() {
    $labels = array(
        'name'               => 'Регионы',
        'singular_name'      => 'Регион',
        'menu_name'          => 'Регионы',
        'name_admin_bar'     => 'Регион',
        'add_new'            => 'Добавить новый',
        'add_new_item'       => 'Добавить новый регион',
        'new_item'           => 'Новый регион',
        'edit_item'          => 'Редактировать регион',
        'view_item'          => 'Просмотр региона',
        'all_items'          => 'Все регионы',
        'search_items'       => 'Искать регионы',
        'not_found'          => 'Регионы не найдены',
    );
  
    $args = array(
        'labels'             => $labels,
        'public'             => true,
        'publicly_queryable' => true,
        'show_ui'            => true,
        'show_in_menu'       => true,
        'query_var'          => true,
        'rewrite'            => array( 'slug' => 'regions' ),
        'capability_type'    => 'post',
        'has_archive'        => true,
        'hierarchical'       => false,
        'menu_position'      => 20,
        'supports'           => array( 'title' ),
        'menu_icon'          => 'dashicons-location', // Иконка в админке
    );
  
    register_post_type( 'region', $args );
}
add_action( 'init', 'register_region_post_type' );
