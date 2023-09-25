<?php

namespace LpdPromo\Controllers;

use LpdPromo\Controllers\PromoTable;
use Timber\Timber;
use OgPlugin\TimberController;

class PromoController extends TimberController{

    protected function context() {
        // Example: Fetch some data and add to context
        $this->add('promos', 'test');

        $posts = Timber::get_posts([
            'post_type' => 'lpd-promo',
            'posts_per_page' => -1,
        ]);
        $this->add('posts', $posts);
        
        $promo_table = new PromoTable();
        ob_start();
        $promo_table->display();
        ob_get_clean();
       
        $this->add('promo_table', $promo_table);
    }



    protected function template() {
        return plugin_dir_path(__DIR__) . '/views/index.twig';
    }

// use WP_List_Table;



}