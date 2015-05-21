<?php
/**
 * Dashboard functions.
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
 * Registers a CD Dashboard Widget.
 *
 * @since {{VERSION}}
 *
 * @param array $widget The widget to register.
 */
function cd_register_widget( $widget ) {

	/**
	 * Allows filtering of the widget default settings.
	 *
	 * @since {{VERSION}}
	 */
	$defaults = apply_filters( 'cd_dash_widget_defaults', array(
		'id'            => '',
		'title'         => '',
		'description'   => '',
		'callback'      => null,
		'form_callback' => null,
		'args'          => array(),
		'position'      => 'normal',
		'priority'      => 'core',
		'single'        => true,
		'cd_widget'     => false,
	) );

	$widget = wp_parse_args( $widget, $defaults );

	require_once __DIR__ . '/class-cd-widget.php';

	/** @var WP_Widget_Factory $wp_widget_factory */
	global $wp_widget_factory;

	$wp_widget_factory->register( 'CD_Widget', $widget );

	// Add to CD object
	$CD                                         = ClientDash::getInstance();
	$CD->dashboard->cd_widgets[ $widget['id'] ] = $widget;
}

