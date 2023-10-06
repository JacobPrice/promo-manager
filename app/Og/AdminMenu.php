<?php

namespace LpdPromo\Og;

use Timber\Timber;

class AdminMenu {

    
    private $submenus = [];
    public function __construct(private $pageTitle, private $menuTitle, private $capability, private $menuSlug, public $controller, private $icon = 'dashicons-admin-generic', private $position = null) {
        add_action('admin_menu', [$this, 'register']);
    }

    public function addSubMenu($pageTitle, $menuTitle, $capability, $renderer, $menuSlug = null) {
        $menuSlug = $menuSlug ?: $this->menuSlug . '-' . sanitize_title($menuTitle);
        $this->submenus[] = compact('pageTitle', 'menuTitle', 'capability', 'menuSlug', 'renderer');
        return $this;
    }


    public function setRender($callback) {
        $this->controller = $callback;
        return $this;
    }

    public function register() {
        add_menu_page(
            $this->pageTitle, 
            $this->menuTitle, 
            $this->capability, 
            $this->menuSlug, 
            $this->controller ?: [$this, 'defaultIndex'], 
            $this->icon, 
            $this->position
        );

        foreach ($this->submenus as $submenu) {
            add_submenu_page(
                $this->menuSlug,
                $submenu['pageTitle'], 
                $submenu['menuTitle'], 
                $submenu['capability'], 
                $submenu['menuSlug'], 
               [$submenu['controller'], 'index']
            );
        }
    }

    public function defaultIndex() {
        echo 'Default Render for Admin Page!';
    }
}
