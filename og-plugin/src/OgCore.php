<?php

namespace OgPlugin;

use DI\Container;

abstract class OgCore {

    protected $actions = [];
    protected $filters = [];
    private $hook_info = [];
    private $container;

    public function __construct(Container $container) {
        $this->container = $container;
        $this->on_init();
        if (is_admin()) {
            $this->on_admin();
        }
        $this->add_hooks();
    }

    // TODO: move these to interface
    protected function on_init() {}

    protected function on_admin() {}

    public function add_action(string $hook, array $callback, int $priority = 10, int $accepted_args = 1): void {
        add_action($hook, [$this, 'method_runner'], $priority, $accepted_args);
        $this->store_hook($hook, $callback, $accepted_args);
    }

    public function add_filter(string $hook, array $callback, int $priority = 10, int $accepted_args = 1): void {
        add_filter($hook, [$this, 'method_runner'], $priority, $accepted_args);
        $this->store_hook($hook, $callback, $accepted_args);
    }

    private function store_hook(string $hook, array $callback, int $accepted_args): void {
        $this->hook_info[$hook][] = [
            'class' => $callback[0],
            'method' => $callback[1],
            'args_count' => $accepted_args
        ];
    }

    public function method_runner(...$args): mixed {
        $hook = current_filter();
        if (!isset($this->hook_info[$hook])) {
            return $args[0]; // Return the original value
        }
        $return_value = $args[0]; // Capture the original value for filters
        foreach ($this->hook_info[$hook] as $info) {
            if (count($args) > $info['args_count']) {
                $args = array_slice($args, 0, $info['args_count']);
            }
            $method = $this->run_method($info['class'], $info['method'], $args);
            if (!empty($method)) {
                $result = call_user_func_array($method, $args);
                if ($result !== null) {
                    $return_value = $result; // Capture the return value
                }
            }
        }
        return $return_value; // Return the captured value or the original value for filters
    }


    private function run_method(string $class_name, string $method_name, array $args = []): array {
        if (!class_exists($class_name)) {
            // TODO: Implement Logging Here
            return [];
        }

        $instance = $this->container->get($class_name);

        if (method_exists($instance, $method_name)) {
            return [$instance, $method_name];
        }

        return [];
    }

    public function add_hooks(): void {
        foreach ($this->actions as $action) {
            $this->add_action($action['hook'], $action['callback'], $action['priority'], $action['accepted_args']);
        }

        foreach ($this->filters as $filter) {
            $this->add_filter($filter['hook'], $filter['callback'], $filter['priority'], $filter['accepted_args']);
        }
    }
}
