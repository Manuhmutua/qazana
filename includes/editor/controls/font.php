<?php
namespace Qazana;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Qazana font control.
 *
 * A base control for creating font control. Displays font select box. The
 * control allows you to set a list of fonts.
 *
 * @since 1.0.0
 */
class Control_Font extends Base_Data_Control {

	/**
	 * Get font control type.
	 *
	 * Retrieve the control type, in this case `font`.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @return string Control type.
	 */
	public function get_type() {
		return 'font';
	}

	/**
	 * Get font control default settings.
	 *
	 * Retrieve the default settings of the font control. Used to return the default
	 * settings while initializing the font control.
	 *
	 * @since 1.0.0
	 * @access protected
	 *
	 * @return array Control default settings.
	 */
	protected function get_default_settings() {
		return [
			'groups' => Fonts::get_font_groups(),
			'options' => Fonts::get_fonts(),
		];
	}

	/**
	 * Render font control output in the editor.
	 *
	 * Used to generate the control HTML in the editor using Underscore JS
	 * template. The variables for the class are available using `data` JS
	 * object.
	 *
	 * @since 1.0.0
	 * @access public
	 */
	public function content_template() {
		$control_uid = $this->get_control_uid();
		?>
		<div class="qazana-control-field">
			<label for="<?php echo $control_uid; ?>" class="qazana-control-title">{{{ data.label }}}</label>
			<div class="qazana-control-input-wrapper">
				<select id="<?php echo $control_uid; ?>" class="qazana-control-font-family" data-setting="{{ data.name }}">
					<option value=""><?php _e( 'Default', 'qazana' ); ?></option>
					<# _.each( data.groups, function( group_label, group_name ) {
						var groupFonts = getFontsByGroups( group_name );
						if ( ! _.isEmpty( groupFonts ) ) { #>
						<optgroup label="{{ group_label }}">
							<# _.each( groupFonts, function( fontType, fontName ) { #>
								<option value="{{ fontName }}">{{{ fontName }}}</option>
							<# } ); #>
						</optgroup>
						<# }
					}); #>
				</select>
			</div>
		</div>
		<# if ( data.description ) { #>
			<div class="qazana-control-field-description">{{{ data.description }}}</div>
		<# } #>
		<?php
	}
}
