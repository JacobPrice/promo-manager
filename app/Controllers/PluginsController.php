<?php

namespace LpdPromo\Controllers;

class PluginsController {
    public static function carbon_fields() {
        \Carbon_Fields\Carbon_Fields::boot();
    }

    public function timber_locations($paths) {
            $paths[] = [
                plugin_dir_path(__FILE__) . 'resources/views',
                plugin_dir_path(__FILE__) . 'resources/blocks',
            ];
            return $paths;
    }
    public function timber() {
        \Timber\Timber::init();
    }
}