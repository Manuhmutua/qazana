<?php
namespace Qazana;

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly.

class Controls_Manager {

	const TAB_CONTENT = 'content';
	const TAB_STYLE = 'style';
	const TAB_ADVANCED = 'advanced';
	const TAB_RESPONSIVE = 'responsive';
	const TAB_LAYOUT = 'layout';
	const TAB_SETTINGS = 'settings';

	const TEXT = 'text';
	const NUMBER = 'number';
	const TEXTAREA = 'textarea';
	const SELECT = 'select';
	const CHECKBOX = 'checkbox';
	const RADIO = 'radio';
	const SWITCHER = 'switcher';

	const HIDDEN = 'hidden';
	const HEADING = 'heading';
	const RAW_HTML = 'raw_html';
	const SECTION = 'section';
	const TAB = 'tab';
	const TABS = 'tabs';
	const DIVIDER = 'divider';

	const COLOR = 'color';
	const MEDIA = 'media';
	const SLIDER = 'slider';
	const DIMENSIONS = 'dimensions';
	const CHOOSE = 'choose';
	const WYSIWYG = 'wysiwyg';
	const CODE = 'code';
	const FONT = 'font';
	const IMAGE_DIMENSIONS = 'image_dimensions';

	const WP_WIDGET = 'wp_widget';

	const URL = 'url';
	const REPEATER = 'repeater';
	const ICON = 'icon';
	const GALLERY = 'gallery';
	const STRUCTURE = 'structure';
	const SELECT2 = 'select2';
	const DATE_TIME = 'date_time';
	const BOX_SHADOW = 'box_shadow';
	const TEXT_SHADOW = 'text_shadow';
	const ANIMATION_IN = 'animation_in';
	const ANIMATION_OUT = 'animation_out';
	const ORDER = 'order';

	/**
	 * @var Base_Control[]
	 */
	private $controls = null;

	/**
	 * @var Group_Control_Base[]
	 */
	private $control_groups = [];

	private $stacks = [];

	private static $tabs;

	private static function init_tabs() {
		self::$tabs = [
			self::TAB_CONTENT => __( 'Content', 'qazana' ),
			self::TAB_STYLE => __( 'Style', 'qazana' ),
			self::TAB_ADVANCED => __( 'Advanced', 'qazana' ),
			self::TAB_RESPONSIVE => __( 'Responsive', 'qazana' ),
			self::TAB_LAYOUT => __( 'Layout', 'qazana' ),
			self::TAB_SETTINGS => __( 'Settings', 'qazana' ),
		];

		self::$tabs = Utils::apply_filters_deprecated( 'qazana/controls/get_available_tabs_controls', [ self::$tabs ], '1.0.0', '`' . __CLASS__ . '::add_tab( $tab_name, $tab_title )`' );
	}

	public static function get_tabs() {
		if ( ! self::$tabs ) {
			self::init_tabs();
		}

		return self::$tabs;
	}

	public static function add_tab( $tab_name, $tab_title ) {
		if ( ! self::$tabs ) {
			self::init_tabs();
		}

		if ( isset( self::$tabs[ $tab_name ] ) ) {
			return;
		}

		self::$tabs[ $tab_name ] = $tab_title;
	}

	public function include_controls() {
		
		require( qazana()->includes_dir  . 'editor/controls/base.php' );
		require( qazana()->includes_dir  . 'editor/controls/base-data.php' );
		require( qazana()->includes_dir  . 'editor/controls/base-ui.php' );
		require( qazana()->includes_dir  . 'editor/controls/base-multiple.php' );
		require( qazana()->includes_dir  . 'editor/controls/base-units.php' );

		// Group Controls
		require( qazana()->includes_dir  . 'editor/interfaces/group-control.php' );
		require( qazana()->includes_dir  . 'editor/controls/groups/base.php' );

		require( qazana()->includes_dir  . 'editor/controls/groups/background.php' );
		require( qazana()->includes_dir  . 'editor/controls/groups/border.php' );
		require( qazana()->includes_dir  . 'editor/controls/groups/typography.php' );
		require( qazana()->includes_dir  . 'editor/controls/groups/image-size.php' );
		require( qazana()->includes_dir  . 'editor/controls/groups/box-shadow.php' );
		require( qazana()->includes_dir  . 'editor/controls/groups/animations.php' );
		require( qazana()->includes_dir  . 'editor/controls/groups/hover-animations.php' );
		require( qazana()->includes_dir  . 'editor/controls/groups/icon.php' );
		require( qazana()->includes_dir  . 'editor/controls/groups/text-shadow.php' );

	}

	/**
	 * @since 1.0.0
	 */
	public function register_controls() {

		$this->controls = [];

		$available_controls = [
			self::TEXT,
			self::NUMBER,
			self::TEXTAREA,
			self::SELECT,
			self::CHECKBOX,
			self::RADIO,
			self::SWITCHER,

			self::HIDDEN,
			self::HEADING,
			self::RAW_HTML,
			self::SECTION,
			self::TAB,
			self::TABS,
			self::DIVIDER,

			self::COLOR,
			self::MEDIA,
			self::SLIDER,
			self::DIMENSIONS,
			self::CHOOSE,
			self::WYSIWYG,
			self::CODE,
			self::FONT,
			self::IMAGE_DIMENSIONS,

			self::WP_WIDGET,

			self::URL,
			self::REPEATER,
			self::ICON,
			self::GALLERY,
			self::STRUCTURE,
			self::SELECT2,
			self::DATE_TIME,
			self::BOX_SHADOW,
			self::TEXT_SHADOW,
			self::ANIMATION_IN,
			self::ANIMATION_OUT,
			self::ORDER,
		];
		
		foreach ( $available_controls as $control_id ) {
			
			$control_filename = str_replace( '_', '-', $control_id );
			$control_filename =  qazana()->includes_dir . "editor/controls/{$control_filename}.php";
			require( $control_filename );

			$class_name = __NAMESPACE__ . '\Control_' . ucwords( $control_id );

			$this->register_control( $control_id, new $class_name() );
		}

		// Group Controls
		$this->control_groups['background'] 		= new Group_Control_Background();
		$this->control_groups['border']     		= new Group_Control_Border();
		$this->control_groups['typography'] 		= new Group_Control_Typography();
		$this->control_groups['image-size'] 		= new Group_Control_Image_Size();
		$this->control_groups['box-shadow'] 		= new Group_Control_Box_Shadow();
		$this->control_groups['text-shadow'] 		= new Group_Control_Text_Shadow();
		$this->control_groups['animations'] 		= new Group_Control_Animations();
		$this->control_groups['hover-animations'] 	= new Group_Control_Hover_Animations();
		$this->control_groups['icon'] 		 		= new Group_Control_Icon();

		do_action( 'qazana/controls/controls_registered', $this );
	}

	/**
	 * @since 1.0.0
	 *
	 * @param $control_id
	 * @param Base_Control $control_instance
	 */
	public function register_control( $control_id, Base_Control $control_instance ) {
		$this->controls[ $control_id ] = $control_instance;
	}

	/**
	 * @param $control_id
	 *
	 * @since 1.0.0
	 * @return bool
	 */
	public function unregister_control( $control_id ) {
		if ( ! isset( $this->controls[ $control_id ] ) ) {
			return false;
		}

		unset( $this->controls[ $control_id ] );

		return true;
	}

	/**
	 * @since 1.0.0
	 * @return Base_Control[]
	 */
	public function get_controls() {
		if ( null === $this->controls ) {
			$this->register_controls();
		}

		return $this->controls;
	}

	/**
	 * @since 1.0.0
	 * @param $control_id
	 *
	 * @return bool|\Qazana\Base_Control
	 */
	public function get_control( $control_id ) {
		$controls = $this->get_controls();

		return isset( $controls[ $control_id ] ) ? $controls[ $control_id ] : false;
	}

	/**
	 * @since 1.0.0
	 * @return array
	 */
	public function get_controls_data() {
		$controls_data = [];

		foreach ( $this->get_controls() as $name => $control ) {
			$controls_data[ $name ] = $control->get_settings();

			if ( $control instanceof Base_Data_Control ) {
				$controls_data[ $name ]['default_value'] = $control->get_default_value();
			}
		}

		return $controls_data;
	}

	/**
	 * @since 1.0.0
	 * @return void
	 */
	public function render_controls() {
		foreach ( $this->get_controls() as $control ) {
			$control->print_template();
		}
	}

	/**
	 * @since 1.0.0
	 *
	 * @param string $id
	 *
	 * @return Group_Control_Base|Group_Control_Base[]
	 */
	public function get_control_groups( $id = null ) {
		if ( $id ) {
			return isset( $this->control_groups[ $id ] ) ? $this->control_groups[ $id ] : null;
		}

		return $this->control_groups;
	}

	/**
	 * @since 1.0.0
	 *
	 * @param $id
	 * @param $instance
	 *
	 * @return Group_Control_Base[]
	 */
	public function add_group_control( $id, $instance ) {
		$this->control_groups[ $id ] = $instance;

		return $instance;
	}

	/**
	 * @since 1.0.0
	 * @return void
	 */
	public function enqueue_control_scripts() {
		foreach ( $this->get_controls() as $control ) {
			$control->enqueue();
		}
	}

	public function open_stack( Controls_Stack $element ) {
		$stack_id = $element->get_unique_name();

		$this->stacks[ $stack_id ] = [
			'tabs' => [],
			'controls' => [],
		];
	}

	public function add_control_to_stack( Controls_Stack $element, $control_id, $control_data, $options = [] ) {
		
		$stack_id = $element->get_unique_name();
		
		if ( ! is_array( $options ) ) {
			_deprecated_argument( __FUNCTION__, '1.7.0', 'Use `[ \'overwrite\' => ' . var_export( $options, true ) . ' ]` instead. Added in ' . $control_id . ' in the ' . $stack_id . ' element.' );

			$options = [
				'overwrite' => $options,
			];
		}

		$default_options = [
			'overwrite' => false,
			'index' => null,
		];

		$options = array_merge( $default_options, $options );

		$default_args = [
			'type' => self::TEXT,
			'tab' => self::TAB_CONTENT,
		];

		$control_data['name'] = $control_id;

		$control_data = array_merge( $default_args, $control_data );

		$control_type_instance = $this->get_control( $control_data['type'] );

		if ( ! $control_type_instance ) {
			_doing_it_wrong( __CLASS__ . '::' . __FUNCTION__, 'Control type `' . $control_data['type'] . '` not found`. Added in `' . $stack_id . '` element.', '1.0.0' );
			return false;
		}

		if ( $control_type_instance instanceof Base_Data_Control ) {
			$control_default_value = $control_type_instance->get_default_value();

			if ( is_array( $control_default_value ) ) {
				$control_data['default'] = isset( $control_data['default'] ) ? array_merge( $control_default_value, $control_data['default'] ) : $control_default_value;
			} else {
				$control_data['default'] = isset( $control_data['default'] ) ? $control_data['default'] : $control_default_value;
			}
		}

		if ( ! $options['overwrite'] && isset( $this->stacks[ $stack_id ]['controls'][ $control_id ] ) ) {
			_doing_it_wrong( __CLASS__ . '::' . __FUNCTION__, 'Cannot redeclare control with same name. - ' . $control_id . ' in the ' . $stack_id . ' element', '1.0.0' );

			return false;
		}

		$tabs = self::get_tabs();

		if ( ! isset( $tabs[ $control_data['tab'] ] ) ) {
			$control_data['tab'] = $default_args['tab'];
		}

		$this->stacks[ $stack_id ]['tabs'][ $control_data['tab'] ] = $tabs[ $control_data['tab'] ];

		$this->stacks[ $stack_id ]['controls'][ $control_id ] = $control_data;

		if ( null !== $options['index'] ) {
			$controls = $this->stacks[ $stack_id ]['controls'];

			$controls_keys = array_keys( $controls );

			array_splice( $controls_keys, $options['index'], 0, $control_id );

			$this->stacks[ $stack_id ]['controls'] = array_merge( array_flip( $controls_keys ), $controls );
		}

		return true;
	}

	public function remove_control_from_stack( $stack_id, $control_id ) {
		if ( is_array( $control_id ) ) {
			foreach ( $control_id as $id ) {
				$this->remove_control_from_stack( $stack_id, $id );
			}

			return true;
		}

		if ( empty( $this->stacks[ $stack_id ]['controls'][ $control_id ] ) ) {
			return new \WP_Error( 'Cannot remove non-existent control.' );
		}

		unset( $this->stacks[ $stack_id ]['controls'][ $control_id ] );

		return true;
	}

	/**
	 * @param string $stack_id
	 * @param string $control_id
	 *
	 * @return array|\WP_Error
	 */
	public function get_control_from_stack( $stack_id, $control_id ) {
		if ( empty( $this->stacks[ $stack_id ]['controls'][ $control_id ] ) ) {
			return new \WP_Error( 'Cannot get a non-existent control.' );
		}

		return $this->stacks[ $stack_id ]['controls'][ $control_id ];
	}

	public function update_control_in_stack( Controls_Stack $element, $control_id, $control_data ) {
		$old_control_data = $this->get_control_from_stack( $element->get_unique_name(), $control_id );
		if ( is_wp_error( $old_control_data ) ) {
			return false;
		}

		$control_data = array_merge( $old_control_data, $control_data );

		return $this->add_control_to_stack( $element, $control_id, $control_data, [ 'overwrite' => true ] );
	}

	public function get_stacks( $stack_id = null ) {
		if ( $stack_id ) {
			if ( isset( $this->stacks[ $stack_id ] ) ) {
				return $this->stacks[ $stack_id ];
			}

			return null;
		}

		return $this->stacks;
	}

	public function get_element_stack( Controls_Stack $controls_stack ) {
		$stack_id = $controls_stack->get_unique_name();

		if ( ! isset( $this->stacks[ $stack_id ] ) ) {
			return null;
		}

		$stack = $this->stacks[ $stack_id ];

		if ( 'widget' === $controls_stack->get_type() && 'common' !== $stack_id ) {
			$common_widget = qazana()->widgets_manager->get_widget_types( 'common' );

			$stack['controls'] = array_merge( $stack['controls'], $common_widget->get_controls() );

			$stack['tabs'] = array_merge( $stack['tabs'], $common_widget->get_tabs_controls() );
		}

		return $stack;
	}

	/**
	 * Controls_Manager constructor.
	 *
	 * @since 1.0.0
	 */
	public function __construct() {		
		$this->include_controls();
	}
}
