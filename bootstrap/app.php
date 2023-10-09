<?php
use LpdPromo\Main;
use LpdPromo\Og\Config;
use DI\ContainerBuilder;
use LpdPromo\Models\Promo;

require_once plugin_dir_path(__DIR__) . '/vendor/autoload.php';
require_once plugin_dir_path(__DIR__) . '/lib/helpers.php';

// TODO: Need to move this to a package within Og
$builder = new ContainerBuilder();
$builder->addDefinitions([
    Config::class => function() {
        $config_data = include(plugin_dir_path(__DIR__) . 'app/Config/app.php');
        return new Config($config_data);
    },
]);
$builder->useAutowiring(true);
$container = $builder->build();

// if WP_ENVIRONMENT_TYPE is not defined or if its not local, use compiled container
if (!defined('WP_ENVIRONMENT_TYPE') || WP_ENVIRONMENT_TYPE !== 'local') {
    $builder->enableCompilation(plugin_dir_path(__DIR__) . '/storage/cache');
}

$main = new Main($container);

function reset_rewrites() {
    flush_rewrite_rules();
}
register_activation_hook( __FILE__, 'reset_rewrites' );
register_deactivation_hook( __FILE__, 'reset_rewrites' );
