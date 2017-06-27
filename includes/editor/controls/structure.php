<?php
namespace Qazana;

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * A private control for section columns structure.
 *
 * @since 1.0.0
 */
class Control_Structure extends Base_Control {

	public function get_type() {
		return 'structure';
	}

	public function content_template() {
		?>
		<div class="qazana-control-field">
			<div class="qazana-control-input-wrapper">
				<div class="qazana-control-structure-title"><?php _e( 'Structure', 'qazana' ); ?></div>
				<# var currentPreset = qazana.presetsFactory.getPresetByStructure( data.controlValue ); #>
				<div class="qazana-control-structure-preset qazana-control-structure-current-preset">
					{{{ qazana.presetsFactory.getPresetSVG( currentPreset.preset, 233, 72, 5 ).outerHTML }}}
				</div>
				<div class="qazana-control-structure-reset"><i class="fa fa-undo"></i><?php _e( 'Reset Structure', 'qazana' ); ?></div>
				<#
				var morePresets = getMorePresets();

				if ( morePresets.length > 1 ) { #>
					<div class="qazana-control-structure-more-presets-title"><?php _e( 'More Structures', 'qazana' ); ?></div>
					<div class="qazana-control-structure-more-presets">
						<# _.each( morePresets, function( preset ) { #>
							<div class="qazana-control-structure-preset-wrapper">
								<input id="qazana-control-structure-preset-{{ data._cid }}-{{ preset.key }}" type="radio" name="qazana-control-structure-preset-{{ data._cid }}" data-setting="structure" value="{{ preset.key }}">
								<label class="qazana-control-structure-preset" for="qazana-control-structure-preset-{{ data._cid }}-{{ preset.key }}">
									{{{ qazana.presetsFactory.getPresetSVG( preset.preset, 102, 42 ).outerHTML }}}
								</label>
								<div class="qazana-control-structure-preset-title">{{{ preset.preset.join( ', ' ) }}}</div>
							</div>
						<# } ); #>
					</div>
				<# } #>
			</div>
		</div>
		
		<# if ( data.description ) { #>
			<div class="qazana-control-description">{{{ data.description }}}</div>
		<# } #>
		<?php
	}

	protected function get_default_settings() {
		return [
			'separator' => 'none',
		];
	}
}
