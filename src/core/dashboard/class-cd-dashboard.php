<?php
/**
 * Initiates all dashboard related functionality.
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
 * Class CD_Dashboard
 *
 * Initiates all dashboard related functionality.
 *
 * @since      {{VERSION}}
 *
 * @package    ClientDash
 * @subpackage ClientDash/core/dashboard
 */
class CD_Dashboard {

	/**
	 * Constructs the class.
	 *
	 * @since Client Dash 1.5
	 */
	public function __construct() {

		$this->require_files();
		$this->enqueue_files();
		$this->add_actions();
	}

	/**
	 * Loads dashboard files.
	 *
	 * @since {{VERSION}}
	 */
	private function require_files() {

	}

	/**
	 * Enqueues dashboard assets.
	 *
	 * @since {{VERSION}}
	 */
	private function enqueue_files() {

	}

	/**
	 * Adds globally necessary actions.
	 *
	 * @since {{VERSION}}
	 */
	private function add_actions() {

		add_action( 'admin_menu', array( $this, '_add_submenu_page' ) );
		add_action( 'wp_dashboard_setup', array( $this, '_save_dashboard_widgets' ), 9999 );

		// Actions for the settings page only
		if ( isset( $_REQUEST['page'] ) && $_REQUEST['page'] == 'cd-dashboard' ) {

			add_action( 'admin_init', array( $this, '_setup_widgets_page' ) );
			add_action( 'load-widgets.php', array( $this, '_load_widgets_page' ) );
			add_action( 'setup_theme', array( $this, '_cd_widget_factory_load' ), 0, 0 );
			add_action( 'widgets_admin_page', array( $this, '_add_sidebars' ) );
			add_action( 'widgets_admin_page', array( $this, '_add_widgets' ) );
		}
	}

	/**
	 * Adds the Dashboard customization page to Admin.
	 *
	 * @since {{VERSION}}
	 */
	function _add_submenu_page() {

		add_submenu_page(
			'cd-admin',
			__( 'Dashboard', 'ClientDash' ),
			__( 'Dashboard', 'ClientDash' ),
			'manage_options',
			'cd-dashboard',
			'function'
		);
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
	 * Loads in the widgets page file.
	 *
	 * @since {{VERSION}}
	 */
	function _load_widgets_page() {

		// The file loses global access, so I have to add it back in
		global $wp_registered_sidebars;

		require ABSPATH . '/wp-admin/widgets.php';
	}

	function _cd_widget_factory_load() {

		require_once __DIR__ . '/class-cd-widget-factory.php';
		$GLOBALS['wp_widget_factory'] = CD_WP_Widget_Factory::get_instance();
	}

	/**
	 * Add the CD sidebars.
	 *
	 * @since {{VERSION}}
	 */
	function _add_sidebars() {

		global $wp_registered_sidebars;

		// Reset it (to prevent seeing the normal sidebars)
		$wp_registered_sidebars = array();

		foreach ( get_editable_roles() as $ID => $role ) {

			register_sidebar( array(
				'id'   => $ID,
				'name' => $role['name'],
			) );
		}
	}

	/**
	 * Adds widgets to Available Widgets on the settings page.
	 *
	 * @since {{VERSION}}
	 */
	function _add_widgets() {

		global $wp_registered_widgets;

		$wp_registered_widgets = array();

		$dashboard_widgets = get_option( 'cd_dashboard_widgets' );

		if ( $dashboard_widgets ) {
			foreach ( $dashboard_widgets as $i => $widget ) {
				$dashboard_widgets[ $i ]['name'] = $widget['title'];
				$this->register_widget( $widget );
			}
		}

		$wp_registered_widgets = $dashboard_widgets;
	}

	private function register_widget( $widget ) {

		require_once __DIR__ . '/class-cd-widget.php';

		/** @var WP_Widget_Factory $wp_widget_factory */
		global $wp_widget_factory;

		$wp_widget_factory->register( 'CD_Widget', $widget );
	}

	/**
	 * Saves the added widgets when on the dashboard.
	 *
	 * @since {{VERSION}}
	 */
	function _save_dashboard_widgets() {

		global $wp_meta_boxes;

		$dashboard_widgets = array();
		if ( isset( $wp_meta_boxes['dashboard'] ) ) {
			foreach ( $wp_meta_boxes['dashboard'] as $position => $priorities ) {
				foreach ( $priorities as $priority => $widgets ) {
					foreach ( $widgets as $ID => $widget ) {

						$dashboard_widgets[ $ID ] = array_merge( $widget, array(
							'position' => $position,
							'priority' => $priority,
						) );
					}
				}
			}
		}

		update_option( 'cd_dashboard_widgets', $dashboard_widgets );
	}
}