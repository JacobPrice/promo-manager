<?php

namespace LpdPromo\Controllers;

if(!class_exists('WP_List_Table')){
    require_once(ABSPATH . 'wp-admin/includes/class-wp-list-table.php');
}

class PromoTable extends \WP_List_Table {
    function get_columns() {
        $columns = [
            'cb'        => '<input type="checkbox" />',
            'name'      => 'Name',
            'email'     => 'Email Address'
        ];
        return $columns;
    }

    function prepare_items() {
        global $faker;
        $this->_column_headers = array($this->get_columns(), [], []);
        $data = [];
        for($i = 0; $i < 100; $i++) {
            $data[] = [
                'ID' => $i,
                'name' => $faker->name,
                'email' => $faker->email,
            ];
        }
        $this->items = $data;
    }

    function column_default($item, $column_name) {
        switch($column_name){
            case 'name':
            case 'email':
                return $item[$column_name];
            default:
                return print_r($item,true);
        }
    }

    function column_cb($item) {
        return sprintf('<input type="checkbox" name="user[]" value="%s" />', $item['ID']);
    }
    
}
