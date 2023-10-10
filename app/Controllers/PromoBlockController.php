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
            [
                'taxonomy' => 'promo_category',
                'hide_empty' => false,
            ]
        );
        if($categories) {
            foreach ($categories as $term) {
                $category_options[$term->term_id] = str_replace('&amp;', '&', $term->name);
            }
        }

        return $category_options;
    }
    public function make_promo_block()
    {
        $category_options = self::category_options_for_promo_categories();
        if(empty($category_options)) {
            $category_options = [
                'none' => 'No Categories',
            ];
        }
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

                        $promo_link_text = carbon_get_post_meta($promo->ID, 'promo_link_text');
                        $promo_link_is_external = carbon_get_post_meta($promo->ID, 'promo_link_is_external');
                        $promo_global_link_text = carbon_get_theme_option('promo_global_link_text');
                        $promo_global_link_is_external = carbon_get_theme_option('promo_global_link_is_external');

                        if ($promo_link_text) {
                            $promo->promo_link_text = $promo_link_text;
                        } else if ($promo_global_link_text) {
                            $promo->promo_link_text = $promo_global_link_text;
                        } else {
                            $promo->promo_link_text = 'Learn More';
                        }

                        if ($promo_link_is_external) {
                            $promo->promo_link_is_external = $promo_link_is_external;
                        } else if ($promo_global_link_is_external) {
                            $promo->promo_link_is_external = $promo_global_link_is_external;
                        } else {
                            $promo->promo_link_is_external = false;
                        }

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
                    /**
                     *  This will pull from the theme first and then the plugin
                     *  referece PluginsController@timber_locations
                     */
                    $template = 'promo-block.twig';

                    $contents = Timber::render(
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
