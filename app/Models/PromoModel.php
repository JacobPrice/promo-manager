<?php

namespace LpdPromo\Models;


class PromoModel
{
    public string $post_type;
    public array $post_type_args;
    
    public function __construct() {
        $this->set_post_type();
        $this->set_post_type_args();
    }
    public static function is_active($post_id)
    {
        $start_date = carbon_get_post_meta($post_id, 'promo_start_date');
        $end_date = carbon_get_post_meta($post_id, 'promo_end_date');
        $now = new \DateTime();
        $now = $now->format('Y-m-d');

        return ($now >= $start_date && $now <= $end_date);
    }
    public function get_active() {
        
            $args = array(
                'post_type' => $this->post_type,
                'posts_per_page' => -1,
                'post_status' => 'publish',
            );
            $query = new \WP_Query($args);
            $active_promos = [];
            if ($query->have_posts()) {
                while ($query->have_posts()) {
                    $query->the_post();
                    $post_id = get_the_ID();
                    if (self::is_active($post_id)) {
                        $active_promos[] = get_post($post_id);
    
                    }
                }
                wp_reset_postdata();
            }
            return $active_promos;
        
    }
    private function set_post_type_args() {
        $rewrite_slug = carbon_get_theme_option('promo_slug');
        $this->post_type_args = [
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
                'slug' => $rewrite_slug ? $rewrite_slug : 'promos',
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
        ];
    }
    private function set_post_type() {
        $this->post_type = 'lpd-promo';
    }
}