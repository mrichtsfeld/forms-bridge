<?php
/**
 * SuiteCRM Leads template.
 *
 * @package formsbridge
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit();
}

return array(
	'title'       => __( 'Leads', 'forms-bridge' ),
	'description' => __(
		'Lead capture form template. The resulting bridge will convert form submissions into SuiteCRM leads.',
		'forms-bridge'
	),
	'fields'      => array(
		array(
			'ref'     => '#form',
			'name'    => 'title',
			'default' => __( 'Leads', 'forms-bridge' ),
		),
		array(
			'ref'   => '#bridge',
			'name'  => 'endpoint',
			'value' => 'Leads',
		),
		array(
			'ref'         => '#bridge/custom_fields[]',
			'name'        => 'assigned_user_id',
			'label'       => __( 'Assigned User', 'forms-bridge' ),
			'description' => __(
				'User to assign the lead to',
				'forms-bridge'
			),
			'type'        => 'select',
			'options'     => array(
				'endpoint' => 'Users',
				'finger'   => array(
					'value' => 'entry_list.[].id',
					'label' => 'entry_list.[].name_value_list.user_name.value',
				),
			),
		),
		array(
			'ref'         => '#bridge/custom_fields[]',
			'name'        => 'status',
			'label'       => __( 'Lead Status', 'forms-bridge' ),
			'description' => __(
				'Initial status of the lead',
				'forms-bridge'
			),
			'type'        => 'select',
			'options'     => array(
				array(
					'value' => 'New',
					'label' => __( 'New', 'forms-bridge' ),
				),
				array(
					'value' => 'Assigned',
					'label' => __( 'Assigned', 'forms-bridge' ),
				),
				array(
					'value' => 'In Process',
					'label' => __( 'In Process', 'forms-bridge' ),
				),
				array(
					'value' => 'Converted',
					'label' => __( 'Converted', 'forms-bridge' ),
				),
				array(
					'value' => 'Recycled',
					'label' => __( 'Recycled', 'forms-bridge' ),
				),
				array(
					'value' => 'Dead',
					'label' => __( 'Dead', 'forms-bridge' ),
				),
			),
			'default'     => 'New',
		),
		array(
			'ref'         => '#bridge/custom_fields[]',
			'name'        => 'lead_source',
			'label'       => __( 'Lead Source', 'forms-bridge' ),
			'description' => __(
				'Source of the lead',
				'forms-bridge'
			),
			'type'        => 'select',
			'options'     => array(
				array(
					'value' => 'Web Site',
					'label' => __( 'Web Site', 'forms-bridge' ),
				),
				array(
					'value' => 'Cold Call',
					'label' => __( 'Cold Call', 'forms-bridge' ),
				),
				array(
					'value' => 'Email',
					'label' => __( 'Email', 'forms-bridge' ),
				),
				array(
					'value' => 'Word of mouth',
					'label' => __( 'Word of Mouth', 'forms-bridge' ),
				),
				array(
					'value' => 'Campaign',
					'label' => __( 'Campaign', 'forms-bridge' ),
				),
				array(
					'value' => 'Conference',
					'label' => __( 'Conference', 'forms-bridge' ),
				),
				array(
					'value' => 'Trade Show',
					'label' => __( 'Trade Show', 'forms-bridge' ),
				),
				array(
					'value' => 'Partner',
					'label' => __( 'Partner', 'forms-bridge' ),
				),
				array(
					'value' => 'Other',
					'label' => __( 'Other', 'forms-bridge' ),
				),
			),
			'default'     => 'Web Site',
		),
	),
	'bridge'      => array(
		'endpoint'      => 'Leads',
		'method'        => 'set_entry',
		'custom_fields' => array(
			array(
				'name'  => 'status',
				'value' => 'New',
			),
			array(
				'name'  => 'lead_source',
				'value' => 'Web Site',
			),
		),
		'mutations'     => array(
			array(
				array(
					'from' => 'first_name',
					'to'   => 'first_name',
					'cast' => 'string',
				),
				array(
					'from' => 'last_name',
					'to'   => 'last_name',
					'cast' => 'string',
				),
				array(
					'from' => 'email',
					'to'   => 'email1',
					'cast' => 'string',
				),
				array(
					'from' => '?phone',
					'to'   => 'phone_work',
					'cast' => 'string',
				),
				array(
					'from' => '?mobile',
					'to'   => 'phone_mobile',
					'cast' => 'string',
				),
				array(
					'from' => '?company',
					'to'   => 'account_name',
					'cast' => 'string',
				),
				array(
					'from' => '?title',
					'to'   => 'title',
					'cast' => 'string',
				),
				array(
					'from' => '?department',
					'to'   => 'department',
					'cast' => 'string',
				),
				array(
					'from' => '?description',
					'to'   => 'description',
					'cast' => 'string',
				),
				array(
					'from' => '?website',
					'to'   => 'website',
					'cast' => 'string',
				),
				array(
					'from' => '?address',
					'to'   => 'primary_address_street',
					'cast' => 'string',
				),
				array(
					'from' => '?city',
					'to'   => 'primary_address_city',
					'cast' => 'string',
				),
				array(
					'from' => '?state',
					'to'   => 'primary_address_state',
					'cast' => 'string',
				),
				array(
					'from' => '?postal_code',
					'to'   => 'primary_address_postalcode',
					'cast' => 'string',
				),
				array(
					'from' => '?country',
					'to'   => 'primary_address_country',
					'cast' => 'string',
				),
				array(
					'from' => '?status',
					'to'   => 'status',
					'cast' => 'string',
				),
				array(
					'from' => '?lead_source',
					'to'   => 'lead_source',
					'cast' => 'string',
				),
				array(
					'from' => '?assigned_user_id',
					'to'   => 'assigned_user_id',
					'cast' => 'string',
				),
			),
		),
	),
	'form'        => array(
		'fields' => array(
			array(
				'label'    => __( 'First Name', 'forms-bridge' ),
				'name'     => 'first_name',
				'type'     => 'text',
				'required' => true,
			),
			array(
				'label'    => __( 'Last Name', 'forms-bridge' ),
				'name'     => 'last_name',
				'type'     => 'text',
				'required' => true,
			),
			array(
				'label'    => __( 'Email', 'forms-bridge' ),
				'name'     => 'email',
				'type'     => 'email',
				'required' => true,
			),
			array(
				'label' => __( 'Phone', 'forms-bridge' ),
				'name'  => 'phone',
				'type'  => 'tel',
			),
			array(
				'label' => __( 'Company', 'forms-bridge' ),
				'name'  => 'company',
				'type'  => 'text',
			),
			array(
				'label' => __( 'Website', 'forms-bridge' ),
				'name'  => 'website',
				'type'  => 'url',
			),
			array(
				'label' => __( 'Message', 'forms-bridge' ),
				'name'  => 'description',
				'type'  => 'textarea',
			),
		),
	),
);
