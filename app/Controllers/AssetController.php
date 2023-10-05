<?php

namespace LpdPromo\Controllers;

class AssetController {
    public function scripts() {
        wp_enqueue_script( 'swiper-scripts', 'https://unpkg.com/swiper/swiper-bundle.min.js', [], '1.0.0', true );
        wp_enqueue_script( 'swiper-slider-script', plugin_dir_url(__FILE__) . '/resources/assets/scripts/swiper.js', [], '', true );

    }
    public function styles() {
        wp_enqueue_style( 'swiper-styles', 'https://unpkg.com/swiper/swiper-bundle.min.css' );
        wp_enqueue_style( 'swiper-slider-style', plugin_dir_url(__FILE__) . '/resources/assets/styles/swiper.css', [] );
    }

}
