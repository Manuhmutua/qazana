<?php
namespace Qazana\Extensions\Tags;

use Qazana\Core\DynamicTags\Data_Tag;
use Qazana\Extensions\Global_Dynamic_Tags;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}


class Post_URL extends Data_Tag {

	public function get_name() {
		return 'post-url';
	}

	public function get_title() {
		return __( 'Post URL', 'qazana' );
	}

	public function get_group() {
		return Global_Dynamic_Tags::POST_GROUP;
	}

	public function get_categories() {
		return [ Global_Dynamic_Tags::URL_CATEGORY ];
	}

	public function get_value( array $options = [] ) {
		return get_permalink();
	}
}
