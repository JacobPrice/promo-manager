<?php

namespace LpdPromo\Controllers;

use OgPlugin\AdminMenu;
class AdminController {
    protected $promo_controller;
    public function __construct() {
        $this->promo_controller = new PromoController();
        // $menu = new AdminMenu('LPD Promo', 'LPD Promo', 'manage_options', 'lpd-promo', [$this->promo_controller, 'render'], 'dashicons-awards', 6);
    }

    
}