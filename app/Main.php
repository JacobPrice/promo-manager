<?php

namespace LpdPromo;
use OgPlugin\OgCore;

class Main extends OgCore {
    public function on_init() {
        // Scripts & Styles
        $this->add_action('wp_enqueue_scripts', ['\\LpdPromo\\Controllers\\AssetController', 'scripts']);
        $this->add_action('wp_enqueue_scripts', ['\\LpdPromo\\Controllers\\AssetController', 'styles']);

        // Carbon Fields
        $this->add_action('after_setup_theme', ['\\LpdPromo\\Controllers\\PluginsController', 'carbon_fields']);

        // Timber
        $this->add_action('init', ['\\LpdPromo\\Controllers\\PluginsController', 'timber']);
        $this->add_filter('timber/locations', ['\\LpdPromo\\Controllers\\PluginsController', 'timber_locations']);

        // CPT - Promos
        $this->add_action('init', ['\\LpdPromo\\Controllers\\PromoController', 'register_post_type']);
        $this->add_action('carbon_fields_register_fields', ['\\LpdPromo\\Controllers\\PromoController', 'add_custom_fields']);
        $this->add_action('wp', ['\\LpdPromo\\Controllers\\PromoController', 'setup_schedule']);
        $this->add_action('lpd_check_expired_promos', ['\\LpdPromo\\Controllers\\PromoController', 'handle_expired_promos']);
        $this->add_action('template_redirect', ['\\LpdPromo\\Controllers\\PromoController', 'check_promo_public_status']);
        // Taxonomies
        $this->add_action('init', ['\\LpdPromo\\Controllers\\TaxonomyController', 'register_promo_categories'], 0);

        // Custom Promo Block
        $this->add_action('carbon_fields_register_fields', ['\\LpdPromo\\Controllers\\PromoBlockController', 'make_promo_block']);

        // Settings
        $this->add_action('carbon_fields_register_fields', ['\\LpdPromo\\Controllers\\PromoSettingsController', 'register_promo_settings']);

        $this->add_action('wp_head', ['\\LpdPromo\\Controllers\\PromoController', 'add_promo_css_variables_to_root'] );

    }

    public function on_admin() {

        $this->add_action('admin_head', ['\\LpdPromo\\Controllers\\PromoController', 'add_promo_css_variables_to_root'] );
        // Columns for Promos In Admin Area
        $this->add_filter('manage_lpd-promo_posts_columns', ['\\LpdPromo\\Controllers\\PromoController', 'add_new_columns']);
        $this->add_action('manage_lpd-promo_posts_custom_column', ['\\LpdPromo\\Controllers\\PromoController', 'manage_custom_columns'],11, 2);

    }
}