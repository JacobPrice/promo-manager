<?php
namespace LpdPromo\Models;

use Carbon_Fields\Container;
use Carbon_Fields\Field;


class Promo
{
    const POST_TYPE = 'lpd-promo';
    public function __construct()
    {
    }

    public static function init()
    {
        add_action('init', [__CLASS__, 'register_post_type']);
        add_action('init', [__CLASS__, 'register_taxonomies']);
        add_filter('manage_lpd-promo_posts_columns', [__CLASS__, 'add_new_columns']);
        add_action('carbon_fields_register_fields', [__CLASS__, 'register_custom_fields']);

    }
// NEED TO ADD HELP TEXT FOR THE CUSTOM FIELDS
// ->set_help_text('This is the promo start date') as an example
    public static function register_custom_fields()
    {
        Container::make('post_meta', 'Promo Settings')
            ->where('post_type', '=', 'lpd-promo')
            ->add_tab('Settings',[
                Field::make('date_time', 'promo_start_date', 'Promo Start Date')
                ->set_attribute('placeholder', 'Please select the date and time')
                ->set_width(50),
                Field::make('date_time', 'promo_end_date', 'Promo Start Date')
                    ->set_attribute('placeholder', 'Please select the date and time')
                    ->set_width(50),
                Field::make('association', 'linked_category', 'Promo Category')
                ->set_types(
                    [
                        [
                            'type' => 'term',
                            'taxonomy' => 'promo_category'
                        ]
                    ])
                    ->set_width(90)
            ])
            ->add_tab( 'Images', [
                Field::make('image', 'mobile_promo_image', 'Mobile Promo Image')
                ->set_width(10),
                Field::make('radio', 'desktop_promo_image_type', 'Desktop Promo Image Type')
                    ->add_options([
                        'full' => 'Full Width Image',
                        'half' => 'Half Width Image',
                    ])
                    ->set_width(10),
                Field::make('image', 'full_desktop_promo_image', 'Desktop Promo Image -- Full Width')
                ->set_conditional_logic([
                    [
                        'field' => 'desktop_promo_image_type',
                        'value' => 'full',
                    ],
                ])
                ->set_width(10),
                Field::make('image', 'half_desktop_promo_image', 'Desktop Promo Image -- Half Width')
                ->set_conditional_logic([
                    [
                        'field' => 'desktop_promo_image_type',
                        'value' => 'half',
                    ],
                ])
                ->set_width(10),

            ])
            ->add_tab('Content', [
                Field::make('text', 'promo_title', 'Promo Title'),
                Field::make('rich_text', 'promo_content', 'Promo Content')
                ->set_width(100),
                Field::make('text', 'promo_link_text', 'Promo Link Text'),
            ]);
    }

    public static function register_post_type()
    {
        register_post_type(
            self::POST_TYPE,
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

    public static function register_taxonomies()
    {
        register_taxonomy('promo_category', self::POST_TYPE, [
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
            'hierarchical' => true
        ]);

    }

    // create new columns for this post type, these should include start date, end date, image, promo category
    public static function add_new_columns($columns)
    {
        $new_columns['cb'] = '<input type="checkbox" />';
        $new_columns['title'] = __('Title');
        $new_columns['status'] = __('Status');
        $new_columns['start_date'] = __('Start Date');
        $new_columns['end_date'] = __('End Date');
        $new_columns['promo_category'] = __('Promo Category');
        $new_columns['image'] = __('Image');
        $new_columns['date'] = __('Date');
        return $new_columns;
    }
}