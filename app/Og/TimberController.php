<?php

namespace LpdPromo\Og;

use Timber\Timber;

abstract class TimberController {

    protected $template;
    protected $context;

    public function __construct($template = '', $context = []) {
        $this->template = $template;
        $this->context = $context;
    }

    // To be implemented by child classes if needed
    protected function context() {}
    protected function template() {}
    protected function add($key, $value) {
        $this->context[$key] = $value;
    }
    public function render() {
        $this->context();
        $this->template = $this->template ?: $this->template();
        Timber::render($this->template, $this->context);
    }
}
