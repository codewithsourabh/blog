<?php
/**
 * The core plugin class.
 *
 * @package    BlogsHQ
 * @subpackage BlogsHQ/includes
 * @since      1.0.0
 */

if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * The core plugin class.
 */
class BlogsHQ {

	/**
	 * The loader that's responsible for maintaining and registering all hooks.
	 *
	 * @var BlogsHQ_Loader
	 */
	protected $loader;

	/**
	 * The unique identifier of this plugin.
	 *
	 * @var string
	 */
	protected $plugin_name;

	/**
	 * The current version of the plugin.
	 *
	 * @var string
	 */
	protected $version;

	/**
	 * Module instances.
	 *
	 * @var array
	 */
	protected $modules = array();

	/**
	 * Service container for dependency injection.
	 *
	 * @var array
	 */
	private $container = array();

	/**
	 * Define the core functionality of the plugin.
	 */
	public function __construct() {
		$this->version     = BLOGSHQ_VERSION;
		$this->plugin_name = 'blogshq';

		$this->load_dependencies();
		$this->set_locale();
		$this->define_admin_hooks();
		$this->define_module_hooks();
		$this->define_public_hooks();
	}

	/**
	 * Register a service in the container.
	 *
	 * @param string   $name     Service name.
	 * @param callable $callback Service factory callback.
	 */
	public function register_service( $name, $callback ) {
		$this->container[ $name ] = $callback;
	}

	/**
	 * Get a service from the container.
	 *
	 * @param string $name Service name.
	 * @return mixed|null Service instance or null if not found.
	 */
	public function get_service( $name ) {
		if ( ! isset( $this->container[ $name ] ) ) {
			return null;
		}

		if ( is_callable( $this->container[ $name ] ) ) {
			$this->container[ $name ] = call_user_func( $this->container[ $name ] );
		}

		return $this->container[ $name ];
	}

	/**
	 * Load the required dependencies for this plugin.
	 */
	private function load_dependencies() {
		require_once BLOGSHQ_PLUGIN_DIR . 'includes/class-blogshq-loader.php';
		require_once BLOGSHQ_PLUGIN_DIR . 'includes/class-blogshq-i18n.php';
		require_once BLOGSHQ_PLUGIN_DIR . 'admin/class-blogshq-admin.php';

		if ( file_exists( BLOGSHQ_PLUGIN_DIR . 'includes/helpers.php' ) ) {
			require_once BLOGSHQ_PLUGIN_DIR . 'includes/helpers.php';
		}

		$this->loader = new BlogsHQ_Loader();
	}

	/**
	 * Define the locale for this plugin for internationalization.
	 */
	private function set_locale() {
		$plugin_i18n = new BlogsHQ_I18n();
		$this->loader->add_action( 'plugins_loaded', $plugin_i18n, 'load_plugin_textdomain' );
	}

	/**
	 * Register all hooks related to the admin area functionality.
	 */
	private function define_admin_hooks() {
		$plugin_admin = new BlogsHQ_Admin( $this->get_plugin_name(), $this->get_version() );

		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_styles' );
		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts' );
		$this->loader->add_action( 'admin_menu', $plugin_admin, 'add_plugin_admin_menu' );
		$this->loader->add_filter( 'plugin_action_links_' . BLOGSHQ_BASENAME, $plugin_admin, 'add_action_links' );
	}

	/**
	 * Register all hooks for plugin modules (FAQ removed).
	 */
	private function define_module_hooks() {
		if ( class_exists( 'BlogsHQ_Logos' ) ) {
			$this->modules['logos'] = new BlogsHQ_Logos();
			$this->loader->add_action( 'init', $this->modules['logos'], 'init' );
		}

		if ( class_exists( 'BlogsHQ_TOC' ) ) {
			$this->modules['toc'] = new BlogsHQ_TOC();
			$this->loader->add_action( 'init', $this->modules['toc'], 'init' );
		}

		if ( class_exists( 'BlogsHQ_AI_Share' ) ) {
			$this->modules['ai_share'] = new BlogsHQ_AI_Share();
			$this->loader->add_action( 'init', $this->modules['ai_share'], 'init' );
		}
	}

	/**
	 * Register all hooks related to the public-facing functionality.
	 */
	private function define_public_hooks() {
		$this->loader->add_action( 'wp_enqueue_scripts', $this, 'enqueue_public_styles', 20 );

		if ( isset( $this->modules['toc'] ) ) {
			$this->loader->add_filter( 'the_content', $this->modules['toc'], 'insert_toc_and_anchors', 5 );
		}
	}

	/**
	 * Enqueue public-facing stylesheets.
	 */
	public function enqueue_public_styles() {
		wp_enqueue_style(
			$this->plugin_name,
			BLOGSHQ_PLUGIN_URL . 'assets/css/frontend.min.css',
			array(),
			$this->version,
			'all'
		);
	}

	/**
	 * Run the loader to execute all hooks.
	 */
	public function run() {
		$this->loader->run();
	}

	/**
	 * The name of the plugin used to uniquely identify it.
	 *
	 * @return string
	 */
	public function get_plugin_name() {
		return $this->plugin_name;
	}

	/**
	 * The reference to the class that orchestrates the hooks.
	 *
	 * @return BlogsHQ_Loader
	 */
	public function get_loader() {
		return $this->loader;
	}

	/**
	 * Retrieve the version number of the plugin.
	 *
	 * @return string
	 */
	public function get_version() {
		return $this->version;
	}
}