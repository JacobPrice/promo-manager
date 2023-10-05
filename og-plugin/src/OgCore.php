<?php

namespace OgPlugin;

abstract class OgCore {

    protected $actions = [];
    protected $filters = [];
    private $instances = [];
    private $hook_info = [];

    public function __construct() {
        $this->init();
        if (is_admin()) {
            $this->on_admin();
        }
        $this->add_hooks();
    }

    /**
     * init
     *
     * Used by subclasses to add hooks and filters
     * Example: $this->add_action('init', ['\\LpdPromo\\Controllers\\PostTypeController', 'register_promos']);
     * @return void
     */
    protected function init() {}
    
    /**
     * on_admin
     *
     * Used by subclasses to add hooks and filters only on admin
     * Example: $this->add_action('admin_init', ['\\LpdPromo\\Controllers\\PostTypeController', 'register_promos']);
     * @return void
     */
    protected function on_admin() {}

    /**
     * Add a new action to the list of actions to be run.
     *
     * @param string   $hook          The name of the action hook.
     * @param array    $callback      The callback to be called when the action is run.
     * @param int      $priority      The priority of the action.
     * @param int      $accepted_args The number of arguments that the callback accepts.
     */
    public function add_action(string $hook, array $callback, int $priority = 10, int $accepted_args = 1): void {
        add_action($hook, [$this, 'method_runner'], $priority, $accepted_args);
        $this->store_hook($hook, $callback, $accepted_args);
    }

    /**
     * Add a new filter to the list of filters to be run.
     *
     * @param string   $hook          The name of the filter hook.
     * @param array    $callback      The callback to be called when the filter is run.
     * @param int      $priority      The priority of the filter.
     * @param int      $accepted_args The number of arguments that the callback accepts.
     */
    public function add_filter(string $hook, array $callback, int $priority = 10, int $accepted_args = 1): void {
        add_filter($hook, [$this, 'method_runner'], $priority, $accepted_args);
        $this->store_hook($hook, $callback, $accepted_args);
    }


    /**
     * Stores callback info for a hook.
     *
     * @param string $hook The hook name.
     * @param array $callback The callback info.
     * @param int $accepted_args The number of arguments the callback accepts.
     */
    private function store_hook(string $hook, array $callback, int $accepted_args): void {
        $this->hook_info[$hook][] = [
            'class' => $callback[0],
            'method' => $callback[1],
            'args_count' => $accepted_args
        ];
    }

    /**
     * Runs a method.
     *
     * @param mixed ...$args The arguments to pass to the method.
     */
    public function method_runner(...$args): void {
        $hook = current_filter();
        if (!isset($this->hook_info[$hook])) {
            return;
        }

        foreach ($this->hook_info[$hook] as $info) {
            if (count($args) > $info['args_count']) {
                $args = array_slice($args, 0, $info['args_count']);
            }
            $this->run_method($info['class'], $info['method'], $args);
        }
    }

    /**
     * Runs a method.
     *
     * @param string $class_name The name of the class.
     * @param string $method_name The name of the method.
     * @param array $args The arguments to pass to the method.
     */
    private function run_method(string $class_name, string $method_name, array $args = []): void {
        if (!isset($this->instances[$class_name])) {
            // Consider checking class_exists($class_name) before instantiating
            $this->instances[$class_name] = new $class_name();
        }

        // Check if method exists before calling
        if (method_exists($this->instances[$class_name], $method_name)) {
            call_user_func_array([$this->instances[$class_name], $method_name], $args);
        }
    }

    /**
     * Adds the hooks and filters to WordPress.
     */
    public function add_hooks(): void {
        foreach ($this->actions as $action) {
            $this->add_action($action['hook'], $action['callback'], $action['priority'], $action['accepted_args']);
        }

        foreach ($this->filters as $filter) {
            $this->add_filter($filter['hook'], $filter['callback'], $filter['priority'], $filter['accepted_args']);
        }
    }

}
