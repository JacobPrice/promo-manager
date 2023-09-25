<?php
require_once __DIR__ . '/vendor/autoload.php';

use Timber\Timber;
use LpdPromo\Models\Promo;


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