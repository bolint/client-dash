<?php
/**
 * Overrides the default widget factory in order to allow passing parameters to widget instances.
 *
 * @since {{VERSION}}
 *
 * @package ClientDash
 * @subpackage ClientDash/core/dashboard
 */

defined( 'ABSPATH' ) || die();

/**
 * Class CD_Widget_Factory
 *
 * Overrides the default widget factory in order to allow passing parameters to widget instances.
 *
 * @since {{VERSION}}
 */
class CD_Widget_Factory extends WP_Widget_Factory {

	/**
	 * @var CD_Widget_Factory
	 */
	private static $instance = null;

	/**
	 * Extend register($widget_class) with ability to pass parameters into widgets
	 *
	 * @param string     $widget_class Class of the new Widget
	 * @param array|bool $widget       parameters to pass through to the widget
	 */
	function register( $widget_class, $widget = false ) {

		if ( $widget ) {
			$this->widgets[ $widget['id'] ] = new $widget_class( $widget );
		} else {
			$this->widgets[$widget_class] = new $widget_class();
		}
	}


	/**
	 * Get (and instantiate, if necessary) the instance of the class
	 *
	 * @static
	 * @return CD_Widget_Factory
	 */
	public static function get_instance() {
		if ( ! is_a( self::$instance, __CLASS__ ) ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	final public function __clone() {
		trigger_error( "No cloning allowed!", E_USER_ERROR );
	}

	final public function __sleep() {
		trigger_error( "No serialization allowed!", E_USER_ERROR );
	}

	function __construct() {
		parent::__construct();
	}

}