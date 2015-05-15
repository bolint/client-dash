<?php

class CD_WP_Widget_Factory extends WP_Widget_Factory {

	/**
	 * @var CD_WP_Widget_Factory
	 */
	private static $instance = null;

	/**
	 * Extend register($widget_class) with ability to pass parameters into widgets
	 *
	 * @param string     $widget_class Class of the new Widget
	 * @param array|null $widget       parameters to pass through to the widget
	 */
	function register( $widget_class, $widget = array() ) {

		$this->widgets[ $widget['id'] ] = new $widget_class( $widget );
	}


	/**
	 * Get (and instantiate, if necessary) the instance of the class
	 *
	 * @static
	 * @return Shared_Sidebars
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

	protected function __construct() {
		parent::__construct();
	}

}