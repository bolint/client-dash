<?php
/**
 * Sets Client Dash colors based on the current admin theme.
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
 * Class CD_ColorScheme
 *
 * Sets Client Dash colors based on the current admin theme.
 *
 * @since {{VERSION}}
 *
 * @package ClientDash
 * @subpackage ClientDash/core
 */
class CD_ColorScheme {

	/**
	 * The current admin color scheme.
	 *
	 * @since {{VERSION}}
	 *
	 * @var string
	 */
	private $color_schemes;

	/**
	 * Constructs the class.
	 *
	 * Here we will include ALL necessary files as well as perform ALL
	 * necessary actions and filters.
	 *
	 * @since Client Dash 1.5
	 */
	public function __construct() {

		$this->add_actions();
	}

	/**
	 * Adds globally necessary actions.
	 *
	 * @since {{VERSION}}
	 */
	private function add_actions() {

		// Color scheme
		add_action( 'admin_init', array( $this, '_save_color_scheme' ) );
		add_action( 'admin_head', array( $this, '_set_color_schemes' ) );
	}

	function _save_color_scheme() {

		global $_wp_admin_css_colors;
		$this->color_schemes = $_wp_admin_css_colors;
	}

	/**
	 * Sets the Client Dash color scheme to match the current admin color scheme.
	 *
	 * @since {{VERSION}}
	 * @access private
	 */
	function _set_color_schemes() {

		$active_theme = $this->get_color_scheme();

		// Build style array
		$styles = array(
			'.cd-icon'                                   => array(
				'color' => $active_theme['primary'],
			),
			'.cd-icon:hover'                             => array(
				'color' => $active_theme['secondary'],
			),
			'.cd-dashicons-grid-item.active .container'  => array(
				'background-color' => $active_theme['tertiary'],
				'color'            => '#eee',
			),
			'#cd-dashicons-selections .dashicons.active' => array(
				'color' => $active_theme['secondary'],
			),
			'.cd-progress-bar .cd-progress-bar-inner'    => array(
				'background-color' => $active_theme['secondary'],
			),
			'.cd-menu-icon-selector li:hover .dashicons' => array(
				'color' => $active_theme['secondary'] . '!important',
			),
			'.cd-menu-icon-selector .active .dashicons'  => array(
				'color' => $active_theme['secondary'] . '!important',
			),
		);

		// Build our styles
		if ( ! empty( $styles ) ) {
			echo '<!-- Client Dash Colors -->';
			echo '<style>';
			foreach ( $styles as $selector => $properties ) {
				echo $selector . '{';
				foreach ( $properties as $property => $value ) {
					echo "$property: $value;";
				}
				echo '}';
			}
			echo '</style>';
		}
	}

	/**
	 * Gets the current color scheme.
	 *
	 * @since {{VERSION}}
	 *
	 * @return array Current color scheme.
	 */
	private function get_color_scheme() {

		$current_color = get_user_meta( get_current_user_id(), 'admin_color', true );
		$colors        = $this->color_schemes[ $current_color ];

		return array(
			'primary'      => $colors->colors[1],
			'primary-dark' => $colors->colors[0],
			'secondary'    => $colors->colors[2],
			'tertiary'     => $colors->colors[3]
		);
	}
}