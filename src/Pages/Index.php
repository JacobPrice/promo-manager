<?php

namespace LpdPromo\Pages;

class Index {
    public function __construct()
    {
    }

    static function register_menu()
    {
        add_menu_page(
            'LPD Promo',
            'LPD Promo',
            'manage_options',
            'lpd-promo',
            [__CLASS__, 'index'],
            'dashicons-awards',
            6
        );
    }

    public function index()
    {
        $this->view('index');
    }

    public function view($view, $data = [])
    {
        extract($data);

        $view = __DIR__ . '/views/' . $view . '.php';

        if(file_exists($view)) {
            require_once($view);
        }
    }
}