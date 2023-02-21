<?php

/**
 * The file that defines the core plugin class
 *
 * A class definition that includes attributes and functions used across both the
 * public-facing side of the site and the admin area.
 *
 * @link       https://www.leadpointdigital.com
 * @since      1.0.0
 *
 * @package    Promo_Manager
 * @subpackage Promo_Manager/includes
 */

/**
 * The core plugin class.
 *
 * This is used to define internationalization, admin-specific hooks, and
 * public-facing site hooks.
 *
 * Also maintains the unique identifier of this plugin as well as the current
 * version of the plugin.
 *
 * @since      1.0.0
 * @package    Promo_Manager
 * @subpackage Promo_Manager/includes
 * @author     LeadPoint Digital <jacob@leadpointdigital.com>
 */
class Promo_Manager {

	/**
	 * The loader that's responsible for maintaining and registering all hooks that power
	 * the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      Promo_Manager_Loader    $loader    Maintains and registers all hooks for the plugin.
	 */
	protected $loader;

	/**
	 * The unique identifier of this plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $plugin_name    The string used to uniquely identify this plugin.
	 */
	protected $plugin_name;

	/**
	 * The current version of the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $version    The current version of the plugin.
	 */
	protected $version;

	/**
	 * Define the core functionality of the plugin.
	 *
	 * Set the plugin name and the plugin version that can be used throughout the plugin.
	 * Load the dependencies, define the locale, and set the hooks for the admin area and
	 * the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function __construct() {
		if ( defined( 'PROMO_MANAGER_VERSION' ) ) {
			$this->version = PROMO_MANAGER_VERSION;
		} else {
			$this->version = '1.0.0';
		}
		$this->plugin_name = 'promo-manager';

		$this->load_dependencies();
		$this->set_locale();
		$this->define_admin_hooks();
		$this->define_public_hooks();

	}

	/**
	 * Load the required dependencies for this plugin.
	 *
	 * Include the following files that make up the plugin:
	 *
	 * - Promo_Manager_Loader. Orchestrates the hooks of the plugin.
	 * - Promo_Manager_i18n. Defines internationalization functionality.
	 * - Promo_Manager_Admin. Defines all hooks for the admin area.
	 * - Promo_Manager_Public. Defines all hooks for the public side of the site.
	 *
	 * Create an instance of the loader which will be used to register the hooks
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function load_dependencies() {

		/**
		 * The class responsible for orchestrating the actions and filters of the
		 * core plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-promo-manager-loader.php';

		/**
		 * The class responsible for defining internationalization functionality
		 * of the plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-promo-manager-i18n.php';

		/**
		 * The class responsible for defining all actions that occur in the admin area.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-promo-manager-admin.php';

		/**
		 * The class responsible for defining all actions that occur in the public-facing
		 * side of the site.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'public/class-promo-manager-public.php';

		$this->loader = new Promo_Manager_Loader();

	}

	/**
	 * Define the locale for this plugin for internationalization.
	 *
	 * Uses the Promo_Manager_i18n class in order to set the domain and to register the hook
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function set_locale() {

		$plugin_i18n = new Promo_Manager_i18n();

		$this->loader->add_action( 'plugins_loaded', $plugin_i18n, 'load_plugin_textdomain' );

	}

	/**
	 * Register all of the hooks related to the admin area functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_admin_hooks() {

		$plugin_admin = new Promo_Manager_Admin( $this->get_plugin_name(), $this->get_version() );

		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_styles' );
		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts' );

		// $this->loader->add_action('init', $plugin_admin, 'lpd_promo_init');
		
	}

	/**
	 * Register all of the hooks related to the public-facing functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_public_hooks() {

		$plugin_public = new Promo_Manager_Public( $this->get_plugin_name(), $this->get_version() );

		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_styles' );
		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_scripts' );



	}

	/**
	 * Run the loader to execute all of the hooks with WordPress.
	 *
	 * @since    1.0.0
	 */
	public function run() {
		$this->loader->run();
	}

	/**
	 * The name of the plugin used to uniquely identify it within the context of
	 * WordPress and to define internationalization functionality.
	 *
	 * @since     1.0.0
	 * @return    string    The name of the plugin.
	 */
	public function get_plugin_name() {
		return $this->plugin_name;
	}

	/**
	 * The reference to the class that orchestrates the hooks with the plugin.
	 *
	 * @since     1.0.0
	 * @return    Promo_Manager_Loader    Orchestrates the hooks of the plugin.
	 */
	public function get_loader() {
		return $this->loader;
	}

	/**
	 * Retrieve the version number of the plugin.
	 *
	 * @since     1.0.0
	 * @return    string    The version number of the plugin.
	 */
	public function get_version() {
		return $this->version;
	}

	public function lpd_promo_init()
	{
		register_post_type('lpd_promo', array(
			'labels' => array(
				'name' => __('Promos'),
				'singular_name' => __('Promo'),
				'add_new' => __('Add New Promo'),
				'add_new_item' => __('Add New Promo'),
				'edit_item' => __('Edit Promo'),
				'new_item' => __('Add New Promo'),
				'view_item' => __('View Promo'),
				'search_items' => __('Search Promos'),
				'not_found' => __('No Promos found'),
				'not_found_in_trash' => __('No Promos found in trash')
			),
			'public' => true,
			'supports' => array('title', 'editor', 'thumbnail', 'revisions'),
			'capability_type' => 'post',
			'rewrite' => array("slug" => "promos"), // Permalinks format
			'menu_position' => 5,
			'has_archive' => true
		));

		register_taxonomy('lpd_promo_categories', 'lpd_promo', array(
			'hierarchical' => true,
			'labels' => array(
				'name' => __('Promo Categories', 'taxonomy general name'),
				'singular_name' => __('Promo Category', 'taxonomy singular name'),
				'search_items' =>  __('Search Promo Categories'),
				'all_items' => __('All Promo Categories'),
				'parent_item' => __('Parent Promo Category'),
				'parent_item_colon' => __('Parent Promo Category:'),
				'edit_item' => __('Edit Promo Category'),
				'update_item' => __('Update Promo Category'),
				'add_new_item' => __('Add New Promo Category'),
				'new_item_name' => __('New Promo Category Name'),
				'menu_name' => __('Promo Categories'),
			),
			'show_ui' => true,
			'query_var' => true,
			'rewrite' => array('slug' => 'promos'),
		));
	}

}
