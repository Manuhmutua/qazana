<?php
namespace Qazana\Testing\Includes\Template_Library;

use Qazana\Template_Library\Manager;
use Qazana\Testing\Qazana_Test_Base;

class Qazana_Test_Manager_Local extends Qazana_Test_Base {

	/**
	 * @var Manager
	 */
	protected static $manager;
	private $fake_post_id = '123';

	public static function setUpBeforeClass() {
		self::$manager = self::qazana()->templates_manager;
	}

	public function setUp() {
		parent::setUp();
		wp_set_current_user( $this->factory()->get_administrator_user()->ID );
	}

	public function test_should_return_true_from_unregister_source() {
		$this->assertTrue( self::$manager->unregister_source( 'local' ) );
	}

	public function test_should_return_true_from_register_source() {
		$this->assertTrue( self::$manager->register_source( 'Qazana\Template_Library\Source_Local' ) );
	}

	public function test_should_return_registered_sources() {
		$this->assertEquals( self::$manager->get_registered_sources()['local'], new \Qazana\Template_Library\Source_Local() );
	}

	public function test_should_return_source() {
		$this->assertEquals( self::$manager->get_source( 'local' ), new \Qazana\Template_Library\Source_Local() );
	}

	public function test_should_return_wp_error_save_error_from_save_template() {
		wp_set_current_user( $this->factory()->get_subscriber_user()->ID );
		$this->assertWPError(
			self::$manager->save_template(
				[
					'post_id' => $this->fake_post_id,
					'source' => 'local',
					'content' => 'content',
					'type' => 'comment',
				]
			), 'save_error'
		);
	}

	// public function test_should_return_template_data_from_save_template() {
	// 	wp_set_current_user( $this->factory()->create_and_get_administrator_user()->ID );
	// 	$template_data = [
	// 		'post_id' => $this->factory()->get_default_post()->ID,
	// 		'source' => 'local',
	// 		'content' => 'content',
	// 		'type' => 'page',
	// 	];

	// 	$remote_remote = [
	// 		'template_id',
	// 		'source',
	// 		'type',
	// 		'title',
	// 		'thumbnail',
	// 		'hasPageSettings',
	// 		'tags',
	// 		'url',
	// 	];
	// 	$saved_template = self::$manager->save_template( $template_data );

	// 	$this->assertArrayHaveKeys( $remote_remote, $saved_template );
	// }


	public function test_should_return_wp_error_arguments_not_specified_from_update_template() {
		$this->assertWPError( self::$manager->update_template( [ 'post_id' => $this->fake_post_id ] ), 'arguments_not_specified' );
	}


	public function test_should_return_wp_error_template_error_from_update_template() {
		$this->assertWPError(
			self::$manager->update_template(
				[
					'source' => 'content',
					'content' => 'content',
					'type' => 'page',
				]
			), 'template_error'
		);
	}

	public function test_should_return_wp_error_save_error_from_update_template() {
		wp_set_current_user( $this->factory()->get_subscriber_user()->ID );
		$this->assertWPError(
			self::$manager->update_template(
				[
					'source' => 'local',
					'content' => 'content',
					'type' => 'comment',
					'id' => $this->fake_post_id,
				]
			), 'save_error'
		);
	}

	/**
	 *
	 */
	// public function test_should_return_template_data_from_update_template() {
	// 	wp_set_current_user( $this->factory()->create_and_get_administrator_user()->ID );
	// 	$post_id = $this->factory()->create_and_get_default_post()->ID;
	// 	$template_data = [
	// 		'source' => 'local',
	// 		'content' => '{}',
	// 		'type' => 'post',
	// 		'id' => $post_id,
	// 	];

	// 	$remote_remote = [
	// 		'template_id',
	// 		'source',
	// 		'type',
	// 		'title',
	// 		'thumbnail',
	// 		'author',
	// 		'hasPageSettings',
	// 		'tags',
	// 		'url',
	// 	];
	// 	$updated_template = self::$manager->update_template( $template_data );

	// 	$this->assertArrayHaveKeys( $remote_remote, $updated_template );
	// }

	/**
	 * @covers \Qazana\Template_Library\Manager::get_template_data()
	 */
	public function test_should_return_data_from_get_template_data() {
		$ret = self::$manager->get_template_data(
			[
				'source' => 'local',
				'template_id' => $this->fake_post_id,
			]
		);

		$this->assertEquals( $ret, [ 'content' => [] ] );
	}
}
