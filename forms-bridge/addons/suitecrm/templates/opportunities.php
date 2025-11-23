<?php
/**
 * SuiteCRM Opportunities template.
 *
 * @package formsbridge
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit();
}

return array(
	'title'       => __( 'Opportunities', 'forms-bridge' ),
	'description' => __(
		'Opportunity form template. The resulting bridge will convert form submissions into SuiteCRM opportunities (sales deals).',
		'forms-bridge'
	),
	'fields'      => array(
		array(
			'ref'     => '#form',
			'name'    => 'title',
			'default' => __( 'Opportunities', 'forms-bridge' ),
		),
		array(
			'ref'   => '#bridge',
			'name'  => 'endpoint',
			'value' => 'Opportunities',
		),
		array(
			'ref'         => '#bridge/custom_fields[]',
			'name'        => 'assigned_user_id',
			'label'       => __( 'Assigned User', 'forms-bridge' ),
			'description' => __(
				'User to assign the opportunity to',
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
			'name'        => 'account_id',
			'label'       => __( 'Account', 'forms-bridge' ),
			'description' => __(
				'Related account for this opportunity',
				'forms-bridge'
			),
			'type'        => 'select',
			'options'     => array(
				'endpoint' => 'Accounts',
				'finger'   => array(
					'value' => 'entry_list.[].id',
					'label' => 'entry_list.[].name_value_list.name.value',
				),
			),
		),
		array(
			'ref'         => '#bridge/custom_fields[]',
			'name'        => 'sales_stage',
			'label'       => __( 'Sales Stage', 'forms-bridge' ),
			'description' => __(
				'Current stage in the sales process',
				'forms-bridge'
			),
			'type'        => 'select',
			'options'     => array(
				array(
					'value' => 'Prospecting',
					'label' => __( 'Prospecting', 'forms-bridge' ),
				),
				array(
					'value' => 'Qualification',
					'label' => __( 'Qualification', 'forms-bridge' ),
				),
				array(
					'value' => 'Needs Analysis',
					'label' => __( 'Needs Analysis', 'forms-bridge' ),
				),
				array(
					'value' => 'Value Proposition',
					'label' => __( 'Value Proposition', 'forms-bridge' ),
				),
				array(
					'value' => 'Id. Decision Makers',
					'label' => __( 'Identifying Decision Makers', 'forms-bridge' ),
				),
				array(
					'value' => 'Perception Analysis',
					'label' => __( 'Perception Analysis', 'forms-bridge' ),
				),
				array(
					'value' => 'Proposal/Price Quote',
					'label' => __( 'Proposal/Price Quote', 'forms-bridge' ),
				),
				array(
					'value' => 'Negotiation/Review',
					'label' => __( 'Negotiation/Review', 'forms-bridge' ),
				),
				array(
					'value' => 'Closed Won',
					'label' => __( 'Closed Won', 'forms-bridge' ),
				),
				array(
					'value' => 'Closed Lost',
					'label' => __( 'Closed Lost', 'forms-bridge' ),
				),
			),
			'default'     => 'Prospecting',
		),
		array(
			'ref'         => '#bridge/custom_fields[]',
			'name'        => 'lead_source',
			'label'       => __( 'Lead Source', 'forms-bridge' ),
			'description' => __(
				'Source of the opportunity',
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
					'value' => 'Existing Customer',
					'label' => __( 'Existing Customer', 'forms-bridge' ),
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
					'value' => 'Self Generated',
					'label' => __( 'Self Generated', 'forms-bridge' ),
				),
				array(
					'value' => 'Other',
					'label' => __( 'Other', 'forms-bridge' ),
				),
			),
			'default'     => 'Web Site',
		),
		array(
			'ref'         => '#bridge/custom_fields[]',
			'name'        => 'opportunity_type',
			'label'       => __( 'Opportunity Type', 'forms-bridge' ),
			'description' => __(
				'Type of business opportunity',
				'forms-bridge'
			),
			'type'        => 'select',
			'options'     => array(
				array(
					'value' => 'Existing Business',
					'label' => __( 'Existing Business', 'forms-bridge' ),
				),
				array(
					'value' => 'New Business',
					'label' => __( 'New Business', 'forms-bridge' ),
				),
			),
			'default'     => 'New Business',
		),
	),
	'bridge'      => array(
		'endpoint'      => 'Opportunities',
		'method'        => 'set_entry',
		'custom_fields' => array(
			array(
				'name'  => 'sales_stage',
				'value' => 'Prospecting',
			),
			array(
				'name'  => 'lead_source',
				'value' => 'Web Site',
			),
			array(
				'name'  => 'opportunity_type',
				'value' => 'New Business',
			),
		),
		'mutations'     => array(
			array(
				array(
					'from' => 'name',
					'to'   => 'name',
					'cast' => 'string',
				),
				array(
					'from' => '?amount',
					'to'   => 'amount',
					'cast' => 'string',
				),
				array(
					'from' => '?currency',
					'to'   => 'currency_id',
					'cast' => 'string',
				),
				array(
					'from' => '?date_closed',
					'to'   => 'date_closed',
					'cast' => 'string',
				),
				array(
					'from' => '?probability',
					'to'   => 'probability',
					'cast' => 'string',
				),
				array(
					'from' => '?next_step',
					'to'   => 'next_step',
					'cast' => 'string',
				),
				array(
					'from' => '?description',
					'to'   => 'description',
					'cast' => 'string',
				),
				array(
					'from' => '?sales_stage',
					'to'   => 'sales_stage',
					'cast' => 'string',
				),
				array(
					'from' => '?lead_source',
					'to'   => 'lead_source',
					'cast' => 'string',
				),
				array(
					'from' => '?opportunity_type',
					'to'   => 'opportunity_type',
					'cast' => 'string',
				),
				array(
					'from' => '?account_id',
					'to'   => 'account_id',
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
				'label'    => __( 'Opportunity Name', 'forms-bridge' ),
				'name'     => 'name',
				'type'     => 'text',
				'required' => true,
			),
			array(
				'label'    => __( 'Amount', 'forms-bridge' ),
				'name'     => 'amount',
				'type'     => 'number',
				'required' => true,
			),
			array(
				'label'       => __( 'Expected Close Date', 'forms-bridge' ),
				'name'        => 'date_closed',
				'type'        => 'date',
				'required'    => true,
				'description' => __( 'Format: YYYY-MM-DD', 'forms-bridge' ),
			),
			array(
				'label'       => __( 'Probability (%)', 'forms-bridge' ),
				'name'        => 'probability',
				'type'        => 'number',
				'description' => __( 'Likelihood of closing (0-100)', 'forms-bridge' ),
			),
			array(
				'label' => __( 'Next Step', 'forms-bridge' ),
				'name'  => 'next_step',
				'type'  => 'text',
			),
			array(
				'label' => __( 'Description', 'forms-bridge' ),
				'name'  => 'description',
				'type'  => 'textarea',
			),
		),
	),
);
