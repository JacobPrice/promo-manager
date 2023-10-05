<?php

namespace LpdPromo\Controllers;

use Carbon_Fields\Field;
use Carbon_Fields\Container;

class PromoSettingsController {
    public function register_promo_settings() {
        Container::make('theme_options', 'Promo Settings')
        ->add_tab('Default Settings', [
            Field::make('checkbox', 'promo_global_individual_pages_disabled', 'Disable Individual Promo Pages')
            ->set_help_text('Check this box to disable individual promo pages. This can be overridden on individual promos.'),
            Field::make('checkbox', 'promo_global_link_is_external', 'Link The Promos Globally To A Specific Link')
            ->set_help_text('Check this box to link all promos to a specific link. This is useful if individual promo pages are not needed. This can be overridden on individual promos.'),
            Field::make('text', 'promo_global_link_text', 'Promo Global Link Text')
            ->set_help_text('This is the text that will be displayed on the promo link/button.')
            ->set_width(50)
            ->set_conditional_logic([
                [
                    'field' => 'promo_global_link_is_external',
                    'value' => true,
                ],
            ]),
            Field::make('text', 'promo_global_link_external', 'Promo Global Link External')
            ->set_help_text('This is the link that will be used for the promo.')
            ->set_width(50)
            ->set_attribute('type', 'url')
            ->set_conditional_logic([
                [
                    'field' => 'promo_global_link_is_external',
                    'value' => true,
                ],
            ]),
        ])
        ->add_tab('Custom Styles',[
            Field::make('color', 'promo_theme_color', 'Promo Theme Color Primary')
            ->set_help_text('This colors the following: Link Background, Next/Prev Arrows, Active Slide Dot')
            ->set_width(25),

            Field::make('color', 'promo_theme_color_secondary', 'Promo Theme Color Secondary')
            ->set_help_text('This colors the following: Link Text, Next/Prev Arrows Hover, Inactive Slide Dot')
            ->set_width(25),
            Field::make('text', 'promo_theme_navigation_size', 'Promo Theme Navigation Size')
            ->set_help_text('This sets the size of the next/prev arrows and the slide dots. If left blank it will default to 20px.')
            ->set_width(25)
            ->set_attribute('type', 'number'),
            Field::make('text', 'promo_theme_navigation_sides_offset', 'Promo Theme Navigation Sides Offset')
            ->set_help_text('This sets the offset of the next/prev arrows. If left blank it will default to 10px.')
            ->set_width(25)
            ->set_attribute('type', 'number'),
        ])
        ->set_page_parent('edit.php?post_type=lpd-promo');
    }
}