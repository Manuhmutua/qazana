<?php
namespace Qazana\Extensions\Tags;

use Qazana\Core\DynamicTags\Tag;
use Qazana\Extensions\Global_Dynamic_Tags;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class Post_Excerpt extends Tag {
	public function get_name() {
		return 'post-excerpt';
	}

	public function get_title() {
		return __( 'Post Excerpt', 'qazana' );
	}

	public function get_group() {
		return Global_Dynamic_Tags::POST_GROUP;
	}

	public function get_categories() {
		return [ Global_Dynamic_Tags::TEXT_CATEGORY ];
	}

	public function render() {
		// Allow only a real `post_excerpt` and not the trimmed `post_content` from the `get_the_excerpt` filter
		$post = get_post();

		if ( ! $post || empty( $post->post_excerpt ) ) {
			return;
		}

		echo esc_html( $post->post_excerpt );
	}
}
