<?php
namespace Qazana\Extensions\Tags;

use Qazana\Core\DynamicTags\Tag;
use Qazana\Extensions\DynamicTags_Site;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class Author_Meta extends Tag {

	public function get_name() {
		return 'author-meta';
	}

	public function get_title() {
		return __( 'Author Meta', 'qazana' );
	}

	public function get_group() {
		return DynamicTags_Site::AUTHOR_GROUP;
	}

	public function get_categories() {
		return [ DynamicTags_Site::TEXT_CATEGORY ];
	}

	public function get_panel_template_setting_key() {
		return 'key';
	}

	public function render() {
		$key = $this->get_settings( 'key' );
		if ( empty( $key ) ) {
			return;
		}

		$value = get_the_author_meta( $key );

		echo wp_kses_post( $value );
	}

	protected function _register_controls() {
		$this->add_control(
			'key',
			[
				'label'   => __( 'Meta Key', 'qazana' ),
			]
		);
	}
}
