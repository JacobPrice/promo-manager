<?php
// Config file, can be acced via dot syntax for multidimensional arrays using config() helper function
// ex: config('plugin.dir')
return [
    'plugin' => [
        'dir' => plugin_dir_path(dirname(__DIR__)),
        'url' => plugin_dir_url(dirname(__DIR__)),
    ],
    'theme' => [
        'dir' => get_stylesheet_directory(),
        'url' => get_stylesheet_directory_uri(),
    ],
];