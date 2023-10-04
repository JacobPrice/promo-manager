<?php

namespace LpdPromo\Controllers;

class PostTypeController {
    public function register_promos() {
        register_post_type(
            'lpd-promo',
            [
                'labels' => [
                    'name' => __('Promos'),
                    'singular_name' => __('Promo'),
                    'menu_name' => __('Promos'),
                    'name_admin_bar' => __('Promo'),
                    'add_new' => __('Add New'),
                    'add_new_item' => __('Add New Promo'),
                    'new_item' => __('New Promo'),
                    'edit_item' => __('Edit Promo'),
                    'view_item' => __('View Promo'),
                    'all_items' => __('All Promos'),
                    'search_items' => __('Search Promos'),
                    'parent_item_colon' => __('Parent Promos:'),
                    'not_found' => __('No promos found.'),
                    'not_found_in_trash' => __('No promos found in Trash.')
                ],
                'public' => true,
                'has_archive' => true,
                'show_ui' => true,
                'rewrite' => [
                    'slug' => 'promos'
                ],
                'supports' => [
                    'title',
                    'editor',
                    'thumbnail',
                    'excerpt',
                    'custom-fields'
                ],
                'menu_icon' => 'dashicons-awards',
                'show_in_rest' => true,
                'rest_base' => 'lpd-promo'
            ]
        );
    }
}