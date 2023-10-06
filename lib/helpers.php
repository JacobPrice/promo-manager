<?php
if ( ! function_exists( 'config_get' ) ) {
    function config_get($key) {
        global $container; // Assuming you have your $container globally available.
        $config = $container->get(OgPlugin\Config::class);
        return $config->get($key);
    }
}
