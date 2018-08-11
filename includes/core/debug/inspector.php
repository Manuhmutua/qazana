<?php
namespace Qazana\Core\Debug;

use Qazana\Admin\Settings\Panel;
use Qazana\Admin\Settings\Tools;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class Inspector {

	protected $is_enabled = false;

	protected $log = [];

	public function __construct() {
		$this->is_enabled = 'enable' === get_option( 'qazana_enable_inspector' );

		if ( $this->is_enabled ) {
			add_action( 'admin_bar_menu', [ $this, 'add_menu_in_admin_bar' ], 201 );
		}

		add_action( 'qazana/admin/after_create_settings/' . Tools::PAGE_ID, [ $this, 'register_admin_tools_fields' ], 50 );
	}

	public function is_enabled() {
		return $this->is_enabled;
	}

	public function register_admin_tools_fields( Tools $tools ) {
		$tools->add_fields( Panel::TAB_GENERAL, 'tools', [
			'enable_inspector' => [
				'label' => __( 'Inspector', 'qazana' ),
				'field_args' => [
					'type' => 'select',
					'options' => [
						'' => __( 'Disable', 'qazana' ),
						'enable' => __( 'Enable', 'qazana' ),
					],
					'sub_desc' => __( 'Enable Inspector', 'qazana' ),
					'desc' => __( 'Inspector adds an admin bar menu that lists all the templates that are used on a page that is being displayed.', 'qazana' ),
				],
			],
		] );
	}

	public function parse_template_path( $template ) {
		// `untrailingslashit` for windows path style.
		if ( 0 === strpos( $template, untrailingslashit( plugin_dir_path( QAZANA__FILE__ ) ) ) ) {
			return 'Qazana - ' . basename( $template );
		}

		if ( 0 === strpos( $template, get_stylesheet_directory() ) ) {
			return wp_get_theme()->get( 'Name' ) . ' - ' . basename( $template );
		}

		$plugins_dir = dirname( plugin_dir_path( QAZANA__FILE__ ) );
		if ( 0 === strpos( $template, $plugins_dir ) ) {
			return ltrim( str_replace( $plugins_dir, '', $template ), '/\\' );
		}

		return str_replace( WP_CONTENT_DIR, '', $template );
	}

	public function add_log( $module, $title, $url = '' ) {
		if ( ! $this->is_enabled ) {
			return;
		}

		if ( ! isset( $this->log[ $module ] ) ) {
			$this->log[ $module ] = [];
		}

		$this->log[ $module ][] = [
			'title' => $title,
			'url' => $url,
		];
	}

	public function add_menu_in_admin_bar( \WP_Admin_Bar $wp_admin_bar ) {
		if ( empty( $this->log ) ) {
			return;
		}

		$wp_admin_bar->add_node( [
			'id' => 'qazana_inspector',
			'title' => __( 'Qazana Inspector', 'qazana' ),
		] );

		foreach ( $this->log as $module => $log ) {
			$module_id = sanitize_key( $module );

			$wp_admin_bar->add_menu( [
				'id' => 'qazana_inspector_' . $module_id,
				'parent' => 'qazana_inspector',
				'title' => $module,
			] );

			foreach ( $log as $index => $row ) {
				$url = $row['url'];

				unset( $row['url'] );

				$wp_admin_bar->add_menu( [
					'id' => 'qazana_inspector_log_' . $module_id . '_' . $index,
					'parent' => 'qazana_inspector_' . $module_id,
					'href' => $url,
					'title' => implode( ' > ', $row ),
					'meta' => [
						'target' => '_blank',
					],
				] );
			}
		}
	}
}
