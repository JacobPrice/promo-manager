<?php

namespace OgPlugin;

abstract class OgCore {

    protected $actions = [];
    protected $filters = [];
    private $instances = [];
    private $hook_info = [];

    public function __construct() {
        $this->init();
        $this->add_hooks();
    }

    protected function init() {}
    protected function on_admin() {}

    public function add_action($hook, $callback, $priority = 10, $accepted_args = 1) {
        add_action($hook, [$this, 'method_runner'], $priority, $accepted_args);
        $this->store_hook($hook, $callback, $accepted_args);
    }

    public function add_filter($hook, $callback, $priority = 10, $accepted_args = 1) {
        add_filter($hook, [$this, 'method_runner'], $priority, $accepted_args);
        $this->store_hook($hook, $callback, $accepted_args);
    }

    private function store_hook($hook, $callback, $accepted_args) {
        $this->hook_info[$hook][] = [
            'class' => $callback[0],
            'method' => $callback[1],
            'args_count' => $accepted_args
        ];
    }

    public function method_runner() {
        $hook = current_filter();
        if (isset($this->hook_info[$hook])) {
            $args = func_get_args();
            foreach ($this->hook_info[$hook] as $info) {
                if (count($args) > $info['args_count']) {
                    $args = array_slice($args, 0, $info['args_count']);
                }
                $this->runMethod($info['class'], $info['method'], $args);
            }
        }
    }

    private function runMethod($class_name, $method_name, array $args = []) {
        if (!isset($this->instances[$class_name])) {
            $this->instances[$class_name] = new $class_name();
        }

        call_user_func_array([$this->instances[$class_name], $method_name], $args);
    }

    public function add_hooks() {
        foreach ($this->actions as $action) {
            $this->add_action($action['hook'], $action['callback'], $action['priority'], $action['accepted_args']);
        }

        foreach ($this->filters as $filter) {
            $this->add_filter($filter['hook'], $filter['callback'], $filter['priority'], $filter['accepted_args']);
        }
    }

}
