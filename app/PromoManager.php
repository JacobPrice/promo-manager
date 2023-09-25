<?php

namespace LpdPromo;

class PromoManager {
    public function _construct() {

    }

    public static function init() {
        \LpdPromo\PostTypes\Promo::init();
        \LpdPromo\Pages\Index::register_admin_menu();
        flush_rewrite_rules();
        add_option( 'activated_plugin', 'lpd-promo-manager' );

        // provide an admin notice of successful activation similar to plugin activated
      }

      //Fires 
      public function load() {
        if ( is_admin() ) {
            add_action( 'admin_notices', array( $this, 'admin_notices' ) );
        }
      }

        public static function deactivate() {
            flush_rewrite_rules();
            // provide an admin notice

        }
}