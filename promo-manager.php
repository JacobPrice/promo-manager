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

// register_activation_hook( __FILE__, '\\LpdPromo\\PromoManager::activate' );


add_action( 'admin_notices', function() {
  echo '<div class="notice notice-success is-dismissible"><p>Promo Manager activated</p></div>';
});