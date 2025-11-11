<?php
/**
 * Class WooCommerceTest
 *
 * @package formsbridge-tests
 */

use FORMS_BRIDGE\Integration;

require_once 'class-base-integration-test.php';

/**
 * WooCommerce integration test case.
 */
class WooCommerceTest extends BaseIntegrationTest {
	public const NAME = 'woo';

	protected static function get_forms() {
		$integration   = Integration::integration( self::NAME );
		$checkout_form = $integration->get_form_by_id( 1 );
		return array( $checkout_form );
	}

	protected static function add_form( $config ) {
		return 1;
	}

	protected static function delete_form( $form ) {
		return true;
	}

	public function test_checkout_form_serialization() {
		$form_data = self::get_form( 'Woo Checkout' );

		$fields = $form_data['fields'];
		$this->assertEquals( 41, count( $fields ) );

		$field = $fields[0];
		$this->assertSame( 'id', $field['name'] );
		$this->assertField( $field, 'number', array( 'schema' => 'integer' ) );

		$field = $fields[1];
		$this->assertSame( 'parent_id', $field['name'] );
		$this->assertField( $field, 'number', array( 'schema' => 'integer' ) );

		$field = $fields[2];
		$this->assertSame( 'status', $field['name'] );
		$this->assertField( $field, 'text' );
	}

	public function test_checkout_submission_serialization() {
		$form_data = self::get_form( 'Woo Checkout' );

		$store = self::store();
		foreach ( $store as $key => $object ) {
			if ( 'checkout-submission' === $key ) {
				$submission = $object;
			}
		}

		if ( ! isset( $submission ) ) {
			throw new Exception( 'Checkout submission not found' );
		}

		$payload = $this->serialize_submission( $submission, $form_data );
	}
}
