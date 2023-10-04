<?php

namespace LpdPromo\Controllers;

class TaxonomyController {
    public function register_promo_categories() {
        register_taxonomy('promo_category', 'lpd-promo', [
            'labels' => [
                'name' => __('Promo Categories'),
                'singular_name' => __('Promo Category'),
                'menu_name' => __('Promo Categories'),
                'all_items' => __('All Promo Categories'),
                'edit_item' => __('Edit Promo Category'),
                'view_item' => __('View Promo Category'),
                'update_item' => __('Update Promo Category'),
                'add_new_item' => __('Add New Promo Category'),
                'new_item_name' => __('New Promo Category Name'),
                'parent_item' => __('Parent Promo Category'),
                'parent_item_colon' => __('Parent Promo Category:'),
                'search_items' => __('Search Promo Categories'),
                'popular_items' => __('Popular Promo Categories'),
                'separate_items_with_commas' => __('Separate promo categories with commas'),
                'add_or_remove_items' => __('Add or remove promo categories'),
                'choose_from_most_used' => __('Choose from the most used promo categories'),
                'not_found' => __('No promo categories found.')
            ],
            'public' => true,
            'show_ui' => true,
            'show_in_menu' => 'lpd-promo',
            'show_in_rest' => true,

        ]);

    }
}