<?php
require_once __DIR__ . '/vendor/autoload.php';

use Faker\Factory;
use Timber\Timber;
use LpdPromo\Models\Promo;
use LpdPromo\Og\OgCore;



add_action('after_setup_theme', 'crb_load');
function crb_load()
{
    \Carbon_Fields\Carbon_Fields::boot();
}


function enqueue_swiper_styles() {
    wp_enqueue_style( 'swiper-styles', 'https://unpkg.com/swiper/swiper-bundle.min.css' );
}
function enqueue_swiper_scripts() {
    wp_enqueue_script( 'swiper-scripts', 'https://unpkg.com/swiper/swiper-bundle.min.js', [], '1.0.0', true );
}
function enqueue_swiper_script_for_slider() {
    wp_enqueue_script( 'swiper-slider-script', plugin_dir_url(__FILE__) . '/resources/assets/scripts/swiper.js', [], '', true );
}
function enqueue_swiper_style_for_slider() {
    wp_enqueue_style( 'swiper-slider-style', plugin_dir_url(__FILE__) . '/resources/assets/styles/swiper.css', [] );
}



add_action( 'wp_enqueue_scripts', 'enqueue_swiper_styles' );
add_action( 'wp_enqueue_scripts', 'enqueue_swiper_scripts' );
add_action( 'wp_enqueue_scripts', 'enqueue_swiper_script_for_slider');
add_action( 'wp_enqueue_scripts', 'enqueue_swiper_style_for_slider');


$faker = Factory::create();
// make faker global
$GLOBALS['faker'] = $faker;

// autoload

// use LpdPromo\PromoManager;


// // register_activation_hook( __FILE__, '\\LpdPromo\\PromoManager::activate' );


// PromoManager::init();

// Include route definitions.
// new \LpdPromo\Controllers\AdminController();
// require_once plugin_dir_path(__FILE__) . 'routes/routes.php';

add_filter('timber/locations', function ($paths) {
    $paths[] = [
        plugin_dir_path(__FILE__) . 'app/views',
    ];
    return $paths;
});

Timber::init();

new LpdPromo\Controllers\AdminController();

new LpdPromo\Controllers\PromoController();
Promo::init();