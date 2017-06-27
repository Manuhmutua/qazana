<?php
namespace Qazana;

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class Group_Control_Box_Shadow extends Group_Base_Control {

	protected static $fields;

	public static function get_type() {
		return 'box-shadow';
	}

	protected function init_fields() {
		$controls = [];

		$controls['box_shadow_type'] = [
			'label' => _x( 'Box Shadow', 'Box Shadow Control', 'qazana' ),
			'type' => Controls_Manager::SWITCHER,
			'label_on' => __( 'Yes', 'qazana' ),
			'label_off' => __( 'No', 'qazana' ),
			'return_value' => 'yes',
		];

		$controls['box_shadow'] = [
			'label' => _x( 'Box Shadow', 'Box Shadow Control', 'qazana' ),
			'type' => Controls_Manager::BOX_SHADOW,
			'selectors' => [
				'{{SELECTOR}}' => 'box-shadow: {{HORIZONTAL}}px {{VERTICAL}}px {{BLUR}}px {{SPREAD}}px {{COLOR}};',
			],
			'condition' => [
				'box_shadow_type!' => '',
			],
			'separator' => 'after',
		];

		return $controls;
	}
}
