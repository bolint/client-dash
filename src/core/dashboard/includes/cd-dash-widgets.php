<?php
/**
 * Creates the default CD Dashboard widgets.
 *
 * @since      {{VERSION}}
 *
 * @package    ClientDash
 * @subpackage ClientDash/core/dashboard/includes
 */

if ( ! defined( 'ABSPATH' ) ) {
	die;
}

add_action( 'cd_add_dash_widgets', '_cd_add_primary_dash_widgets' );

/**
 * Adds in the default CD Dash widgets.
 *
 * @since {{VERSION}}
 */
function _cd_add_primary_dash_widgets() {

	// Text
	cd_register_widget( array(
		'id'       => 'cd_text',
		'title'    => __( 'Text', 'ClientDash' ),
		'description'    => __( 'Arbitrary text or HTML' ),
		'callback' => 'cd_dash_widget_text_output',
		'form_callback' => 'cd_dash_widget_text_form',
		'cd_widget' => true,
		'single'   => false,
	));
}

/**
 * The text widget form output.
 *
 * @since {{VERSION}}
 *
 * @param array $instance The current widget instance.
 * @param CD_Widget $widget The current widget object.
 */
function cd_dash_widget_text_form( $instance, $widget ) {

	$widget->form_output( array(
		'title' => array(
			'type' => 'textbox',
			'label' => __( 'Title:', 'ClientDash' ),
		),
		'text' => array(
			'type' => 'textarea',
		),
		'paragraphs' => array(
			'type' => 'checkbox',
			'value' => '1',
			'label' => __( 'Automatically add paragraphs', 'ClientDash' ),
		),
	), $instance );
}

/**
 * The text widget output.
 *
 * @since {{VERSION}}
 *
 * @param array $args The widget type args.
 * @param array $instance The current widget instance.
 */
function cd_dash_widget_text_output( $args, $instance ) {

	$text = isset( $instance['text'] ) ? $instance['text'] : '';
	$text = isset( $instance['paragraphs'] ) && $instance['paragraphs'] == '1' ? wpautop( $text ) : $text;

	echo $text;
}