<?php

namespace LpdPromo\Og;

abstract class PostController
{
    protected $model;

    public function __construct($model = null) {
        $this->model = $model;
    }
    
    /**
     * register_post_type
     * 
     * Register the post type with WordPress
     *
     * @return void
     */
    public function register_post_type() {
       register_post_type(
           $this->model->post_type,
           $this->model->post_type_args
       );
    }
}
