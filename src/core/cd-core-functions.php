<?php
/**
* Client Dash core functions.
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
 * Loads the specified template file.
 *
 * @since {{VERSION}}
 *
 * @param $template array|string The template to load.
 */
function cd_load_template( $template ) {

	if ( is_array( $template ) ) {
		$template = implode( '/', $template );
	}

	$template .= '.php';

	/** @noinspection PhpIncludeInspection */
	include __DIR__ . "/templates/$template";
}