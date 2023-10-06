<?php

namespace LpdPromo\Controllers;

use Timber\Timber;
use Carbon_Fields\Block;
use Carbon_Fields\Field;
use Detection\MobileDetect;
use LpdPromo\Models\PromoModel;

class PromoBlockController
{
    public function __construct(protected PromoModel $model)
    {
    }
    public static function category_options_for_promo_categories()
    {
        // set the options for promo_category
        $category_options = [];
        $categories = get_terms(
            array(
                'taxonomy' => 'promo_category',
                // replace with your taxonomy name
                'hide_empty' => false,
                // set to true if you only want terms with posts
            )
        );

        foreach ($categories as $term) {
            $category_options[$term->term_id] = str_replace('&amp;', '&', $term->name); // Note the change from $term->id to $term->term_id
        }

        return $category_options;
    }
    public function make_promo_block()
    {
        $category_options = self::category_options_for_promo_categories();
        Block::make(__('Promo Block'))
            ->set_description('A block that displays active promos')
            ->add_fields([
                Field::make('select', 'promo_layout', 'Promo Layout')
                    ->add_options([
                        'list' => 'List',
                        'slider' => 'Slider',
                    ])
                    ->set_width(50)
                    ->set_default_value('list'),
                Field::make('select', 'promo_type', 'Promo Type')
                    ->add_options([
                        'all' => 'All',
                        'category' => 'Category',
                    ])
                    ->set_width(50)
                    ->set_default_value('all'),
                Field::make('select', 'promo_category', 'Promo Category')
                    ->add_options($category_options)
                    ->set_default_value(array_keys($category_options)[0])
                    ->set_width(50)
                    ->set_conditional_logic([
                        [
                            'field' => 'promo_type',
                            'value' => 'category',
                        ],
                    ])
            ])
            ->set_icon('megaphone')
            ->set_mode('preview')
            ->set_render_callback(
                function ($fields, $attributes, $inner_blocks) {
                    $promos =$this->model->get_active();
                    if ($fields['promo_type'] === 'category') {
                        $promos = array_filter($promos, function ($promo) use ($fields) {
                            $promo_categories = get_the_terms($promo->ID, 'promo_category');
                            if (is_array($promo_categories)) {
                                foreach ($promo_categories as $category) {
                                    if ($category->term_id === intval($fields['promo_category'])) {
                                        return true;
                                    }
                                }
                            }
                            return false;
                        });
                    }
                    
                    $detect = new MobileDetect;
                    $is_mobile = $detect->isMobile();
                    foreach ($promos as $promo) {
                        $promo->promo_title = carbon_get_post_meta($promo->ID, 'promo_title');
                        $promo->promo_content = carbon_get_post_meta($promo->ID, 'promo_content');
                        $promo->promo_link_text = carbon_get_post_meta($promo->ID, 'promo_link_text');
                        $promo->promo_start_date = carbon_get_post_meta($promo->ID, 'promo_start_date');
                        $promo->promo_end_date = carbon_get_post_meta($promo->ID, 'promo_end_date');
                        $promo->promo_category = get_the_terms($promo->ID, 'promo_category');
                        $promo->promo_desktop_image_type = carbon_get_post_meta($promo->ID, 'desktop_promo_image_type');
                        $promo->images = [
                            'mobile' => wp_get_attachment_image(carbon_get_post_meta($promo->ID, 'mobile_promo_image'), 'full'),
                            'desktop' => [
                                'full' => wp_get_attachment_image(carbon_get_post_meta($promo->ID, 'full_desktop_promo_image'), 'full'),
                                'half' => wp_get_attachment_image(carbon_get_post_meta($promo->ID, 'half_desktop_promo_image'), 'full'),
                            ],
                        ];
                    }

                    // get theme directory
                    $theme = wp_get_theme();
                    $theme_dir = $theme->get_stylesheet_directory();
                    // root plugin directory
                 
                    if (file_exists($theme_dir . '/promo-block.twig')) {
                    $template = $theme_dir . '/promo-block.twig';
                    } else {
                    $template = config_get('plugin.dir') . 'resources/blocks/promo-block.twig';
                    }
                    // include the template with the promos
                    Timber::render(
                        $template,
                        [
                            'promos' => $promos,
                            'promo_layout' => $fields['promo_layout'],
                            'is_mobile' => $is_mobile,
                        ]
                    );
                }
            );
    }
}
