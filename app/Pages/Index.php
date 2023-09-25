<?php

namespace LpdPromo\Pages;

use Timber\Timber;

class Index {
    public function __construct()
    {
    }

    static function register_admin_menu()
    {
        add_action('admin_menu', function() {
            add_menu_page(
                'LPD Promo',
                'LPD Promo',
                'manage_options',
                'lpd-promo',
                [__CLASS__, 'index'],
                'dashicons-awards',
                6
            );
        });
    }

    public static function index()
    {
        $view = new Index();
        $view->view('index', ['title' => 'Promo Manager']);
    }

    public function view($view, $data = [])
    {
        $view = dirname(__DIR__) . '/views/' . $view . '.twig';
        $data = array_merge($data, Timber::context());
        Timber::render($view, $data);
    }
}