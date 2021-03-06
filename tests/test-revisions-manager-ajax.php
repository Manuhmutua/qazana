<?php
namespace Qazana\Testing\Extensions\History;

use Qazana\Editor;
use Qazana\Testing\Qazana_Test_AJAX;

class Qazana_Test_Revisions_Manager_Ajax extends Qazana_Test_AJAX {

	private $revisions_manager;
	private $fake_post_id = 1234;

	public function setUp() {
		parent::setUp();
		if ( ! $this->revisions_manager ) {
            
            $manager = $this->qazana()->extensions_manager;

            $manager->loader->add_stack( array( 'path' => $this->qazana()->plugin_dir, 'uri' => $this->qazana()->plugin_url ), $this->qazana()->plugin_extensions_locations );

            foreach ( $manager->loader->merge_files_stack_locations() as $path ) {
                if ( ! is_dir( $path ) ) {
                    return false;
                }
                
                $folders = scandir( $path, 1 );

                $manager->initialize_extension( 'history', $path );
            }

            wp_set_current_user( $this->factory()->create_and_get_administrator_user()->ID );

            $extension = $manager->get_extension( 'history' );

            $this->define_doing_ajax();

			$this->revisions_manager = $this->qazana()->revisions_manager;
		}
	}

	public function test_should_not_get_revision_data_on_request_because_of_unset_revision_ID() {
		$response = $this->setUp_test_for_on_revision_data_request();

		$this->assertFalse( $response['success'],
			'the function "on_revision_data_request" should return "success = false"' );
		$this->assertEquals( 'You must set the revision ID.', $response['data'],
			'the function "on_revision_data_request" should return "data = You must set the revision ID."' );
	}

	public function test_should_not_get_revision_data_on_request_because_of_invalid_revision() {
		$_POST['id'] = $this->fake_post_id;

		$response = $this->setUp_test_for_on_revision_data_request();

		$this->assertFalse( $response['success'],
			'the function "on_revision_data_request" should return "success = false"' );
		$this->assertEquals( 'Invalid revision.', $response['data'],
			'the function "on_revision_data_request" should return "data = Invalid revision."' );
	}

	public function test_should_not_get_revision_data_on_request_because_of_access_denied() {
		wp_set_current_user( $this->factory()->get_subscriber_user()->ID );
		$_POST['id'] = $this->factory()->get_default_post();

		$response = $this->setUp_test_for_on_revision_data_request();

		$this->assertFalse( $response['success'],
			'the function "on_revision_data_request" should return "success = false"' );
		$this->assertEquals( __( 'Access denied.', 'qazana' ), $response['data'],
			'the function "on_revision_data_request" should return "data = ' . __( 'Access denied.', 'qazana' ) . '"' );
	}

	public function test_should_get_revision_data_on_request() {
		wp_set_current_user( $this->factory()->create_and_get_administrator_user()->ID );
		$_POST['id'] = $this->factory()->create_and_get_default_post();

		$response = $this->setUp_test_for_on_revision_data_request();

		$this->assertTrue( $response['success'],
			'the function "on_revision_data_request" should return "success = true"' );
		$this->assertArrayHaveKeys( [ 'settings', 'elements' ], $response['data'] );
	}

	public function test_should_not_delete_revision_on_request_because_of_unset_revision_ID() {
		$response = $this->setUp_test_for_on_delete_revision_request();

		$this->assertFalse( $response['success'],
			'the function "on_delete_revision_request" should return "success = false"' );
		$this->assertEquals( 'You must set the revision ID.', $response['data'],
			'the function "on_delete_revision_request" should return "data = You must set the revision ID."' );
	}

	public function test_should_not_delete_revision_on_request_because_of_invalid_revision() {
		$_POST['id'] = $this->fake_post_id;

		$response = $this->setUp_test_for_on_delete_revision_request();

		$this->assertFalse( $response['success'],
			'the function "on_delete_revision_request" should return "success = false"' );
		$this->assertEquals( 'Invalid revision.', $response['data'],
			'the function "on_delete_revision_request" should return "data = Invalid revision."' );
	}

	public function test_should_not_delete_revision_on_request_because_of_access_denied() {
		wp_set_current_user( $this->factory()->get_subscriber_user()->ID );
		$_POST['id'] = $this->factory()->get_default_post();

		$response = $this->setUp_test_for_on_delete_revision_request();

		$this->assertFalse( $response['success'],
			'the function "on_delete_revision_request" should return "success = false"' );
		$this->assertEquals( __( 'Access denied.', 'qazana' ), $response['data'],
			'the function "on_delete_revision_request" should return "data = ' . __( 'Access denied.', 'qazana' ) . '"' );
	}

	public function test_should_not_delete_revision_on_request_because_the_method_cannot_delete_given_revision() {
		wp_set_current_user( $this->factory()->create_and_get_administrator_user()->ID );
		$_POST['id'] = $this->factory()->create_and_get_default_post();

		$response = $this->setUp_test_for_on_delete_revision_request();

		$this->assertFalse( $response['success'],
			'the function "on_delete_revision_request" should return "success = false"' );
		$this->assertEquals( __( 'Cannot delete this revision.', 'qazana' ), $response['data'],
			'the function "on_delete_revision_request" should return "data = ' .
			__( 'Cannot delete this revision.', 'qazana' ) . '"' );
	}

	public function test_should_delete_revision_on_request() {
		wp_set_current_user( $this->factory()->create_and_get_administrator_user()->ID );
		$_POST['id'] = $this->factory()->create_and_get_parent_and_child_posts()['child_id'];

		$response = $this->setUp_test_for_on_delete_revision_request();

		$this->assertTrue( $response['success'],
			'the function "on_delete_revision_request" should return "success = true"' );
		$this->assertArrayNotHasKey( 'data', $response,
			'the function "on_delete_revision_request" should not return data' );
	}

	public function test_should_return_revisions_data() {
		$parent_and_child_posts = $this->factory()->create_and_get_parent_and_child_posts();
		$parent_id = $parent_and_child_posts['parent_id'];
		$child_id = $parent_and_child_posts['child_id'];
		$document = $this->qazana()->documents->get( $parent_id );

		$ret = apply_filters( 'qazana/documents/ajax_save/return_data', [], $document );

		$this->assertArrayHaveKeys( [
			'config',
			'latest_revisions',
			'revisions_ids',
        ], $ret );

		$this->assertEquals( $child_id, $ret['config']['current_revision_id'] );
		$this->assertEquals( 2, count( $ret['latest_revisions'] ) );
		$this->assertEquals( [ $parent_id, $child_id ], $ret['revisions_ids'] );
	}

	private function setUp_test_for_on_revision_data_request() {
		$_POST['_nonce'] = wp_create_nonce( Editor::EDITING_NONCE_KEY );

		return $this->_handleAjaxAndDecode( 'qazana_get_revision_data' );
	}

	private function setUp_test_for_on_delete_revision_request() {
		$_POST['_nonce'] = wp_create_nonce( Editor::EDITING_NONCE_KEY );

		return $this->_handleAjaxAndDecode( 'qazana_delete_revision' );
	}
}