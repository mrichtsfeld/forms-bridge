<?php
/**
 * Airtable addon settings template.
 *
 * @package formsbridge
 */

return array(
	'title'       => 'Airtable Settings',
	'description' => 'Configure the Airtable integration.',
	'data'        => array(
		'title'       => 'Airtable Settings',
		'description' => 'Configure the Airtable integration.',
		'bridges'     => array(
			array(
				'name'      => 'airtable_bridge',
				'title'     => 'Airtable Bridge',
				'form_id'   => '',
				'backend'   => '',
				'method'    => 'POST',
				'endpoint'  => '',
				'enabled'   => true,
				'mutations' => array(),
				'workflow'  => array(),
			),
		),
	),
);
