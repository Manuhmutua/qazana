<?php
namespace Qazana;
use Qazana\Extensions\DynamicTags as TagsModule;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Qazana media control.
 *
 * A base control for creating a media chooser control. Based on the WordPress
 * media library. Used to select an image from the WordPress media library.
 *
 * @since 1.0.0
 */
class Control_Media extends Control_Base_Multiple {

	/**
	 * Get media control type.
	 *
	 * Retrieve the control type, in this case `media`.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @return string Control type.
	 */
	public function get_type() {
		return 'media';
	}

	/**
	 * Get media control default values.
	 *
	 * Retrieve the default value of the media control. Used to return the default
	 * values while initializing the media control.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @return array Control default value.
	 */
	public function get_default_value() {
		return [
			'url' => '',
			'id' => '',
		];
	}

	/**
	 * Import media images.
	 *
	 * Used to import media control files from external sites while importing
	 * Qazana template JSON file, and replacing the old data.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @param array $settings Control settings
	 *
	 * @return array Control settings.
	 */
	public function on_import( $settings ) {
		if ( empty( $settings['url'] ) ) {
			return $settings;
		}

		$settings = qazana()->templates_manager->get_import_images_instance()->import( $settings );

		if ( ! $settings ) {
			$settings = [
				'id' => '',
				'url' => Utils::get_placeholder_image_src(),
			];
		}

		return $settings;
	}

	/**
	 * Enqueue media control scripts and styles.
	 *
	 * Used to register and enqueue custom scripts and styles used by the media
	 * control.
	 *
	 * @since 1.0.0
	 * @access public
	 */
	public function enqueue() {
		$suffix = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';
		wp_enqueue_media();

		wp_enqueue_style(
			'media',
			admin_url( '/css/media' . $suffix . '.css' )
		);

		wp_register_script(
			'image-edit',
			admin_url( '/js/image-edit' . $suffix . '.js' ),
			[
				'jquery',
				'json2',
				'imgareaselect',
			],
			false,
			true
		);

		wp_enqueue_script( 'image-edit' );
	}

	/**
	 * Render media control output in the editor.
	 *
	 * Used to generate the control HTML in the editor using Underscore JS
	 * template. The variables for the class are available using `data` JS
	 * object.
	 *
	 * @since 1.0.0
	 * @access public
	 */
	public function content_template() {
		?>
		<div class="qazana-control-field">
			<label class="qazana-control-title">{{{ data.label }}}</label>
			<div class="qazana-control-input-wrapper">
				<div class="qazana-control-media qazana-control-tag-area qazana-control-preview-area qazana-aspect-ratio-169">
					<div class="qazana-control-media-upload-button">
						<i class="fa fa-plus-circle" aria-hidden="true"></i>
					</div>
					<div class="qazana-control-media-area{{{ 'video' === data.media_type ? ' qazana-fit-aspect-ratio' : '' }}}">
						<# if( 'image' === data.media_type ) { #>
							<div class="qazana-control-media-image"></div>
						<# } else if( 'video' === data.media_type ) { #>
							<video class="qazana-control-media-video" preload="metadata"></video>
							<i class="fa fa-video-camera"></i>
						<# } #>
						<div class="qazana-control-media-delete"><?php _e( 'Delete', 'qazana' ); ?></div>
					</div>
				</div>
			</div>
			<# if ( data.description ) { #>
				<div class="qazana-control-field-description">{{{ data.description }}}</div>
			<# } #>
			<input type="hidden" data-setting="{{ data.name }}" />
		</div>
		<?php
	}

	/**
	 * Get media control default settings.
	 *
	 * Retrieve the default settings of the media control. Used to return the default
	 * settings while initializing the media control.
	 *
	 * @since 1.0.0
	 * @access protected
	 *
	 * @return array Control default settings.
	 */
	protected function get_default_settings() {
		return [
			'label_block' => true,
			'media_type' => 'image',
			'dynamic' => [
				'categories' => [ TagsModule::IMAGE_CATEGORY ],
				'returnType' => 'object',
			],
		];
	}

	/**
	 * Get media control image title.
	 *
	 * Retrieve the `title` of the image selected by the media control.
	 *
	 * @since 1.0.0
	 * @access public
	 * @static
	 *
	 * @param array $attachment Media attachment.
	 *
	 * @return string Image title.
	 */
	public static function get_image_title( $attachment ) {
		if ( empty( $attachment['id'] ) ) {
			return '';
		}

		return get_the_title( $attachment['id'] );
	}

	/**
	 * Get media control image alt.
	 *
	 * Retrieve the `alt` value of the image selected by the media control.
	 *
	 * @since 1.0.0
	 * @access public
	 * @static
	 *
	 * @param array $instance Media attachment.
	 *
	 * @return string Image alt.
	 */
	public static function get_image_alt( $instance ) {
		if ( empty( $instance['id'] ) ) {
			return '';
		}

		$attachment_id = $instance['id'];
		if ( ! $attachment_id ) {
			return '';
		}

		$attachment = get_post( $attachment_id );
		if ( ! $attachment ) {
			return '';
		}

		$alt = get_post_meta( $attachment_id, '_wp_attachment_image_alt', true );
		if ( ! $alt ) {
			$alt = $attachment->post_excerpt;
			if ( ! $alt ) {
				$alt = $attachment->post_title;
			}
		}
		return trim( strip_tags( $alt ) );
	}
}
