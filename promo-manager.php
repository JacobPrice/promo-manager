<?php
/**
 * Plugin Name: Promo Manager
 * Plugin URI:
 * Description: Manage promotional content
 * Version: 1.0
 * Author: LeadPoint Digital
 * Author URI:
 */

 if (file_exists($composer_autoload = __DIR__.'/vendor/autoload.php')) {
    require_once $composer_autoload;
  }

 
define('BB_MORPH_HASH_KEY','ej92WM8DyzhShaa4' );

\LpdPromo\PostTypes\Promo::init();

/**
 * activate the plugin
 */
/**
 * When activating we should do these
 * - Register custom post type of lpd-promo from the Promo Class
 * - Register the menu page for the plugin
 */

function activate() {
  \LpdPromo\PostTypes\Promo::init();
  \LpdPromo\Pages\Index::register_menu();
  flush_rewrite_rules();
}

register_activation_hook( __FILE__, 'activate' );


