<?php

namespace LpdPromo\PostTypes;

class Promo
{
    public static function init()
    {
        add_action('init', [__CLASS__, 'register']);
        // add_action('init', [__CLASS__, 'registerTaxonomy']);
        // add_action('add_meta_boxes', [__CLASS__, 'addMetaBox']);
        // add_action('save_post', [__CLASS__, 'saveMetaBox']);
        // add_action('manage_promo_posts_custom_column', [__CLASS__, 'manageColumns'], 10, 2);
        // add_filter('manage_promo_posts_columns', [__CLASS__, 'setColumns']);
        // add_filter('post_row_actions', [__CLASS__, 'removeQuickEdit'], 10, 2);
        // add_filter('manage_edit-promo_sortable_columns', [__CLASS__, 'setSortableColumns']);
        // add_action('pre_get_posts', [__CLASS__, 'setOrder']);
    }

    public function register() {
        
    }

}