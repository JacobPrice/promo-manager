<?php
require_once __DIR__ . '/vendor/autoload.php';

use Faker\Factory;
use Timber\Timber;
use LpdPromo\Models\Promo;

// add_action('admin_print_scripts', function() {
//     // print alpinejs cdn and tailwindcss cdn
//       echo '<script src="https://cdn.tailwindcss.com"></script>';
//       echo '<script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>';
//   });



add_action('after_setup_theme', 'crb_load');
function crb_load()
{
    \Carbon_Fields\Carbon_Fields::boot();
}


function enqueue_swiper_styles() {
    wp_enqueue_style( 'swiper-styles', 'https://unpkg.com/swiper/swiper-bundle.min.css' );
}
function enqueue_swiper_scripts() {
    wp_enqueue_script( 'swiper-scripts', 'https://unpkg.com/swiper/swiper-bundle.min.js', array('jquery'), '1.0.0', true );
}

add_action( 'wp_enqueue_scripts', 'enqueue_swiper_styles' );
add_action( 'wp_enqueue_scripts', 'enqueue_swiper_scripts' );

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