<?php
/**
 * Slack addon support channel bridge template
 *
 * @package formsbridge
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit();
}

return array(
	'title'       => __( 'Support Channel', 'forms-bridge' ),
	'description' => __(
		'Support form template. The resulting bridge will notify form submissions in a Slack channel',
		'forms-bridge'
	),
	'fields'      => array(
		array(
			'ref'   => '#bridge',
			'name'  => 'endpoint',
			'value' => '/api/chat.postMessage',
		),
		array(
			'ref'     => '#form',
			'name'    => 'title',
			'default' => __( 'Support', 'forms-bridge' ),
		),
	),
	'form'        => array(
		'title'  => __( 'Support', 'forms-bridge' ),
		'fields' => array(
			array(
				'name'     => 'your-name',
				'label'    => __( 'Your name', 'forms-bridge' ),
				'type'     => 'text',
				'required' => true,
			),
			array(
				'name'     => 'your-email',
				'label'    => __( 'Your email', 'forms-bridge' ),
				'type'     => 'email',
				'required' => true,
			),
			array(
				'name'  => 'comments',
				'label' => __( 'Comments', 'forms-bridge' ),
				'type'  => 'textarea',
			),
		),
	),
	'bridge'      => array(
		'endpoint'  => '/api/chat.postMessage',
		'mutations' => array(
			array(
				array(
					'from' => 'your-name',
					'to'   => 'text.name',
					'cast' => 'string',
				),
				array(
					'from' => 'your-email',
					'to'   => 'text.email',
					'cast' => 'string',
				),
				array(
					'from' => '?comments',
					'to'   => 'text.comments',
					'cast' => 'string',
				),
				array(
					'from' => 'text',
					'to'   => 'text',
					'cast' => 'pretty_json',
				),
			),
		),
	),
);
