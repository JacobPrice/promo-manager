<?php

namespace LpdPromo\Controllers;

class PluginsController {
    public function __construct() {
    }
    public static function carbon_fields( ) {
        \Carbon_Fields\Carbon_Fields::boot();
    }

    public function timber_locations($paths) {
            $paths[] = [
                config('theme.dir'),
                config('plugin.dir') . 'resources/blocks'
            ];
            return $paths;
    }
    public function timber() {
        \Timber\Timber::init();
    }
}