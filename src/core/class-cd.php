<?php
/**
 * The file that defines the core plugin class
 *
 * @since      {{VERSION}}
 *
 * @package    ClientDash
 * @subpackage ClientDash/core
 */

if ( ! defined( 'ABSPATH' ) ) {
	die;
}

/**
 * Class ClientDash
 *
 * The main plugin class that begins everything else.
 *
 * @since      {{VERSION}}
 *
 * @package    ClientDash
 * @subpackage ClientDash/core
 */
class ClientDash {

	/**
	 * The color scheme settings.
	 *
	 * @since {{VERSION}}
	 *
	 * @var CD_ColorScheme
	 */
	protected $colorscheme;

	/**
	 * The Dashboard object.
	 *
	 * @since {{VERSION}}
	 *
	 * @var CD_Dashboard
	 */
	public $dashboard;

	private function __clone() {
		_doing_it_wrong( __FUNCTION__, __( 'Cheatin&#8217; huh?', 'ClientDash' ), '2.1' );
	}

	private function __wakeup() {
		_doing_it_wrong( __FUNCTION__, __( 'Cheatin&#8217; huh?', 'ClientDash' ), '2.1' );
	}

	/**
	 * Singleton class.
	 *
	 * @since {{VERSION}}
	 *
	 * @return ClientDash
	 */
	public static function getInstance() {
		static $instance = null;
		if ( null === $instance ) {
			$instance = new static();
		}

		return $instance;
	}

	/**
	 * Constructs the class.
	 *
	 * @since Client Dash 1.5
	 */
	protected function __construct() {

		if ( is_admin() ) {
			$this->load_core();
			$this->add_core_actions();
		}
	}

	/**
	 * Loads all core plugin files.
	 *
	 * @since {{VERSION}}
	 */
	private function load_core() {

		// Core Functions
		require_once __DIR__ . '/cd-core-functions.php';

		// Color Scheme
		require_once __DIR__ . '/class-cd-colorscheme.php';
		$this->colorscheme = new CD_ColorScheme();

		// Dashboard
		require_once __DIR__ . '/dashboard/class-cd-dashboard.php';
		$this->dashboard = new CD_Dashboard();
	}

	/**
	 * Registers all plugin assets.
	 *
	 * @since {{VERSION}}
	 */
	function _register_assets() {

		// Admin script
		wp_register_script(
			'CD-admin',
			CD_URL . '/assets/js/client-dash.min.js',
			array( 'jquery' ),
			defined( 'WP_DEBUG' ) && WP_DEBUG ? time() : CD_VERSION
		);
	}

	/**
	 * Enqueues all immediately necessary plugin assets.
	 *
	 * @since {{VERSION}}
	 */
	function _enqueue_necessities() {

		wp_enqueue_script( 'CD-admin' );
	}

	/**
	 * Adds globally necessary actions.
	 *
	 * @since {{VERSION}}
	 */
	private function add_core_actions() {

		add_action( 'admin_menu', array( $this, '_add_clientdash_menu' ) );
		add_action( 'admin_enqueue_scripts', array( $this, '_enqueue_necessities' ) );
		add_action( 'admin_init', array( $this, '_register_assets' ) );
	}

	/**
	 * Add the primary menu item.
	 *
	 * @since {{VERSION}}
	 */
	function _add_clientdash_menu() {

		add_menu_page(
			__( 'Admin', 'ClientDash' ),
			__( 'Admin', 'ClientDash' ),
			'manage_options',
			'cd-admin',
			null,
			'dashicons-admin-generic',
			61
		);
	}
}