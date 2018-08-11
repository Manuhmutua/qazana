<?php
namespace Qazana;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Qazana group control base.
 *
 * An abstract class for creating new group controls in the panel.
 *
 * @since 1.0.0
 * @abstract
 */
abstract class Group_Control_Base implements Group_Control_Interface {

	/**
	 * Arguments.
	 *
	 * Holds all the group control arguments.
	 *
	 * @access private
	 *
	 * @var array Group control arguments.
	 */
	private $args = [];

	/**
	 * Options.
	 *
	 * Holds all the group control options.
	 *
	 * Currently supports only the popover options.
	 *
	 * @access private
	 *
	 * @var array Group control options.
	 */
	private $options;

	/**
	 * Get options.
	 *
	 * Retrieve group control options. If options are not set, it will initialize default options.
	 *
	 * @since 1.9.0
	 * @access public
	 *
	 * @param array $option Optional. Single option.
	 *
	 * @return mixed Group control options. If option parameter was not specified, it will
	 *               return an array of all the options. If single option specified, it will
	 *               return the option value or `null` if option does not exists.
	 */
	final public function get_options( $option = null ) {
		if ( null === $this->options ) {
			$this->init_options();
		}

		if ( $option ) {
			if ( isset( $this->options[ $option ] ) ) {
				return $this->options[ $option ];
			}

			return null;
		}

		return $this->options;
	}

	/**
	 * Add new controls to stack.
	 *
	 * Register multiple controls to allow the user to set/update data.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @param Controls_Stack $element   The element stack.
	 * @param array          $user_args The control arguments defined by the user.
	 * @param array          $options   Optional. The element options. Default is
	 *                                  an empty array.
	 */
	final public function add_controls( Controls_Stack $element, array $user_args, array $options = [] ) {
		$this->init_args( $user_args );

		// Filter which controls to display
		$filtered_fields = $this->filter_fields();
		$filtered_fields = $this->prepare_fields( $filtered_fields );

		// For php < 7
		reset( $filtered_fields );

		if ( isset( $this->args['separator'] ) ) {
			$filtered_fields[ key( $filtered_fields ) ]['separator'] = $this->args['separator'];
		}

		$has_injection = false;

		if ( ! empty( $options['position'] ) ) {
			$has_injection = true;

			$element->start_injection( $options['position'] );

			unset( $options['position'] );
		}

		if ( $this->get_options( 'popover' ) ) {
			$this->start_popover( $element );
		}

		foreach ( $filtered_fields as $field_id => $field_args ) {
			// Add the global group args to the control
			$field_args = $this->add_group_args_to_field( $field_id, $field_args );

			// Register the control
			$id = $this->get_controls_prefix() . $field_id;

			if ( ! empty( $field_args['responsive'] ) ) {
				unset( $field_args['responsive'] );

				$element->add_responsive_control( $id, $field_args, $options );
			} else {
				$element->add_control( $id , $field_args, $options );
			}
		}

		if ( $this->get_options( 'popover' ) ) {
			$element->end_popover();
		}

		if ( $has_injection ) {
			$element->end_injection();
		}
	}

	final public function remove_controls( Controls_Stack $element ) {

		// Filter witch controls to display
		$fields = $this->get_fields();

		foreach ( $fields as $field_id => $field_args ) {

			// Register the control
			$id = $this->get_controls_prefix() . $field_id;

			$element->remove_control( $id );
		}
	}

	final public function get_args() {
		return $this->args;
	}

	/**
	 * Get fields.
	 *
	 * Retrieve group control fields.
	 *
	 * @since 1.2.2
	 * @access public
	 *
	 * @return array Control fields.
	 */
	final public function get_fields() {

        // TODO: Temp - compatibility for posts group
		if ( method_exists( $this, '_get_controls' ) ) {
			return $this->_get_controls( $this->get_args() );
		}

		if ( null === static::$fields ) {
			static::$fields = $this->init_fields();
		}

		return static::$fields;
	}

	/**
	 * Get controls prefix.
	 *
	 * Retrieve the prefix of the group control, which is `{{ControlName}}_`.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @return string Control prefix.
	 */
	public function get_controls_prefix() {
		return ! empty($this->args['name']) ? $this->args['name'] . '_' : '_';
	}

	/**
	 * Get group control classes.
	 *
	 * Retrieve the classes of the group control.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @return string Group control classes.
	 */
	public function get_base_group_classes() {
		return 'qazana-group-control-' . static::get_type() . ' qazana-group-control';
	}

	/**
	 * Init fields.
	 *
	 * Initialize group control fields.
	 *
	 * @abstract
	 * @since 1.2.2
	 * @access protected
	 */
	abstract protected function init_fields();

	/**
	 * Get default options.
	 *
	 * Retrieve the default options of the group control. Used to return the
	 * default options while initializing the group control.
	 *
	 * @since 1.2.2
	 * @access protected
	 *
	 * @return array Default group control options.
	 */
	protected function get_default_options() {
		return [];
	}

	/**
	 * Get child default arguments.
	 *
	 * Retrieve the default arguments for all the child controls for a specific group
	 * control.
	 *
	 * @since 1.2.2
	 * @access protected
	 *
	 * @return array Default arguments for all the child controls.
	 */
	protected function get_child_default_args() {
		return [];
	}

	/**
	 * Filter fields.
	 *
	 * Filter which controls to display, using `include`, `exclude` and the
	 * `condition` arguments.
	 *
	 * @since 1.2.2
	 * @access protected
	 *
	 * @return array Control fields.
	 */
	protected function filter_fields() {
		$args = $this->get_args();

		$fields = $this->get_fields();

		if ( ! empty( $args['include'] ) ) {
			$fields = array_intersect_key( $fields, array_flip( $args['include'] ) );
		}

		if ( ! empty( $args['exclude'] ) ) {
			$fields = array_diff_key( $fields, array_flip( $args['exclude'] ) );
		}

		return $fields;
	}

	/**
	 * Add group arguments to field.
	 *
	 * Register field arguments to group control.
	 *
	 * @since 1.2.2
	 * @access protected
	 *
	 * @param string $control_id Group control id.
	 * @param array  $field_args Group control field arguments.
	 *
	 * @return array
	 */
	protected function add_group_args_to_field( $control_id, $field_args ) {
		$args = $this->get_args();

		if ( ! empty( $args['tab'] ) ) {
			$field_args['tab'] = $args['tab'];
		}

		if ( ! empty( $args['section'] ) ) {
			$field_args['section'] = $args['section'];
		}

		$classes = $this->get_base_group_classes() . ' qazana-group-control-' . $control_id;

		$field_args['classes'] = ! empty( $field_args['classes'] ) ? $field_args['classes'] . ' ' . $classes : $classes;

		// add defaults
		if ( ! empty( $args['defaults'][$control_id] ) ) {
			$field_args['default'] = $args['defaults'][$control_id];
		}

		if ( ! empty( $args['condition'] ) ) {
			if ( empty( $field_args['condition'] ) ) {
				$field_args['condition'] = [];
			}

			$field_args['condition'] += $args['condition'];
		}

		return $field_args;
	}

	/**
	 * Prepare fields.
	 *
	 * Process group control fields before adding them to `add_control()`.
	 *
	 * @since 1.2.2
	 * @access protected
	 *
	 * @param array $fields Group control fields.
	 *
	 * @return array Processed fields.
	 */
	protected function prepare_fields( $fields ) {
		foreach ( $fields as $field_key => &$field ) {
			if ( ! empty( $field['condition'] ) ) {
				$field = $this->add_conditions_prefix( $field );
			}

			if ( ! empty( $field['selectors'] ) ) {
				$field['selectors'] = $this->handle_selectors( $field['selectors'] );
			}

			if ( isset( $this->args['fields_options']['__all'] ) ) {
				$field = array_merge( $field, $this->args['fields_options']['__all'] );
			}

			if ( isset( $this->args['fields_options'][ $field_key ] ) ) {
				$field = array_merge( $field, $this->args['fields_options'][ $field_key ] );
			}
		}

		return $fields;
	}

	/**
	 * Init options.
	 *
	 * Initializing group control options.
	 *
	 * @since 1.9.0
	 * @access private
	 */
	private function init_options() {
		$default_options = [
			'popover' => [
				'starter_name' => 'popover_toggle',
				'starter_value' => 'custom',
				'starter_title' => '',
			],
		];

		$this->options = array_replace_recursive( $default_options, $this->get_default_options() );
	}

	/**
	 * Init arguments.
	 *
	 * Initializing group control base class.
	 *
	 * @since 1.2.2
	 * @access private
	 *
	 * @param array $args Group control settings value.
	 */
	private function init_args( $args ) {
		$this->args = array_merge( $this->get_default_args(), $this->get_child_default_args(), $args );
	}

	/**
	 * Get default arguments.
	 *
	 * Retrieve the default arguments of the group control. Used to return the
	 * default arguments while initializing the group control.
	 *
	 * @since 1.2.2
	 * @access private
	 *
	 * @return array Control default arguments.
	 */
	private function get_default_args() {
		return [
			'default' => '',
			'selector' => '{{WRAPPER}}',
			'fields_options' => [],
		];
	}

	/**
	 * Add condition prefix.
	 *
	 * Used to add the group prefix to controls with conditions, to
	 * distinguish them from other controls with the same name.
	 *
	 * This way Qazana can apply condition logic to a specific control in a
	 * group control.
	 *
	 * @since 1.2.0
	 * @access private
	 *
	 * @param array $field Group control field.
	 *
	 * @return array Group control field.
	 */
	private function add_conditions_prefix( $field ) {
		$controls_prefix = $this->get_controls_prefix();

		$prefixed_condition_keys = array_map(
			function( $key ) use ( $controls_prefix ) {
				return $controls_prefix . $key;
			},
			array_keys( $field['condition'] )
		);

		$field['condition'] = array_combine(
			$prefixed_condition_keys,
			$field['condition']
		);

		return $field;
	}

	/**
	 * Handle selectors.
	 *
	 * Used to process the CSS selector of group control fields. When using
	 * group control, Qazana needs to apply the selector to different fields.
	 * This method handles the process.
	 *
	 * In addition, it handles selector values from other fields and process the
	 * css.
	 *
	 * @since 1.2.2
	 * @access private
	 *
	 * @param array $selectors An array of selectors to process.
	 *
	 * @return array Processed selectors.
	 */
	private function handle_selectors( $selectors ) {
		$args = $this->get_args();

		$selectors = array_combine(
			array_map(
				function( $key ) use ( $args ) {
						return str_replace( '{{SELECTOR}}', $args['selector'], $key );
				}, array_keys( $selectors )
			),
			$selectors
		);

		if ( ! $selectors ) {
			return $selectors;
		}

		$controls_prefix = $this->get_controls_prefix();

		foreach ( $selectors as &$selector ) {
			$selector = preg_replace_callback(
				'/(?:\{\{)\K[^.}]+(?=\.[^}]*}})/', function( $matches ) use ( $controls_prefix ) {
					return $controls_prefix . $matches[0];
				}, $selector
			);
		}

		return $selectors;
	}

	/**
	 * Start popover.
	 *
	 * Starts a group controls popover.
	 *
	 * @since 1.9.1
	 * @access private
	 * @param Controls_Stack $element Element.
	 */
	private function start_popover( Controls_Stack $element ) {
		$popover_options = $this->get_options( 'popover' );

		$settings = $this->get_args();

		if ( ! empty( $settings['label'] ) ) {
			$label = $settings['label'];
		} else {
			$label = $popover_options['starter_title'];
		}

		$control_params = [
			'type' => Controls_Manager::POPOVER_TOGGLE,
			'label' => $label,
			'return_value' => $popover_options['starter_value'],
		];

		if ( ! empty( $settings['condition'] ) ) {
			$control_params['condition'] = $settings['condition'];
		}

		$element->add_control( $this->get_controls_prefix() . $popover_options['starter_name'], $control_params );

		$element->start_popover();
	}
}
