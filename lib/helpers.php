<?php
if ( ! function_exists( 'config' ) ) {
    function config($key) {
        global $container; // Assuming you have your $container globally available.
        $config = $container->get(LpdPromo\Og\Config::class);
        return $config->get($key);
    }
}
