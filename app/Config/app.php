<?php
// Config file, can be acced via dot syntax for multidimensional arrays using config_get() helper function
// ex: config_get('plugin.dir')
return [
    'plugin' => [
        'dir' => plugin_dir_path(dirname(__DIR__)),
        'url' => plugin_dir_url(dirname(__DIR__)),
    ]
];