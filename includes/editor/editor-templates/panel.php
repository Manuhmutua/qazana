<?php
namespace Qazana;

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
?>
<script type="text/template" id="tmpl-qazana-panel">
	<div id="qazana-mode-switcher"></div>
	<header id="qazana-panel-header-wrapper"></header>
	<main id="qazana-panel-content-wrapper"></main>
	<footer id="qazana-panel-footer">
		<div class="qazana-panel-container">
		</div>
	</footer>
</script>

<script type="text/template" id="tmpl-qazana-panel-menu-item">
	<div class="qazana-panel-menu-item-icon">
		<i class="fa fa-{{ icon }}"></i>
	</div>
	<div class="qazana-panel-menu-item-title">{{{ title }}}</div>
</script>

<script type="text/template" id="tmpl-qazana-panel-header">
    <div class="qazana-panel-header-row">

        <div id="qazana-panel-header-exit" class="qazana-panel-header-button" title="<?php _e( 'Exit', 'qazana' ); ?>">
            <a id="qazana-panel-header-view-page" href="<?php the_permalink(); ?>">
                <span class="screen-reader-text"><?php esc_attr_e( 'View Page', 'qazana' ); ?></span>
            </a>
        </div>

        <div id="qazana-panel-header-menu" class="qazana-panel-header-menu" title="<?php _e( 'Menu', 'qazana' ); ?>">

            <ul class="qazana-panel-header-nav">
                <li>
                    <span id="qazana-panel-header-nav-button" class="qazana-panel-header-nav-button" title="<?php esc_attr_e( 'Qazana Dashboard', 'qazana' ); ?>" href="<?php the_permalink(); ?>"></span>
                    <ul>
                        <li><a id="qazana-panel-header-nav-view-page" href="<?php the_permalink(); ?>" target="_blank">
                            <i class="dashicons dashicons-plus"></i>
                            <span><?php esc_attr_e( 'View Page', 'qazana' ); ?></span>
                        </a>
                        </li>
                            <li>
                                <a class="qazana-panel-header-nav-button qazana-panel-header-nav-button-add-new-page" href="<?php
                                echo esc_url( add_query_arg(
                                    array(
                                        'post_type' => 'page',
                                        'ref' => 'qazana',
                                    ),
                                    admin_url( 'post-new.php' )
                                ) ); ?>"><i class="icon-display"></i>
                                    <span><?php esc_attr_e( 'Add new qazana page', 'qazana' ); ?></span></a>
                            </li>
                            <li>
                                <a class="qazana-panel-header-nav-button qazana-panel-header-nav-button-dashboard" href="<?php echo admin_url( 'admin.php?page=' . qazana()->slug ); ?>"><i class="layers-button-icon-dashboard"></i>
                                <span><?php esc_attr_e( 'Qazana Dashboard', 'qazana' ); ?></span></a>

                            </li>
                    </ul>
                </li>
            </ul>
        </div>

        <div id="qazana-panel-header-save" class="qazana-panel-header-tool" title="<?php esc_attr_e( 'Publish', 'qazana' ); ?>">
            <button class="qazana-button">
                <span class="qazana-state-icon">
                    <i class="fa fa-spin fa-circle-o-notch" data-tooltip="<?php esc_attr_e( 'Publish', 'qazana' ); ?>"></i>
                </span>
                <span><?php esc_attr_e( 'Publish', 'qazana' ); ?></span>
            </button>
        </div>

    </div>

    <div class="qazana-panel-header-row">

        <div id="qazana-panel-header-menu-button" class="qazana-header-button">
            <i class="qazana-icon eicon-menu tooltip-target" data-tooltip="<?php esc_attr_e( 'Menu', 'qazana' ); ?>"></i>
        </div>
        <div id="qazana-panel-header-title"></div>
        <div id="qazana-panel-header-add-button" class="qazana-header-button">
            <i class="qazana-icon eicon-apps tooltip-target" data-tooltip="<?php esc_attr_e( 'Widgets Panel', 'qazana' ); ?>"></i>
        </div>

    </div>

</script>

<script type="text/template" id="tmpl-qazana-panel-footer-content">
    <div id="qazana-panel-footer-exit" class="qazana-panel-footer-tool" title="<?php _e( 'Exit', 'qazana' ); ?>">
        <i class="fa fa-times"></i>
        <div class="qazana-panel-footer-sub-menu-wrapper">
            <div class="qazana-panel-footer-sub-menu">
                <a id="qazana-panel-footer-view-page" class="qazana-panel-footer-sub-menu-item" href="<?php the_permalink(); ?>" target="_blank">
                    <i class="qazana-icon fa fa-external-link"></i>
                    <span class="qazana-title"><?php _e( 'View Page', 'qazana' ); ?></span>
                </a>
                <a id="qazana-panel-footer-view-edit-page" class="qazana-panel-footer-sub-menu-item" href="<?php echo get_edit_post_link(); ?>">
                    <i class="qazana-icon fa fa-wordpress"></i>
                    <span class="qazana-title"><?php _e( 'Go to Dashboard', 'qazana' ); ?></span>
                </a>
            </div>
        </div>
    </div>
    <div id="qazana-panel-footer-responsive" class="qazana-panel-footer-tool" title="<?php esc_attr_e( 'Responsive Mode', 'qazana' ); ?>">
        <i class="eicon-device-desktop"></i>
        <div class="qazana-panel-footer-sub-menu-wrapper">
            <div class="qazana-panel-footer-sub-menu">
                <div class="qazana-panel-footer-sub-menu-item" data-device-mode="desktop">
                    <i class="qazana-icon eicon-device-desktop"></i>
                    <span class="qazana-title"><?php _e( 'Desktop', 'qazana' ); ?></span>
                    <span class="qazana-description"><?php _e( 'Default Preview', 'qazana' ); ?></span>
                </div>
                <div class="qazana-panel-footer-sub-menu-item" data-device-mode="tablet">
                    <i class="qazana-icon eicon-device-tablet"></i>
                    <span class="qazana-title"><?php _e( 'Tablet', 'qazana' ); ?></span>
                    <span class="qazana-description"><?php _e( 'Preview for 768px', 'qazana' ); ?></span>
                </div>
                <div class="qazana-panel-footer-sub-menu-item" data-device-mode="mobile">
                    <i class="qazana-icon eicon-device-mobile"></i>
                    <span class="qazana-title"><?php _e( 'Mobile', 'qazana' ); ?></span>
                    <span class="qazana-description"><?php _e( 'Preview for 360px', 'qazana' ); ?></span>
                </div>
            </div>
        </div>
    </div>

    <div id="qazana-panel-footer-templates" class="qazana-panel-footer-tool" title="<?php esc_attr_e( 'Templates', 'qazana' ); ?>">
        <span class="qazana-screen-only"><?php _e( 'Templates', 'qazana' ); ?></span>
        <i class="fa fa-folder"></i>
        <div class="qazana-panel-footer-sub-menu-wrapper">
            <div class="qazana-panel-footer-sub-menu">
                <div id="qazana-panel-footer-templates-modal" class="qazana-panel-footer-sub-menu-item">
                    <i class="qazana-icon fa fa-folder"></i>
                    <span class="qazana-title"><?php _e( 'Templates Library', 'qazana' ); ?></span>
                </div>
                <div id="qazana-panel-footer-save-template" class="qazana-panel-footer-sub-menu-item">
                    <i class="qazana-icon fa fa-save"></i>
                    <span class="qazana-title"><?php _e( 'Save Template', 'qazana' ); ?></span>
                </div>
            </div>
        </div>
    </div>
    <div id="qazana-panel-footer-save" class="qazana-panel-footer-tool" title="<?php esc_attr_e( 'Save', 'qazana' ); ?>">
        <button class="qazana-button">
            <span class="qazana-state-icon">
                <i class="fa fa-spin fa-circle-o-notch "></i>
            </span>
            <?php _e( 'Publish', 'qazana' ); ?>
        </button>
        <?php /*<div class="qazana-panel-footer-sub-menu-wrapper">
            <div class="qazana-panel-footer-sub-menu">
                <div id="qazana-panel-footer-publish" class="qazana-panel-footer-sub-menu-item">
                    <i class="qazana-icon fa fa-check-circle"></i>
                    <span class="qazana-title"><?php _e( 'Publish', 'qazana' ); ?></span>
                </div>
                <div id="qazana-panel-footer-discard" class="qazana-panel-footer-sub-menu-item">
                    <i class="qazana-icon fa fa-times-circle"></i>
                    <span class="qazana-title"><?php _e( 'Discard', 'qazana' ); ?></span>
                </div>
            </div>
        </div>*/ ?>
    </div>
</script>

<script type="text/template" id="tmpl-qazana-mode-switcher-content">
	<input id="qazana-mode-switcher-preview-input" type="checkbox">
	<label for="qazana-mode-switcher-preview-input" id="qazana-mode-switcher-preview" title="<?php esc_attr_e( 'Preview', 'qazana' ); ?>">
		<span class="qazana-screen-only"><?php _e( 'Preview', 'qazana' ); ?></span>
		<i class="fa"></i>
	</label>
</script>

<script type="text/template" id="tmpl-editor-content">
	<div class="qazana-panel-navigation">
		<# _.each( elementData.tabs_controls, function( tabTitle, tabSlug ) { #>
		<div class="qazana-panel-navigation-tab qazana-tab-control-{{ tabSlug }}">
			<a data-tab="{{ tabSlug }}">
				{{{ tabTitle }}}
			</a>
		</div>
		<# } ); #>
	</div>
	<# if ( elementData.reload_preview ) { #>
		<div id="qazana-update-preview">
			<div id="qazana-update-preview-title"><?php echo __( 'Update changes to page', 'qazana' ); ?></div>
			<div id="qazana-update-preview-button-wrapper">
				<button id="qazana-update-preview-button" class="qazana-button qazana-button-success"><?php echo __( 'Apply', 'qazana' ); ?></button>
			</div>
		</div>
	<# } #>
	<div id="qazana-controls"></div>
</script>

<script type="text/template" id="tmpl-qazana-panel-schemes-disabled">
	<i class="qazana-panel-nerd-box-icon eicon-nerd"></i>
	<div class="qazana-panel-nerd-box-title">{{{ '<?php echo __( '{0} are disabled', 'qazana' ); ?>'.replace( '{0}', disabledTitle ) }}}</div>
	<div class="qazana-panel-nerd-box-message"><?php printf( __( 'You can enable it from the <a href="%s" target="_blank">Qazana settings page</a>.', 'qazana' ), admin_url( 'admin.php?page=' . qazana()->slug ) ); ?></div>
</script>

<script type="text/template" id="tmpl-qazana-panel-scheme-color-item">
	<div class="qazana-panel-scheme-color-input-wrapper">
		<input type="text" class="qazana-panel-scheme-color-value" value="{{ value }}" data-alpha="true" />
	</div>
	<div class="qazana-panel-scheme-color-title">{{{ title }}}</div>
</script>

<script type="text/template" id="tmpl-qazana-panel-scheme-typography-item">
	<div class="qazana-panel-heading">
		<div class="qazana-panel-heading-toggle">
			<i class="fa"></i>
		</div>
		<div class="qazana-panel-heading-title">{{{ title }}}</div>
	</div>
	<div class="qazana-panel-scheme-typography-items qazana-panel-box-content">
		<?php
		$scheme_fields_keys = Group_Control_Typography::get_scheme_fields_keys();

		$typography_group = qazana()->controls_manager->get_control_groups( 'typography' );

		$typography_fields = $typography_group->get_fields();

		$scheme_fields = array_intersect_key( $typography_fields, array_flip( $scheme_fields_keys ) );

		foreach ( $scheme_fields as $option_name => $option ) : ?>
			<div class="qazana-panel-scheme-typography-item">
				<div class="qazana-panel-scheme-item-title qazana-control-title"><?php echo $option['label']; ?></div>
				<div class="qazana-panel-scheme-typography-item-value">
					<?php if ( 'select' === $option['type'] ) : ?>
						<select name="<?php echo $option_name; ?>" class="qazana-panel-scheme-typography-item-field">
							<?php foreach ( $option['options'] as $field_key => $field_value ) : ?>
								<option value="<?php echo $field_key; ?>"><?php echo $field_value; ?></option>
							<?php endforeach; ?>
						</select>
					<?php elseif ( 'font' === $option['type'] ) : ?>
						<select name="<?php echo $option_name; ?>" class="qazana-panel-scheme-typography-item-field">
							<option value=""><?php _e( 'Default', 'qazana' ); ?></option>

							<optgroup label="<?php _e( 'System', 'qazana' ); ?>">
								<?php foreach ( Fonts::get_fonts_by_groups( [ Fonts::SYSTEM ] ) as $font_title => $font_type ) : ?>
									<option value="<?php echo esc_attr( $font_title ); ?>"><?php echo $font_title; ?></option>
								<?php endforeach; ?>
							</optgroup>

							<optgroup label="<?php _e( 'Google', 'qazana' ); ?>">
								<?php foreach ( Fonts::get_fonts_by_groups( [ Fonts::GOOGLE, Fonts::EARLYACCESS ] ) as $font_title => $font_type ) : ?>
									<option value="<?php echo esc_attr( $font_title ); ?>"><?php echo $font_title; ?></option>
								<?php endforeach; ?>
							</optgroup>
						</select>
					<?php elseif ( 'text' === $option['type'] ) : ?>
						<input name="<?php echo $option_name; ?>" class="qazana-panel-scheme-typography-item-field" />
					<?php endif; ?>
				</div>
			</div>
		<?php endforeach; ?>
	</div>
</script>

<script type="text/template" id="tmpl-qazana-control-responsive-switchers">
	<div class="qazana-control-responsive-switchers">
		<a class="qazana-responsive-switcher qazana-responsive-switcher-desktop" data-device="desktop">
			<i class="eicon-device-desktop"></i>
		</a>
		<a class="qazana-responsive-switcher qazana-responsive-switcher-tablet" data-device="tablet">
			<i class="eicon-device-tablet"></i>
		</a>
		<a class="qazana-responsive-switcher qazana-responsive-switcher-mobile" data-device="mobile">
			<i class="eicon-device-mobile"></i>
		</a>
	</div>
</script>

<script type="text/template" id="tmpl-qazana-panel-revisions">
	<div class="qazana-panel-scheme-buttons">
		<div class="qazana-panel-scheme-button-wrapper qazana-panel-scheme-discard">
			<button class="qazana-button" disabled>
				<i class="fa fa-times"></i><?php _e( 'Discard', 'qazana' ); ?>
			</button>
		</div>
		<div class="qazana-panel-scheme-button-wrapper qazana-panel-scheme-save">
			<button class="qazana-button qazana-button-success" disabled>
				<?php _e( 'Apply', 'qazana' ); ?>
			</button>
		</div>
	</div>
	<div class="qazana-panel-box">
		<div class="qazana-panel-heading">
			<div class="qazana-panel-heading-title"><?php _e( 'Revision History', 'qazana' ); ?></div>
		</div>
		<div id="qazana-revisions-list" class="qazana-panel-box-content"></div>
	</div>
</script>

<script type="text/template" id="tmpl-qazana-panel-revisions-no-revisions">
	<i class="qazana-panel-nerd-box-icon eicon-nerd"></i>
	<div class="qazana-panel-nerd-box-title"><?php _e( 'No Revisions Saved Yet', 'qazana' ); ?></div>
	<div class="qazana-panel-nerd-box-message">{{{ qazana.translate( qazana.config.revisions_enabled ? 'no_revisions_1' : 'revisions_disabled_1' ) }}}</div>
	<div class="qazana-panel-nerd-box-message">{{{ qazana.translate( qazana.config.revisions_enabled ? 'no_revisions_2' : 'revisions_disabled_2' ) }}}</div>
</script>

<script type="text/template" id="tmpl-qazana-panel-revisions-revision-item">
	<div class="qazana-revision-item__gravatar">{{{ gravatar }}}</div>
	<div class="qazana-revision-item__details">
		<div class="qazana-revision-date">{{{ date }}}</div>
		<div class="qazana-revision-meta">{{{ qazana.translate( type ) }}} <?php _e( 'By', 'qazana' ); ?> {{{ author }}}</div>
	</div>
	<div class="qazana-revision-item__tools">
		<i class="qazana-revision-item__tools-delete fa fa-times"></i>
		<i class="qazana-revision-item__tools-spinner fa fa-spin fa-circle-o-notch"></i>
	</div>
</script>
