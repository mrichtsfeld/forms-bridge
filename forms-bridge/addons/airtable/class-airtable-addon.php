<?php

namespace FORMS_BRIDGE;

if ( ! defined( 'ABSPATH' ) ) {
	exit();
}

require_once 'class-airtable-form-bridge.php';

/**
 * Airtable addon class.
 */
class Airtable_Addon extends Addon {

	/**
	 * Handles the addon's title.
	 *
	 * @var string
	 */
	const TITLE = 'Airtable';

	/**
	 * Handles the addon's name.
	 *
	 * @var string
	 */
	const NAME = 'airtable';

	/**
	 * Handles the addon's custom bridge class.
	 *
	 * @var string
	 */
	const BRIDGE = '\FORMS_BRIDGE\Airtable_Form_Bridge';

	/**
	 * Performs a request against the backend to check the connection status.
	 *
	 * @param string $backend Backend name.
	 *
	 * @return boolean
	 */
	public function ping( $backend ) {
		$bridge = new Airtable_Form_Bridge(
			array(
				'name'     => '__airtable-' . time(),
				'backend'  => $backend,
				'endpoint' => '/',
				'method'   => 'GET',
			)
		);

		$backend = $bridge->backend;
		if ( ! $backend ) {
			Logger::log( 'Airtable backend ping error: Bridge has no valid backend', Logger::ERROR );
			return false;
		}

		$credential = $backend->credential;
		if ( ! $credential ) {
			Logger::log( 'Airtable backend ping error: Backend has no valid credential', Logger::ERROR );
			return false;
		}

		$parsed = wp_parse_url( $backend->base_url );
		$host   = $parsed['host'] ?? '';

		if ( 'api.airtable.com' !== $host ) {
			Logger::log( 'Airtable backend ping error: Backend does not point to the Airtable API endpoints', Logger::ERROR );
			return false;
		}

		$access_token = $credential->get_access_token();

		if ( ! $access_token ) {
			Logger::log( 'Airtable backend ping error: Unable to recover the credential access token', Logger::ERROR );
			return false;
		}

		return true;
	}

	/**
	 * Performs a GET request against the backend endpoint and retrive the response data.
	 *
	 * @param string $endpoint Airtable endpoint.
	 * @param string $backend Backend name.
	 *
	 * @return array|WP_Error
	 */
	public function fetch( $endpoint, $backend ) {
		$backend = FBAPI::get_backend( $backend );
		if ( ! $backend ) {
			return new WP_Error( 'invalid_backend' );
		}

		$credential = $backend->credential;
		if ( ! $credential ) {
			return new WP_Error( 'invalid_credential' );
		}

		$access_token = $credential->get_access_token();
		if ( ! $access_token ) {
			return new WP_Error( 'invalid_credential' );
		}

		$response = http_bridge_get(
			'https://api.airtable.com/v0/meta/bases',
			array(),
			array(
				'Authorization' => "Bearer {$access_token}",
				'Accept'        => 'application/json',
			)
		);

		if ( is_wp_error( $response ) ) {
			return $response;
		}

		return $response;
	}

	/**
	 * Performs an introspection of the backend API and returns a list of available endpoints.
	 *
	 * @param string      $backend Target backend name.
	 * @param string|null $method HTTP method.
	 *
	 * @return array|WP_Error
	 */
	public function get_endpoints( $backend, $method = null ) {
		$response = $this->fetch( null, $backend );

		if ( is_wp_error( $response ) || empty( $response['data']['bases'] ) ) {
			return array();
		}

		return array_map(
			function ( $base ) {
				return '/v0/' . $base['id'] . '/' . $base['tables'][0]['id'];
			},
			$response['data']['bases']
		);
	}

	/**
	 * Performs an introspection of the backend endpoint and returns API fields
	 * and accepted content type.
	 *
	 * @param string      $endpoint Airtable endpoint.
	 * @param string      $backend Backend name.
	 * @param string|null $method HTTP method.
	 *
	 * @return array List of fields and content type of the endpoint.
	 */
	public function get_endpoint_schema( $endpoint, $backend, $method = null ) {
		if ( 'POST' !== $method ) {
			return array();
		}

		$bridge  = null;
		$bridges = FBAPI::get_addon_bridges( self::NAME );
		foreach ( $bridges as $candidate ) {
			$data = $candidate->data();
			if ( ! $data ) {
				continue;
			}

			if (
				$data['endpoint'] === $endpoint &&
				$data['backend'] === $backend
			) {
				/**
				 * Current bridge.
				 *
				 * @var Airtable_Form_Bridge
				 */
				$bridge = $candidate;
			}
		}

		if ( ! isset( $bridge ) ) {
			return array();
		}

		$fields = $bridge->get_fields();

		if ( is_wp_error( $fields ) ) {
			return array();
		}

		$schema = array();
		foreach ( $fields as $field ) {
			$schema[] = array(
				'name'   => $field['name'],
				'schema' => array( 'type' => $field['type'] ),
			);
		}

		return $schema;
	}
}

Airtable_Addon::setup();
