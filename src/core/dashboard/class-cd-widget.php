<?php
/**
 * The CD Widget object.
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
 * Class CD_Widget
 *
 * The CD Widget object.
 *
 * @since      {{VERSION}}
 *
 * @package    ClientDash
 * @subpackage ClientDash/core/dashboard
 */
class CD_Widget extends WP_Widget {

	/**
	 * The callback for displaying the widget output.
	 *
	 * @since {{VERSION}}
	 *
	 * @var array|string
	 */
	public $dashboard_callback;

	/**
	 * The callback for displaying the widget form output.
	 *
	 * @since {{VERSION}}
	 *
	 * @var array|string
	 */
	public $form_callback;

	/**
	 * Whether the widget is a single existence widget or not.
	 *
	 * @since {{VERSION}}
	 *
	 * @var bool
	 */
	public $single;

	/**
	 * Whether the widget is added by CD or not.
	 *
	 * @since {{VERSION}}
	 *
	 * @var bool
	 */
	public $cd_widget;

	/**
	 * Constructs the widget.
	 *
	 * Unlike WP_Widget, this class accepts properties upon construction. This is what enables multiple widgets to be
	 * built from one single class.
	 *
	 * @param string $widget The widget properties.
	 */
	function __construct( $widget ) {

		parent::__construct(
			$widget['id'],
			$widget['title'],
			array( 'description' => isset( $widget['description'] ) ? $widget['description'] : '' )
		);

		$this->dashboard_callback = $widget['callback'] !== null ? $widget['callback'] : array(
			$this,
			'default_widget_output'
		);

		$this->form_callback = $widget['form_callback'] !== null ? $widget['form_callback'] : array(
			$this,
			'default_form_output'
		);

		$this->single    = $widget['single'] !== null ? $widget['single'] : true;
		$this->cd_widget = $widget['cd_widget'] !== null ? $widget['cd_widget'] : false;
	}

	/**
	 * Displays the widget form.
	 *
	 * @since {{VERSION}}
	 *
	 * @param array $instance The current widget instance.
	 *
	 * @return void
	 */
	function form( $instance ) {
		?>
		<input type="hidden" name="cd_widget" value="1"/>
		<?php

		if ( $this->single ) {
			?>
			<input type="hidden" name="cd_single" value="1"/>
		<?php
		}

		if ( is_callable( $this->form_callback ) ) {
			call_user_func( $this->form_callback, $instance, $this );
		}
	}

	/**
	 * The widget output.
	 *
	 * @since {{VERSION}}
	 *
	 * @param array $args     The widget type args.
	 * @param array $instance The current widget instance.
	 */
	function widget( $args, $instance ) {

		$name = isset( $instance['title'] ) && $instance['title'] ? esc_attr( $instance['title'] ) : $this->name;

		wp_add_dashboard_widget(
			$args['meta_box_id'],
			$name,
			array( $this, '_widget_output' ),
			null,
			array(
				'args'     => $args,
				'instance' => $instance
			)
		);
	}

	/**
	 * Calls the widget output method.
	 *
	 * @since {{VERSION}}
	 *
	 * @param mixed $object I dk, it's supplied via do_meta_boxes(). Ignore it.
	 * @param array $box    The meta box settings.
	 */
	function _widget_output( $object, $box ) {

		if ( is_callable( $this->dashboard_callback ) ) {

			// Send widgets if a custom CD widget, otherwise, don't send widgets (core widgets and plugin / theme
			// widgets)
			if ( $this->cd_widget ) {
				call_user_func( $this->dashboard_callback, $box['args']['args'], $box['args']['instance'] );
			} else {
				call_user_func( $this->dashboard_callback );
			}
		}
	}

	/**
	 * The default widget form output.
	 *
	 * @since {{VERSION}}
	 *
	 * @param array $instance The current widget instance.
	 */
	public function default_form_output( $instance ) {

		$this->form_output( array(
			'title' => array(
				'type'  => 'textbox',
				'label' => __( 'Title:', 'ClientDash' ),
			),
		), $instance );
	}

	/**
	 * An easy way to output the forms fields.
	 *
	 * @since {{VERSION}}
	 *
	 * @param array $fields   The fields to display.
	 * @param array $instance The current widget instance.
	 */
	public function form_output( $fields, $instance ) {

		foreach ( $fields as $name => $field ) {

			if ( is_callable( array( $this, 'field_' . $field['type'] ) ) ) {
				call_user_func(
					array( $this, 'field_' . $field['type'] ),
					$name,
					isset( $field['label'] ) ? $field['label'] : '',
					$instance,
					isset( $field['value'] ) ? $field['value'] : ''
				);
			}
		}
	}

	/**
	 * The default widget output (empty).
	 *
	 * @since {{VERSION}}
	 *
	 * @param array $args     The widget type args.
	 * @param array $instance The current widget instance.
	 */
	public function default_widget_output( $args, $instance ) { }

	/**
	 * Outputs a text input field.
	 *
	 * @since {{VERSION}}
	 *
	 * @param string $name     The name of the input field.
	 * @param string $label    The label for the input field.
	 * @param array  $instance The current widget instance settings.
	 * @param string $value    The value for the input field.
	 */
	public function field_textbox( $name, $label, $instance, $value = '' ) {
		?>
		<p>
			<label>
				<?php echo esc_attr( $label ); ?>
				<input type="text" name="<?php echo $this->get_field_name( $name ); ?>" class="widefat"
				       id="<?php echo $this->get_field_id( $name ); ?>"
				       value="<?php echo isset( $instance[ $name ] ) ? esc_attr( $instance[ $name ] ) : $value; ?>"/>
			</label>
		</p>
	<?php
	}

	/**
	 * Outputs a text area field.
	 *
	 * @since {{VERSION}}
	 *
	 * @param string $name     The name of the input field.
	 * @param string $label    The label for the input field.
	 * @param array  $instance The current widget instance settings.
	 * @param string $value    The value for the input field.
	 */
	public function field_textarea( $name, $label, $instance, $value = '' ) {
		?>
		<p>
			<label>
				<?php echo ! empty( $label ) ? esc_attr( $label ) : ''; ?>
				<textarea name="<?php echo $this->get_field_name( $name ); ?>" class="widefat" rows="10"
				          id="<?php echo $this->get_field_id( $name ); ?>"
					><?php echo isset( $instance[ $name ] ) ? esc_attr( $instance[ $name ] ) : $value; ?></textarea>
			</label>
		</p>
	<?php
	}

	/**
	 * Outputs a checkbox input field.
	 *
	 * @since {{VERSION}}
	 *
	 * @param string $name     The name of the input field.
	 * @param string $label    The label for the input field.
	 * @param array  $instance The current widget instance settings.
	 * @param string $value    The value for the input field.
	 */
	public function field_checkbox( $name, $label, $instance, $value = '1' ) {
		?>
		<p>
			<label>
				<input type="checkbox" name="<?php echo $this->get_field_name( $name ); ?>"
				       id="<?php echo $this->get_field_id( $name ); ?>"
				       value="<?php echo $value; ?>"
					<?php checked( isset( $instance[ $name ] ) ? $instance[ $name ] : false, $value ); ?>/>
				<?php echo esc_attr( $label ); ?>
			</label>
		</p>
	<?php
	}
}