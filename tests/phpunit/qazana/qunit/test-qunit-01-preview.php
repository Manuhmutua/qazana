<?php
namespace Qazana\Testing;

use Qazana\Core;

class Qazana_Test_Qunit_Preview extends Qazana_Test_Base {

	public function setUp() {
		parent::setUp();

		define( 'WP_ADMIN', false );
		define( 'WP_USE_THEMES', true );
		$_GET['qazana-preview'] = 1;

		wp_set_current_user( $this->factory()->create_and_get_administrator_user()->ID );

        $this->qazana()->document = new Core\Managers\Documents();

		$GLOBALS['post'] = $this->factory()->create_and_get_default_post();

		add_post_meta( $GLOBALS['post']->ID, '_qazana_edit_mode', 'qazana' );

		query_posts(
			[
				'p' => $GLOBALS['post']->ID,
				'post_type' => 'any',
			]
		);

		$_GET['qazana-preview'] = $GLOBALS['post']->ID;

		$this->qazana()->preview->init();

		// Load the theme template.
		$template = get_index_template();

		/** This filter is documented in wp-includes/template-loader.php */
		$template = apply_filters( 'template_include', $template );

		// Recreate the frontend instance because it's scripts hooks are removed in previous test
		$this->qazana()->frontend = new \Qazana\Frontend();

		ob_start();

		require $template;

		$html = ob_get_clean();

		$plugin_path = str_replace( '\\', '/', $this->qazana()->plugin_dir );
		$quint = '' .
		         '<script src="file://' . $plugin_path . 'tests/qunit/vendor/j-ulrich/jquery-simulate-ext/jquery.simulate.js"></script>' .
		         '<script src="file://' . $plugin_path . 'tests/qunit/vendor/j-ulrich/jquery-simulate-ext/jquery.simulate.ext.js"></script>' .
		         '<script src="file://' . $plugin_path . 'tests/qunit/vendor/j-ulrich/jquery-simulate-ext/jquery.simulate.drag-n-drop.js"></script>';

		$html = str_replace( '</body>', $quint . '</body>', $html );

		$html = fix_qunit_html_urls( $html );

		file_put_contents( __DIR__ . '/../../../qunit/preview.html', $html );
	}

	public function test_staticPreviewExist() {
		$this->assertNotFalse( file_exists( __DIR__ . '/../../../qunit/preview.html' ) );
	}
}
