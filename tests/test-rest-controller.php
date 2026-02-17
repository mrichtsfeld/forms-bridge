<?php
/**
 * Class RESTControllerTest
 *
 * @package formsbridge-tests
 */

use FORMS_BRIDGE\REST_Settings_Controller;

/**
 * REST Controller test case.
 */
class RESTControllerTest extends WP_UnitTestCase {
	/**
	 * Handles the last intercepted http request data.
	 *
	 * @var array
	 */
	private static $request;

	/**
	 * HTTP requests interceptor. Prevent test to access the network and store the request arguments
	 * on the static $request attribute.
	 *
	 * @param mixed  $pre  Initial pre hook value.
	 * @param array  $args Request arguments.
	 * @param string $url  Request URL.
	 *
	 * @return array
	 * @throws Exception If the request fails.
	 */
	public function pre_http_request( $pre, $args, $url ) {
		self::$request = array(
			'args' => $args,
			'url'  => $url,
		);

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
	}

	/**
	 * Forms provider for testing.
	 *
	 * @return array
	 */
	public static function forms_provider() {
		return array(
			array(
				'_id'    => 'gf:1',
				'id'     => '1',
				'title'  => 'test-form',
				'fields' => array(),
			),
		);
	}

	/**
	 * Set up before class.
	 *
	 * @throws Exception In case credential or backend registration fails.
	 */
	public static function set_up_before_class() {
		add_filter( 'forms_bridge_forms', array( self::class, 'forms_provider' ), 10, 0 );

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
			)
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
					array(
						'name'  => 'a',
						'value' => 'b',
					),
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
	}

	/**
	 * Set up.
	 */
	public function set_up() {
		parent::set_up();
		add_filter( 'pre_http_request', array( $this, 'pre_http_request' ), 10, 3 );

		// Set up REST server.
		// global $wp_rest_server;
		// $wp_rest_server = new WP_REST_Server();
	}

	/**
	 * Tear down after class.
	 */
	public static function tear_down_after_class() {
		remove_filter( 'forms_bridge_forms', array( self::class, 'forms_provider' ), 10, 0 );
		FBAPI::delete_backend( 'test-backend' );
		FBAPI::delete_bridge( 'test-bridge', 'rest' );
		FBAPI::delete_credential( 'test-basic' );
	}

	/**
	 * Tear down.
	 */
	public function tear_down() {
		remove_filter( 'pre_http_request', array( $this, 'pre_http_request' ), 10, 3 );
		parent::tear_down();
	}

	/**
	 * Test forms endpoint.
	 */
	public function test_forms_endpoint() {
		// Test GET request to forms endpoint.
		$request  = new WP_REST_Request( 'GET', '/forms-bridge/v1/forms' );
		$response = rest_do_request( $request );

		$this->assertEquals( 200, $response->get_status() );
		$data = $response->get_data();
		$this->assertIsArray( $data );
		$this->assertCount( 1, $data );
		$this->assertEquals( 'test-form', $data[0]['title'] );
	}

	/**
	 * Test HTTP schemas endpoint.
	 */
	public function test_http_schemas_endpoint() {
		// Test GET request to HTTP schemas endpoint.
		$request  = new WP_REST_Request( 'GET', '/forms-bridge/v1/http/schemas' );
		$response = rest_do_request( $request );

		$this->assertEquals( 200, $response->get_status() );
		$data = $response->get_data();
		$this->assertIsArray( $data );
		$this->assertArrayHasKey( 'backend', $data );
		$this->assertEquals( 'object', $data['backend']['type'] );
		$this->assertArrayHasKey( 'name', $data['backend']['properties'] );
		$this->assertArrayHasKey( 'base_url', $data['backend']['properties'] );
		$this->assertArrayHasKey( 'headers', $data['backend']['properties'] );
		$this->assertArrayHasKey( 'credential', $data['backend']['properties'] );
		$this->assertCount( 3, $data['backend']['required'] );
		$this->assertArrayHasKey( 'credential', $data );
		$this->assertArrayHasKey( 'oneOf', $data['credential'] );
		$this->assertCount( 5, $data['credential']['oneOf'] );
	}

	/**
	 * Test REST schemas endpoint.
	 */
	public function test_rest_schemas_endpoint() {
		// Test GET request to REST addon schemas endpoint.
		$request  = new WP_REST_Request( 'GET', '/forms-bridge/v1/rest/schemas' );
		$response = rest_do_request( $request );

		$this->assertEquals( 200, $response->get_status() );
		$data = $response->get_data();
		$this->assertIsArray( $data );
		$this->assertArrayHasKey( 'bridge', $data );
		$this->assertEquals( 'object', $data['bridge']['type'] );
		$this->assertArrayHasKey( 'name', $data['bridge']['properties'] );
		$this->assertArrayHasKey( 'form_id', $data['bridge']['properties'] );
		$this->assertArrayHasKey( 'backend', $data['bridge']['properties'] );
		$this->assertArrayHasKey( 'endpoint', $data['bridge']['properties'] );
		$this->assertArrayHasKey( 'method', $data['bridge']['properties'] );
		$this->assertArrayHasKey( 'custom_fields', $data['bridge']['properties'] );
		$this->assertArrayHasKey( 'mutations', $data['bridge']['properties'] );
		$this->assertArrayHasKey( 'workflow', $data['bridge']['properties'] );
		$this->assertArrayHasKey( 'is_valid', $data['bridge']['properties'] );
		$this->assertArrayHasKey( 'enabled', $data['bridge']['properties'] );
		$this->assertArrayHasKey( 'allow_failure', $data['bridge']['properties'] );
		$this->assertArrayHasKey( 'order', $data['bridge']['properties'] );
		$this->assertCount( 12, $data['bridge']['required'] );
		$this->assertFalse( $data['bridge']['additionalProperties'] );
	}

	/**
	 * Test template endpoints.
	 */
	public function test_template_endpoints() {
		$template_name = 'test-template';
		$template_data = array(
			'name'         => $template_name,
			'title'        => 'Test Template',
			'description'  => 'Test description',
			'fields'       => array(
				array(
					'ref'     => '#bridge',
					'name'    => 'endpoint',
					'label'   => 'Endpoint',
					'type'    => 'text',
					'default' => '/api/posts',
				),
			),
			'form'         => array(
				'title'  => 'Test Form',
				'fields' => array(
					array(
						'name'     => 'foo',
						'label'    => 'Foo',
						'type'     => 'text',
						'required' => true,
					),
				),
			),
			'integrations' => array( 'wpcf7' ),
		);

		// Test POST request to create template.
		$request = new WP_REST_Request( 'POST', '/forms-bridge/v1/rest/templates/' . $template_name );
		$request->set_header( 'Content-Type', 'application/json' );
		$request->set_body( wp_json_encode( $template_data ) );
		$response = rest_do_request( $request );

		$this->assertEquals( 200, $response->get_status() );
		$data = $response->get_data();
		$this->assertArrayHasKey( 'success', $data );
		$this->assertTrue( $data['success'] );

		// Test GET request to retrieve template.
		$request  = new WP_REST_Request( 'GET', '/forms-bridge/v1/rest/templates/' . $template_name );
		$response = rest_do_request( $request );

		$this->assertEquals( 200, $response->get_status() );
		$data = $response->get_data();
		$this->assertEquals( $template_name, $data['name'] );
		$this->assertEquals( 'Test Template', $data['title'] );

		// Test DELETE request to remove template.
		$request  = new WP_REST_Request( 'DELETE', '/forms-bridge/v1/rest/templates/' . $template_name );
		$response = rest_do_request( $request );

		$this->assertEquals( 200, $response->get_status() );

		// Verify template is deleted.
		$request  = new WP_REST_Request( 'GET', '/forms-bridge/v1/rest/templates/' . $template_name );
		$response = rest_do_request( $request );
		$this->assertEquals( 404, $response->get_status() );
	}

	/**
	 * Test template use endpoint.
	 */
	public function test_template_use_endpoint() {
		$template_name = 'test-use-template';
		$template_data = array(
			'name'         => $template_name,
			'title'        => 'Test Use Template',
			'description'  => 'Test use description',
			'fields'       => array(
				array(
					'ref'     => '#bridge',
					'name'    => 'endpoint',
					'label'   => 'Endpoint',
					'type'    => 'text',
					'default' => '/api/posts',
				),
			),
			'form'         => array(
				'title'  => 'Test Form',
				'fields' => array(
					array(
						'name'     => 'foo',
						'label'    => 'Foo',
						'type'     => 'text',
						'required' => true,
					),
				),
			),
			'integrations' => array( 'wpcf7' ),
		);

		// Create template first.
		$request = new WP_REST_Request( 'POST', '/forms-bridge/v1/rest/templates/' . $template_name );
		$request->set_header( 'Content-Type', 'application/json' );
		$request->set_body( wp_json_encode( $template_data ) );
		$response = rest_do_request( $request );

		// Test POST request to use template.
		$request = new WP_REST_Request( 'POST', '/forms-bridge/v1/rest/templates/' . $template_name . '/use' );
		$request->set_header( 'Content-Type', 'application/json' );
		$request->set_body(
			wp_json_encode(
				array(
					'integration' => 'wpcf7',
					'fields'      => array(
						array(
							'ref'   => '#bridge',
							'name'  => 'endpoint',
							'label' => 'Endpoint',
							'value' => '/api/posts',
							'type'  => 'text',
						),
						array(
							'ref'   => '#backend',
							'name'  => 'base_url',
							'label' => 'Base URL',
							'value' => 'https://example.coop',
							'type'  => 'url',
						),
						array(
							'ref'   => '#bridge',
							'name'  => 'name',
							'label' => 'Name',
							'value' => 'Test Bridge',
							'type'  => 'text',
						),
						array(
							'ref'   => '#backend',
							'name'  => 'name',
							'label' => 'Name',
							'value' => 'Test Backend',
							'type'  => 'text',
						),
						array(
							'ref'   => '#form',
							'name'  => 'title',
							'label' => 'Form title',
							'value' => 'Test Form',
							'type'  => 'text',
						),
						array(
							'ref'   => '#form',
							'name'  => 'id',
							'value' => '1',
							'type'  => 'text',
						),
					),
				),
			),
		);
		$response = rest_do_request( $request );

		$this->assertEquals( 200, $response->get_status() );
		$data = $response->get_data();
		$this->assertArrayHasKey( 'success', $data );
		$this->assertTrue( $data['success'] );

		// Clean up.
		$request = new WP_REST_Request( 'DELETE', '/forms-bridge/v1/rest/templates/' . $template_name );
		rest_do_request( $request );
	}

	/**
	 * Test job endpoints.
	 */
	public function test_job_endpoints() {
		$job_name = 'test-job';
		$job_data = array(
			'name'        => $job_name,
			'title'       => 'Test Job',
			'description' => 'Test job description',
			'method'      => 'POST',
			'input'       => array(),
			'output'      => array(),
			'snippet'     => 'function foo( $payload ) { return $payload; }',
		);

		// Test POST request to create job.
		$request = new WP_REST_Request( 'POST', '/forms-bridge/v1/rest/jobs/' . $job_name );
		$request->set_header( 'Content-Type', 'application/json' );
		$request->set_body( wp_json_encode( $job_data ) );
		$response = rest_do_request( $request );

		$this->assertEquals( 200, $response->get_status() );
		$data = $response->get_data();
		$this->assertEquals( $job_name, $data['name'] );
		$this->assertEquals( 'Test Job', $data['title'] );

		// Test GET request to retrieve job.
		$request  = new WP_REST_Request( 'GET', '/forms-bridge/v1/rest/jobs/' . $job_name );
		$response = rest_do_request( $request );

		$this->assertEquals( 200, $response->get_status() );
		$data = $response->get_data();
		$this->assertEquals( $job_name, $data['name'] );

		// Test DELETE request to remove job.
		$request  = new WP_REST_Request( 'DELETE', '/forms-bridge/v1/rest/jobs/' . $job_name );
		$response = rest_do_request( $request );

		$this->assertEquals( 200, $response->get_status() );

		// Verify job is deleted.
		$request  = new WP_REST_Request( 'GET', '/forms-bridge/v1/rest/jobs/' . $job_name );
		$response = rest_do_request( $request );
		$this->assertEquals( 404, $response->get_status() );
	}

	/**
	 * Test jobs workflow endpoint.
	 */
	public function test_jobs_workflow_endpoint() {
		// Test POST request to get jobs workflow.
		$request = new WP_REST_Request( 'POST', '/forms-bridge/v1/rest/jobs/workflow' );
		$request->set_header( 'Content-Type', 'application/json' );
		$request->set_body(
			wp_json_encode(
				array( 'jobs' => array( 'test-job-1', 'test-job-2' ) ),
			),
		);
		$response = rest_do_request( $request );

		$this->assertEquals( 200, $response->get_status() );
		$data = $response->get_data();
		$this->assertIsArray( $data );
		$this->assertCount( 2, $data );
	}

	/**
	 * Test backend ping endpoint.
	 */
	public function test_backend_ping_endpoint() {
		// Test POST request to ping backend.
		$request = new WP_REST_Request( 'POST', '/forms-bridge/v1/rest/backend/ping' );
		$request->set_header( 'Content-Type', 'application/json' );
		$request->set_body(
			wp_json_encode(
				array(
					'backend' => array(
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
				),
			),
		);
		$response = rest_do_request( $request );

		$this->assertEquals( 200, $response->get_status() );
		$data = $response->get_data();
		$this->assertIsArray( $data );
		$this->assertArrayHasKey( 'success', $data );
	}

	/**
	 * Test backend ping endpoint with temp credential.
	 */
	public function test_backend_ping_endpoint_with_temp_credential() {
		// Test POST request to ping backend.
		$request = new WP_REST_Request( 'POST', '/forms-bridge/v1/rest/backend/ping' );
		$request->set_header( 'Content-Type', 'application/json' );
		$request->set_body(
			wp_json_encode(
				array(
					'backend'    => array(
						'name'       => 'test-backend',
						'base_url'   => 'https://example.coop',
						'credential' => 'test-basic-temp',
						'headers'    => array(
							array(
								'name'  => 'Content-Type',
								'value' => 'application/json',
							),
						),
					),
					'credential' => array(
						'name'          => 'test-basic-temp',
						'schema'        => 'Basic',
						'client_id'     => 'foo',
						'client_secret' => 'bar',
					),
				),
			),
		);
		$response = rest_do_request( $request );

		$this->assertEquals( 200, $response->get_status() );
		$data = $response->get_data();
		$this->assertIsArray( $data );
		$this->assertArrayHasKey( 'success', $data );
	}

	/**
	 * Test backend endpoints endpoint with temp credential.
	 */
	public function test_backend_endpoints_endpoint_with_temp_credential() {
		// Test POST request to get backend endpoints.
		$request = new WP_REST_Request( 'POST', '/forms-bridge/v1/rest/backend/endpoints' );
		$request->set_header( 'Content-Type', 'application/json' );
		$request->set_body(
			wp_json_encode(
				array(
					'backend'    => array(
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
					'credential' => array(
						'name'          => 'test-basic-temp',
						'schema'        => 'Basic',
						'client_id'     => 'foo',
						'client_secret' => 'bar',
					),
					'method'     => 'GET',
				),
			),
		);
		$response = rest_do_request( $request );

		$this->assertEquals( 200, $response->get_status() );
		$data = $response->get_data();
		$this->assertIsArray( $data );
	}

	/**
	 * Test backend endpoints endpoint.
	 */
	public function test_backend_endpoints_endpoint() {
		// Test POST request to get backend endpoints.
		$request = new WP_REST_Request( 'POST', '/forms-bridge/v1/rest/backend/endpoints' );
		$request->set_header( 'Content-Type', 'application/json' );
		$request->set_body(
			wp_json_encode(
				array(
					'backend' => array(
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
					'method'  => 'GET',
				),
			),
		);
		$response = rest_do_request( $request );

		$this->assertEquals( 200, $response->get_status() );
		$data = $response->get_data();
		$this->assertIsArray( $data );
	}

	/**
	 * Test backend endpoint schema endpoint.
	 */
	public function test_backend_endpoint_schema_endpoint() {
		// Test POST request to get endpoint schema.
		$request = new WP_REST_Request( 'POST', '/forms-bridge/v1/rest/backend/endpoint/schema' );
		$request->set_header( 'Content-Type', 'application/json' );
		$request->set_body(
			wp_json_encode(
				array(
					'backend'  => array(
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
					'endpoint' => '/api/endpoint',
					'method'   => 'GET',
				),
			),
		);
		$response = rest_do_request( $request );

		$this->assertEquals( 200, $response->get_status() );
		$data = $response->get_data();
		$this->assertIsArray( $data );
	}

	/**
	 * Test error handling.
	 */
	public function test_error_handling() {
		// Test 404 for non-existent template.
		$request  = new WP_REST_Request( 'GET', '/forms-bridge/v1/rest/templates/non-existent' );
		$response = rest_do_request( $request );
		$this->assertEquals( 404, $response->get_status() );

		// Test 404 for non-existent job.
		$request  = new WP_REST_Request( 'GET', '/forms-bridge/v1/rest/jobs/non-existent' );
		$response = rest_do_request( $request );
		$this->assertEquals( 404, $response->get_status() );
	}

	/**
	 * Test invalid template data.
	 */
	public function test_invalid_template_data() {
		$template_name = 'invalid-template';

		// Test POST request with invalid template data.
		$request = new WP_REST_Request( 'POST', '/forms-bridge/v1/rest/templates/' . $template_name );
		$request->set_header( 'Content-Type', 'application/json' );
		$request->set_body(
			wp_json_encode(
				array( // Missing required fields.
					'name' => $template_name,
				),
			),
		);
		$response = rest_do_request( $request );

		$this->assertEquals( 400, $response->get_status() ); // Bad request.
	}

	/**
	 * Test invalid job data.
	 */
	public function test_invalid_job_data() {
		$job_name = 'invalid-job';

		// Test POST request with invalid job data.
		$request = new WP_REST_Request( 'POST', '/forms-bridge/v1/rest/jobs/' . $job_name );
		$request->set_header( 'Content-Type', 'application/json' );
		$request->set_body(
			wp_json_encode(
				array( // Missing required fields.
					'name' => $job_name,
				),
			),
		);
		$response = rest_do_request( $request );

		$this->assertEquals( 400, $response->get_status() ); // Bad request.
	}

	/**
	 * Test backend ping with invalid credentials.
	 */
	public function test_backend_ping_with_invalid_credentials() {
		// Test POST request to ping backend with invalid credentials.
		$request = new WP_REST_Request( 'POST', '/forms-bridge/v1/rest/backend/ping' );
		$request->set_header( 'Content-Type', 'application/json' );
		$request->set_body(
			wp_json_encode(
				array(
					'backend' => array(
						'name'       => 'invalid-backend',
						'base_url'   => 'https://example.coop',
						'credential' => 'invalid-credential',
					),
				),
			),
		);
		$response = rest_do_request( $request );

		$this->assertEquals( 400, $response->get_status() ); // Bad request.
	}

	/**
	 * Test caching behavior.
	 */
	public function test_caching_behavior() {
		// Test that responses are properly cached for introspection endpoints.
		$request1 = new WP_REST_Request( 'POST', '/forms-bridge/v1/rest/backend/ping' );
		$request1->set_header( 'Content-Type', 'application/json' );
		$request1->set_body(
			wp_json_encode(
				array(
					'backend' => array(
						'name'       => 'test-backend',
						'base_url'   => 'https://example.coop',
						'credential' => 'test-basic',
					),
				),
			),
		);
		$response1 = rest_do_request( $request1 );

		$request2 = new WP_REST_Request( 'POST', '/forms-bridge/v1/rest/backend/ping' );
		$request2->set_header( 'Content-Type', 'application/json' );
		$request2->set_body(
			wp_json_encode(
				array(
					'backend' => array(
						'name'       => 'test-backend',
						'base_url'   => 'https://example.coop',
						'credential' => 'test-basic',
					),
				),
			),
		);
		$response2 = rest_do_request( $request2 );

		// Both responses should be identical (cached).
		$this->assertEquals( $response1->get_data(), $response2->get_data() );
	}

	/**
	 * Test template use with invalid integration.
	 */
	public function test_template_use_with_invalid_integration() {
		$template_name = 'test-invalid-integration';
		$template_data = array(
			'name'         => $template_name,
			'title'        => 'Test Invalid Integration',
			'description'  => 'Test invalid integration description',
			'fields'       => array(
				array(
					'ref'     => '#bridge',
					'name'    => 'endpoint',
					'label'   => 'Endpoint',
					'type'    => 'text',
					'default' => '/api/posts',
				),
			),
			'form'         => array(
				'title'  => 'Test Form',
				'fields' => array(
					array(
						'name'     => 'foo',
						'label'    => 'Foo',
						'type'     => 'text',
						'required' => true,
					),
				),
			),
			'integrations' => array( 'wpcf7' ),
		);

		// Create template first.
		$request = new WP_REST_Request( 'POST', '/forms-bridge/v1/rest/templates/' . $template_name );
		$request->set_header( 'Content-Type', 'application/json' );
		$request->set_body( wp_json_encode( $template_data ) );
		rest_do_request( $request );

		// Test POST request to use template with invalid integration.
		$request = new WP_REST_Request( 'POST', '/forms-bridge/v1/rest/templates/' . $template_name . '/use' );
		$request->set_header( 'Content-Type', 'application/json' );
		$request->set_body(
			wp_json_encode(
				array(
					'integration' => 'invalid-integration',
					'fields'      => array(
						array(
							'ref'   => '#bridge',
							'name'  => 'endpoint',
							'label' => 'Endpoint',
							'value' => '/api/posts',
							'type'  => 'text',
						),
						array(
							'ref'   => '#backend',
							'name'  => 'base_url',
							'label' => 'Base URL',
							'value' => 'https://example.coop',
							'type'  => 'url',
						),
						array(
							'ref'   => '#bridge',
							'name'  => 'name',
							'label' => 'Name',
							'value' => 'Test Bridge',
							'type'  => 'text',
						),
						array(
							'ref'   => '#backend',
							'name'  => 'name',
							'label' => 'Name',
							'value' => 'Test Backend',
							'type'  => 'text',
						),
						array(
							'ref'   => '#form',
							'name'  => 'title',
							'label' => 'Form title',
							'value' => 'Test Form',
							'type'  => 'text',
						),
						array(
							'ref'   => '#form',
							'name'  => 'id',
							'value' => '1',
							'type'  => 'text',
						),
					),
				),
			),
		);
		$response = rest_do_request( $request );

		$this->assertEquals( 400, $response->get_status() ); // Bad request.

		// Clean up.
		$request = new WP_REST_Request( 'DELETE', '/forms-bridge/v1/rest/templates/' . $template_name );
		rest_do_request( $request );
	}

	/**
	 * Test endpoint validation.
	 */
	public function test_endpoint_validation() {
		// Test that endpoints validate their parameters correctly.
		$request = new WP_REST_Request( 'POST', '/forms-bridge/v1/rest/backend/endpoint/schema' );
		$request->set_header( 'Content-Type', 'application/json' );
		$request->set_body(
			wp_json_encode(
				array(
					// Missing required parameters.
					'backend' => array( 'name' => 'test-backend' ),
				),
			),
		);
		$response = rest_do_request( $request );

		$this->assertEquals( 400, $response->get_status() ); // Bad request due to missing endpoint parameter.
	}

	/**
	 * Test jobs workflow with empty array.
	 */
	public function test_jobs_workflow_with_empty_array() {
		// Test POST request to get jobs workflow with empty array.
		$request = new WP_REST_Request( 'POST', '/forms-bridge/v1/rest/jobs/workflow' );
		$request->set_header( 'Content-Type', 'application/json' );
		$request->set_body( wp_json_encode( array( 'jobs' => array() ) ) );
		$response = rest_do_request( $request );

		$this->assertEquals( 400, $response->get_status() ); // Bad request - jobs array should have at least one item.
	}

	/**
	 * Test template reset functionality.
	 */
	public function test_template_reset_functionality() {
		$template_name = 'test-reset-template';
		$template_data = array(
			'name'         => $template_name,
			'title'        => 'Test Reset Template',
			'description'  => 'Test reset description',
			'fields'       => array(
				array(
					'ref'     => '#bridge',
					'name'    => 'endpoint',
					'label'   => 'Endpoint',
					'type'    => 'text',
					'default' => '/api/posts',
				),
			),
			'form'         => array(
				'title'  => 'Test Form',
				'fields' => array(
					array(
						'name'     => 'foo',
						'label'    => 'Foo',
						'type'     => 'text',
						'required' => true,
					),
				),
			),
			'integrations' => array( 'wpcf7' ),
		);

		// Create template first.
		$request = new WP_REST_Request( 'POST', '/forms-bridge/v1/rest/templates/' . $template_name );
		$request->set_header( 'Content-Type', 'application/json' );
		$request->set_body( wp_json_encode( $template_data ) );
		rest_do_request( $request );

		// Modify the template.
		$modified_data          = $template_data;
		$modified_data['title'] = 'Modified Template';
		$request                = new WP_REST_Request( 'POST', '/forms-bridge/v1/rest/templates/' . $template_name );
		$request->set_header( 'Content-Type', 'application/json' );
		$request->set_body( wp_json_encode( $modified_data ) );
		rest_do_request( $request );

		// Reset the template.
		$request  = new WP_REST_Request( 'DELETE', '/forms-bridge/v1/rest/templates/' . $template_name );
		$response = rest_do_request( $request );

		$this->assertEquals( 200, $response->get_status() );

		// Verify template is reset (should be empty or default).
		$request  = new WP_REST_Request( 'GET', '/forms-bridge/v1/rest/templates/' . $template_name );
		$response = rest_do_request( $request );
		$this->assertEquals( 404, $response->get_status() ); // Should be not found after reset.
	}
}
