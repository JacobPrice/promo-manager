<?php
use LpdPromo\Main;
use DI\ContainerBuilder;

require_once plugin_dir_path(__DIR__) . '/vendor/autoload.php';
// TODO: Need to move this to a package within Og
$builder = new ContainerBuilder();
$builder->addDefinitions(plugin_dir_path(__DIR__) . '/app/config.php');
$builder->useAutowiring(true);
$container = $builder->build();

// if WP_ENVIRONMENT_TYPE is not defined or if its not local, use compiled container
if (!defined('WP_ENVIRONMENT_TYPE') || WP_ENVIRONMENT_TYPE !== 'local') {
    $builder->enableCompilation(plugin_dir_path(__DIR__) . '/storage/cache');
}

new Main($container);