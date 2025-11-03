<?php
/**
 * Class CustomHooksTest
 *
 * @package forms-bridge-tests
 */

use FORMS_BRIDGE\Addon;

/**
 * Custom hooks test case.
 */
class CustomHooksTest extends WP_UnitTestCase {
	/**
	 * Handles the last intercepted http request data.
	 *
	 * @var array
	 */
	private static $request;

	public static function set_up_before_class() {
		add_filter(
			'forms_bridge_forms',
			function () {
				return array(
					array(
						'_id'    => 'gf:1',
						'id'     => '1',
						'title'  => 'test-form',
						'fields' => array(),
					),
				);
			},
		);

		$result = FBAPI::save_credential(
			array(
				'name'          => 'test-basic',
				'schema'        => 'Basic',
				'client_id'     => 'foo',
				'client_secret' => 'bar',
			)
		);

		if ( ! $result ) {
			throw new Exception( 'Can not create the credential' );
		}

		$result = FBAPI::save_backend(
			array(
				'name'       => 'test-backend',
				'base_url'   => 'https://example.coop',
				'credential' => 'test-basic',
				'headers'    => array(
					array(
						'name'  => 'Content-Type',
						'value' => 'application/json',
					),
				),
			),
		);

		if ( ! $result ) {
			throw new Exception( 'Can not create the backend' );
		}

		$result = FBAPI::save_bridge(
			array(
				'name'          => 'test-bridge',
				'form_id'       => 'gf:1',
				'backend'       => 'test-backend',
				'endpoint'      => '/api/endpoint',
				'method'        => 'POST',
				'custom_fields' => array(
					'a' => 'b',
				),
				'mutations'     => array(
					array(
						array(
							'from' => 'foo',
							'to'   => 'boofoo',
							'cast' => 'string',
						),
					),
				),
			),
			'rest'
		);

		if ( ! $result ) {
			throw new Exception( 'Can not create the bridge' );
		}

		add_filter(
			'pre_http_request',
			static function ( $pre, $args, $url ) {
				self::$request = array(
					'args' => $args,
					'url'  => $url,
				);

				var_dump( 'Intercept HTTP request' );

				return array(
					'response'      => array(
						'code'    => 200,
						'message' => 'Success',
					),
					'headers'       => array( 'Content-Type' => 'application/json' ),
					'cookies'       => array(),
					'body'          => '{"success":true}',
					'http_response' => null,
				);
			},
			5,
			3
		);
	}

	public function test_backends() {
		$backends = apply_filters( 'http_bridge_backends', array() );

		$this->assertEquals( count( $backends ), 1 );

		$backend = $backends[0];
		$this->assertSame( $backend->name, 'test-backend' );
		$this->assertSame( $backend->base_url, 'https://example.coop' );
		$this->assertSame( $backend->url( '/api/endpoint' ), 'https://example.coop/api/endpoint' );

		$response = $backend->post( '/api/endpoint', array( 'foo' => 'bar' ) );

		$this->assertTrue( $response['data']['success'] );

		$this->assertSame( self::$request['url'], 'https://example.coop/api/endpoint' );
		$this->assertSame( self::$request['args']['body'], '{"foo":"bar"}' );
		$this->assertTrue( isset( self::$request['args']['headers']['Authorization'] ) );
	}

	public function test_bridges() {
		$bridges = apply_filters( 'forms_bridge_bridges', array() );

		$this->assertEquals( count( $bridges ), 1 );

		$bridge = $bridges[0];
		$this->assertSame( $bridge->name, 'test-bridge' );
		$this->assertSame( $bridge->backend->name, 'test-backend' );
		$this->assertSame( $bridge->endpoint, '/api/endpoint' );

		$response = $bridge->submit( array( 'foo' => 'bar' ) );
		var_dump( $response );
		// $this->assertTrue( $response['data']['success'] );
		//
		// $this->assertSame( self::$request['url'], 'https://example.coop/api/endpoint' );
		// $this->assertTrue( isset( self::$request['args']['headers']['Authorization'] ) );
		//
		// $body = json_decode( self::$request['args']['body'] );
		// $this->assertSame( $body['FOO'], 'bar' );
		// $this->assertSame( $body['a'], 'b' );
	}
}
