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

// FIXME only add dash widgets on dashboard AND settings page. Currently loading everywhere
// TODO figure out best way to sort, or not sort, widgets

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
	 * The widgets to show on the dashboard.
	 *
	 * @since {{VERSION}}
	 *
	 * @var array
	 */
	public $dash_widgets;

	/**
	 * Registered CD Widgets
	 *
	 * @since {{VERSION}}
	 *
	 * @var array
	 */
	public $cd_widgets;

	/**
	 * The settings page object.
	 *
	 * @since {{VERSION}}
	 *
	 * @var CD_Dashboard_Settings
	 */
	public $settings;

	/**
	 * Constructs the class.
	 *
	 * @since Client Dash 1.5
	 */
	public function __construct() {

		$this->load_dependencies();
		$this->add_actions();
	}

	/**
	 * Loads necessary files for the Dashboard.
	 *
	 * @since {{VERSION}}
	 */
	private function load_dependencies() {

		require_once __DIR__ . '/cd-dashboard-functions.php';
		require_once __DIR__ . '/includes/cd-dash-widgets.php';
	}

	/**
	 * Adds globally necessary actions.
	 *
	 * @since {{VERSION}}
	 */
	private function add_actions() {

		$page = isset( $_REQUEST['page'] ) ? $_REQUEST['page'] : false;

		add_action( 'cd_translations', array( $this, '_translations' ) );

		add_action( 'admin_menu', array( $this, '_add_submenu_page' ) );

		add_action( 'setup_theme', array( $this, '_cd_widget_factory_load' ), 0, 0 );

		add_action( 'widgets_init', array( $this, 'add_sidebars' ), 100 );
		add_action( 'widgets_init', array( $this, 'add_widgets' ), 100 );

		add_action( 'current_screen', array( $this, 'hide_cd' ) );

		add_filter( 'pre_update_option_sidebars_widgets', array( $this, 'preserve_sidebars' ), 10, 2 );
//		add_filter( 'sidebars_widgets', array( $this, 'preserve_sidebars' ) );

		add_action( 'wp_dashboard_setup', array( $this, '_get_widgets' ) );
		add_action( 'wp_dashboard_setup', array( $this, '_save_dashboard_widgets' ), 98 );
		add_action( 'wp_dashboard_setup', array( $this, '_remove_dash_widgets' ), 99 );
		add_action( 'wp_dashboard_setup', array( $this, '_add_dash_widgets' ), 100 );

		// Actions for the settings page only (or during AJAX when saving CD widgets)
		if ( $page == 'cd-dashboard' || isset( $_POST['cd_widget'] ) ) {

			require_once __DIR__ . '/class-cd-dashboard-settings.php';
			$this->settings = new CD_Dashboard_Settings();
		}
	}

	/**
	 * Adds Dashboard translations.
	 *
	 * @since {{VERSION}}
	 *
	 * @param array $translations The tranlslations.
	 *
	 * @return array The new translations.
	 */
	function _translations( $translations ) {

		$translations['one_widget_per_sidebar'] = __( 'Can only have one widget of this type per sidebar.', 'ClientDash' );

		return $translations;
	}

	/**
	 * Adds the Dashboard customization page to Client Dash.
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
	 * Replaces the default WP_Widget_Factory with a new one, to pass params to register_widget.
	 *
	 * @since {{VERSION}}
	 */
	function _cd_widget_factory_load() {

		require_once __DIR__ . '/class-cd-widget-factory.php';
		$GLOBALS['wp_widget_factory'] = CD_Widget_Factory::get_instance();
	}

	/**
	 * Add the CD sidebars.
	 *
	 * @since {{VERSION}}
	 * @access private
	 */
	function add_sidebars() {

		global $wp_registered_sidebars, $wp_roles;

		// Reset it (to prevent seeing the normal sidebars)

		$all_roles = $wp_roles->roles;

		/** This filter is documented in wp-admin/widgets.php */
		$editable_roles = apply_filters( 'editable_roles', $all_roles );

		// Make the default sidebars the available roles
		$sidebars = array();
		foreach ( $editable_roles as $ID => $role ) {
			$sidebars[] = array(
				'id'   => "cd_dashboard_$ID",
				'name' => $role['name'],
			);
		}

		/**
		 * Allows filtering of the sidebars that will be used for adding Dashboard widgets.
		 *
		 * @since {{VERSION}}
		 */
		$sidebars = apply_filters( 'cd_dashboard_widget_sidebars', $sidebars );

		foreach ( $sidebars as $sidebar ) {
			register_sidebar( $sidebar );
		}
	}

	/**
	 * Adds Dashboard widgets to Available Widgets.
	 *
	 * @since {{VERSION}}
	 * @access private
	 */
	function add_widgets() {

		/* @var CD_Widget_Factory $wp_widget_factory */
		global $wp_widget_factory;

		$dashboard_widgets = get_option( 'cd_dashboard_widgets' );

		/**
		 * Allows filtering of the available widgets for use on the Dashboard.
		 *
		 * @since {{VERSION}}
		 */
		$dashboard_widgets = apply_filters( 'cd_available_dashboard_widgets', $dashboard_widgets );

		if ( $dashboard_widgets ) {
			foreach ( $dashboard_widgets as $i => $widget ) {
				$dashboard_widgets[ $i ]['name'] = $widget['title'];
				cd_register_widget( $widget );
			}

			// Adds the new widgets to GLOBALS
			$wp_widget_factory->_register_widgets();
		}

		do_action( 'cd_add_dash_widgets' );
	}

	/**
	 * Hides CD widgets and sidebars when not on a CD page.
	 *
	 * @since {{VERSION}}
	 * @access private
	 */
	function hide_cd() {

		/** @var WP_Screen $current_screen */
		global $current_screen, $wp_registered_widgets, $wp_registered_sidebars;

		// Only necessary on widgets page
		if ( $current_screen->id !== 'widgets' ) {
			return;
		}

		// Hide CD widgets and sidebars when not on CD page
		if ( ! isset( $_GET['page'] ) || $_GET['page'] !== 'cd-dashboard' ) {

			$hide_widgets  = array();
			$hide_sidebars = array();

			foreach ( $wp_registered_widgets as $widget_ID => $widget ) {

				if ( isset( $widget['callback'][0] ) && $widget['callback'][0] instanceof CD_Widget ) {

					$hide_widgets[] = $widget_ID;
					unset( $wp_registered_widgets[ $widget_ID ] );
				}
			}

			foreach ( $wp_registered_sidebars as $sidebar_ID => $sidebar ) {

				// TODO Find more accurate method
				if ( strpos( $sidebar_ID, 'cd_' ) !== false ) {

					$hide_sidebars[] = $sidebar_ID;
					unset( $wp_registered_sidebars[ $sidebar_ID ] );
				}
			}

		} else {

			// Hide NON CD widgets and sidebars when on CD page
			$hide_widgets  = array();
			$hide_sidebars = array();

			foreach ( $wp_registered_widgets as $widget_ID => $widget ) {

				if ( isset( $widget['callback'][0] ) && $widget['callback'][0] instanceof CD_Widget ) {
					continue;
				}

				$hide_widgets[] = $widget_ID;
				unset( $wp_registered_widgets[ $widget_ID ] );
			}

			foreach ( $wp_registered_sidebars as $sidebar_ID => $sidebar ) {

				// TODO Find more accurate method
				if ( strpos( $sidebar_ID, 'cd_' ) === false ) {

					$hide_sidebars[] = $sidebar_ID;
					unset( $wp_registered_sidebars[ $sidebar_ID ] );
				}
			}
		}
	}

	/**
	 * Makes sure hidden sidebars don't get erased.
	 *
	 * When on the CD Dashboard page, default sidebars are hidden, and would be deleted upon save. Vice versa as well.
	 * This makes sure this doesn't happen.
	 *
	 * @since {{VERSION}}
	 * @access private
	 *
	 * @param array $sidebars_widgets
	 * @param array $old_sidebars_widgets
	 *
	 * @return array
	 */
	function preserve_sidebars( $sidebars_widgets, $old_sidebars_widgets ) {

		global $wp_registered_sidebars;

		if ( ! $old_sidebars_widgets ) {
			return $sidebars_widgets;
		}

		// Makes sure not to remove any registered sidebar, even if it's not present on the current page.
		$sidebars_widgets = array_intersect_key(
			array_merge( $sidebars_widgets, $old_sidebars_widgets ),
			array_merge( $wp_registered_sidebars, array( 'array_version' => 1 ) )
		);

		return $sidebars_widgets;
	}

	/**
	 * Retrieves the current dashboard widgets.
	 *
	 * @since {{VERSION}}
	 */
	function _get_widgets() {

		global $wp_registered_widgets, $wp_registered_sidebars;

		// Get the current role
		$user = wp_get_current_user();
		$role = $user->roles[0];

		/**
		 * Allows filtering of the sidebar to use on the Dashboard.
		 *
		 * @since {{VERSION}}
		 */
		$index = apply_filters( 'cd_load_dashboard_widgets', "cd_dashboard_$role" );

		$sidebars_widgets = wp_get_sidebars_widgets();

		// Bail if no widgets are set
		if ( empty( $sidebars_widgets[ $index ] ) ) {
			return;
		}

		$sidebar = $wp_registered_sidebars[ $index ];

		foreach ( $sidebars_widgets[ $index ] as $ID ) {

			if ( ! isset( $wp_registered_widgets[ $ID ] ) ) {
				continue;
			}

			$params = array_merge(
				array(
					array_merge(
						$sidebar,
						array(
							'widget_id'   => $ID,
							'widget_name' => $wp_registered_widgets[ $ID ]['name']
						)
					)
				),
				(array) $wp_registered_widgets[ $ID ]['params']
			);

			// Substitute HTML id and class attributes into before_widget
			$classname_ = '';
			foreach ( (array) $wp_registered_widgets[ $ID ]['classname'] as $cn ) {
				if ( is_string( $cn ) ) {
					$classname_ .= '_' . $cn;
				} elseif ( is_object( $cn ) ) {
					$classname_ .= '_' . get_class( $cn );
				}
			}
			$classname_                 = ltrim( $classname_, '_' );
			$params[0]['before_widget'] = sprintf( $params[0]['before_widget'], $ID, $classname_ );

			$callback = $wp_registered_widgets[ $ID ]['callback'];

			$widget = $wp_registered_widgets[ $ID ]['callback'][0];

			// Change to single if set
			if ( $this->cd_widgets[ $widget->id_base ]['single'] ) {
				$ID = $widget->id_base;
			}

			$this->dash_widgets[ $ID ] = array(
				'id_base'  => $widget->id_base,
				'callback' => $callback,
				'args'     => $params,
			);
		}
	}

	/**
	 * Removes all Dashboard widgets.
	 *
	 * @since {{VERSION}}
	 */
	function _remove_dash_widgets() {

		global $wp_meta_boxes;

		// Don't bother if none set
		if ( empty( $this->dash_widgets ) ) {
			return;
		}

		$wp_meta_boxes['dashboard'] = array();
	}

	/**
	 * Adds the new CD Dashboard widgets.
	 *
	 * @since {{VERSION}}
	 */
	function _add_dash_widgets() {

		// Don't bother if none set
		if ( empty( $this->dash_widgets ) ) {
			return;
		}

		foreach ( $this->dash_widgets as $widget ) {

			$widget['args'][0]['meta_box_id'] = $this->cd_widgets[ $widget['id_base'] ]['single'] ? $widget['id_base'] : $widget['args'][0]['widget_id'];
			call_user_func_array( $widget['callback'], $widget['args'] );
		}
	}

	/**
	 * The CD Dash Widget callback.
	 *
	 * I would just call the $widget['callback'], but then the arguments are not sent properly to the  widget callback
	 * itself. So this function is the middleman between the meta box callback, and the widget callback.
	 *
	 * @since {{VERSION}}
	 *
	 * @param string $object Supplied via meta box callback. Typically empty.
	 * @param array $box The meta box info.
	 */
	public function dash_widget_callback( $object, $box ) {

		$widget = $box['args'];
		call_user_func_array( $widget['callback'], $widget['args'] );
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

	/**
	 * Determines if CD Widgets and Sidebars should load on the current page or not.
	 *
	 * @since {{VERSION}}
	 *
	 * @return bool
	 */
	private function load_cd() {

		global $current_screen;

		if ( ( ! $current_screen && ! defined( 'DOING_AJAX' ) ) ||
		     ( $current_screen && $current_screen->id !== 'dashboard' &&
		       ( ! isset( $_GET['page'] ) || $_GET['page'] !== 'cd-dashboard' ) )
		) {
			return false;
		}

		return true;
	}
}