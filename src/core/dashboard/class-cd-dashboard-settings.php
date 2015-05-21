<?php
/**
 * Sets up the dashboard settings page.
 *
 * @since      {{VERSION}}
 *
 * @package    ClientDash
 * @subpackage ClientDash/core/dashboard
 */

if ( ! defined( 'ABSPATH' ) ) {
	die;
}

/**
 * Class CD_Dashboard_Settings
 *
 * Sets up the dashboard settings page.
 *
 * @since      {{VERSION}}
 *
 * @package    ClientDash
 * @subpackage ClientDash/core/dashboard
 */
class CD_Dashboard_Settings {

	public function __construct() {
		$this->add_actions();
	}

	private function add_actions() {

		add_action( 'widgets_init', array( $this, '_remove_widgets' ), 99 );

		// Don't do during AJAX
		if ( ! isset( $_POST['cd_widget'] ) ) {

			add_action( 'admin_enqueue_scripts', array( $this, '_enqueue_scripts' ) );
			add_action( 'admin_init', array( $this, '_setup_widgets_page' ) );
			add_action( 'admin_body_class', array( $this, '_reset_page' ) );
			add_action( 'admin_body_class', array( $this, '_add_class' ) );
			add_action( 'adminmenu', array( $this, '_setup_widgets_page' ) );
			add_action( 'load-widgets.php', array( $this, '_load_widgets_page' ) );
		}
	}

	/**
	 * Removes all registered widgets
	 *
	 * @since {{VERSION}}
	 */
	function _remove_widgets() {

		global $wp_widget_factory;

		foreach ( (array) $wp_widget_factory->widgets as $classname => $widget ) {
			unregister_widget( $classname );
		}
	}

	/**
	 * Enqueues scripts specific to this page.
	 *
	 * @since {{VERSION}}
	 */
	function _enqueue_scripts() {
		wp_enqueue_script( 'jquery-effects-shake' );
	}

	/**
	 * Tricks WP into thinking we're on the Widgets page.
	 *
	 * @since {{VERSION}}
	 */
	function _setup_widgets_page() {

		global $plugin_page, $pagenow, $parent_file;

		$plugin_page = null;
		$pagenow     = 'widgets.php';
		$parent_file = 'themes.php';
	}

	/**
	 * Sets the page properties back to the original.
	 *
	 * @since {{VERSION}}
	 */
	function _reset_page() {

		global $plugin_page, $pagenow, $parent_file;

		$plugin_page = 'cd-dashboard';
		$pagenow     = 'admin.php';
		$parent_file = 'cd-admin';
	}

	/**
	 * Adds some body classes.
	 *
	 * @since {{VERSION}}
	 *
	 * @param string $class The body classes.
	 *
	 * @return string The new classes.
	 */
	function _add_class( $class ) {
		return $class . ' cd-dashboard cd-admin';
	}

	/**
	 * Loads in the widgets page file.
	 *
	 * @since {{VERSION}}
	 */
	function _load_widgets_page() {

		// The file loses global access, so I have to add it back in
		global $wp_registered_sidebars;
		require ABSPATH . '/wp-admin/widgets.php';
	}
}