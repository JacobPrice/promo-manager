<?php

namespace LpdPromo;
use LpdPromo\Og\OgCore;
use LpdPromo\Controllers\AssetController;
use LpdPromo\Controllers\PluginsController;
use LpdPromo\Controllers\PromoController;
use LpdPromo\Controllers\TaxonomyController;
use LpdPromo\Controllers\PromoBlockController;
use LpdPromo\Controllers\PromoSettingsController;


class Main extends OgCore {
    public function on_init() {
        // Scripts & Styles
        $this->add_action('wp_enqueue_scripts', [AssetController::class, 'scripts']);
        $this->add_action('wp_enqueue_scripts', [AssetController::class, 'styles']);

        // Carbon Fields
        $this->add_action('after_setup_theme', [PluginsController::class, 'carbon_fields']);

        // Timber
        $this->add_action('init', [PluginsController::class, 'timber']);
        $this->add_filter('timber/locations', [PluginsController::class, 'timber_locations']);

        // CPT - Promos
        $this->add_action('init', [PromoController::class, 'register_post_type']);
        $this->add_action('carbon_fields_register_fields', [PromoController::class, 'add_custom_fields']);
        $this->add_action('wp', [PromoController::class, 'setup_schedule']);
        $this->add_action('lpd_check_expired_promos', [PromoController::class, 'handle_expired_promos']);
        $this->add_action('template_redirect', [PromoController::class, 'check_promo_public_status']);
        // Taxonomies
        $this->add_action('init', [TaxonomyController::class, 'register_promo_categories'], 0);

        // Custom Promo Block
        $this->add_action('carbon_fields_register_fields', [PromoBlockController::class, 'make_promo_block']);

        // Settings
        $this->add_action('carbon_fields_register_fields', [PromoSettingsController::class, 'register_promo_settings']);

        $this->add_action('wp_head', [PromoController::class, 'add_promo_css_variables_to_root'] );

    }

    public function on_admin() {

        $this->add_action('admin_head', [PromoController::class, 'add_promo_css_variables_to_root'] );
        // Columns for Promos In Admin Area
        $this->add_filter('manage_lpd-promo_posts_columns', [PromoController::class, 'add_new_columns']);
        $this->add_action('manage_lpd-promo_posts_custom_column', [PromoController::class, 'manage_custom_columns'],11, 2);

    }
}