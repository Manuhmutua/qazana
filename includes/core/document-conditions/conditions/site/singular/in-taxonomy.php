<?php
namespace Qazana\DocumentConditions\Conditions;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class In_Taxonomy extends Base {

	public static function get_type() {
		return 'singular';
	}
	/**
	 * @var \WP_Taxonomy
	 */
	private $taxonomy;

	public function __construct( $data ) {
		parent::__construct( $data );

		$this->taxonomy = $data['object'];
	}

	public function get_name() {
		return 'in_' . $this->taxonomy->name;
	}

	public function get_title() {
		/* translators: %s: Taxonomy label. */
		return sprintf( __( 'In %s', 'qazana' ), $this->taxonomy->labels->singular_name );
	}

	public function check( $args ) {
		return is_singular() && has_term( (int) $args['id'], $this->taxonomy->name );
	}

	protected function _register_controls() {
		$this->add_control(
			'taxonomy',
			[
				'section' => 'settings',
				'type' => 'query',
				'select2options' => [
					'dropdownCssClass' => 'qazana-conditions-select2-dropdown',
				],
				'filter_type' => 'taxonomy',
				'object_type' => $this->taxonomy->name,
			]
		);
	}
}
