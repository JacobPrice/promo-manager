<?php

namespace LpdPromo;
use OgPlugin\OgCore;

class Main extends OgCore {
    public function init() {
        // Scripts & Styles
        $this->add_action('wp_enqueue_scripts', ['\\LpdPromo\\Controllers\\AssetController', 'scripts']);
        $this->add_action('wp_enqueue_scripts', ['\\LpdPromo\\Controllers\\AssetController', 'styles']);

        // Carbon Fields
        $this->add_action('after_setup_theme', ['\\LpdPromo\\Controllers\\PluginsController', 'carbon_fields']);

        // Timber
        $this->add_action('init', ['\\LpdPromo\\Controllers\\PluginsController', 'timber']);
        $this->add_filter('timber/locations', ['\\LpdPromo\\Controllers\\PluginsController', 'timber_locations']);

        // Post Types
        $this->add_action('init', ['\\LpdPromo\\Controllers\\PostTypeController', 'register_promos']);

        // Taxonomies
        $this->add_action('init', ['\\LpdPromo\\Controllers\\TaxonomyController', 'register_promo_categories'], 0);
    }

    public function on_admin() {

    }
}