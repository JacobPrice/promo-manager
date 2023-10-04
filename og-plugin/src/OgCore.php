<?php

namespace OgPlugin;


/**
 * OgCore
 * 
 * This class is a wrapper for WordPress add_action and add_filter functions.
 */
abstract class OgCore {
public function __construct(
        protected $actions = [],
        protected $filters = [],
    ) {
        $this->init();
        $this->add_hooks();
    }
    // TODO: Move init and on_admin to interface contract.
    public function init() {}
    public function on_admin() {}
    public function add_action($hook, $callback, $priority = 10, $accepted_args = 1) {
        $this->actions[] = compact('hook', 'callback', 'priority', 'accepted_args');
        return $this;
    }

    public function add_filter($hook, $callback, $priority = 10, $accepted_args = 1) {
        $this->filters[] = compact('hook', 'callback', 'priority', 'accepted_args');
        return $this;
    }

    public function add_hooks() {
        foreach ($this->actions as $action) {
            add_action($action['hook'], $action['callback'], $action['priority'], $action['accepted_args']);
        }

        foreach ($this->filters as $filter) {
            add_filter($filter['hook'], $filter['callback'], $filter['priority'], $filter['accepted_args']);
        }
    }
}
