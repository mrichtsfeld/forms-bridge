<?php
/**
 * Class FormidableTest
 *
 * @package formsbridge-tests
 */

require_once 'class-base-integration-test.php';

/**
 * Formidable Forms integration test case.
 */
class FormidableTest extends BaseIntegrationTest {
	public const NAME = 'formidable';

	/**
	 * Fetch forms from the database.
	 *
	 * @return object[]
	 */
	protected static function get_forms() {
		return FrmForm::get_published_forms();
	}

	/**
	 * Registers a new form on the database.
	 *
	 * @param object $config Form config.
	 *
	 * @return int Form ID.
	 */
	protected static function add_form( $config ) {
		$form_data = array(
			'name'        => $config->name,
			'description' => '',
			'status'      => 'published',
			'form_key'    => $config->form_key,
		);

		$form_id = FrmForm::create( $form_data );

		if ( ! $form_id ) {
			return null;
		}

		// Add fields if they exist in config
		if ( isset( $config->fields ) && is_array( $config->fields ) ) {
			foreach ( $config->field as $field ) {
				$field_data = array(
					'field_key'     => $field->field_key,
					'name'          => $field->name,
					'description'   => $field->description,
					'required'      => $field->required,
					'options'       => $field->options,
					'field_options' => $field->field_options,
				);

				$field_data['field_options']['draft'] = 0;

				FrmField::create( $field_values );
			}
		}

		return $form_id;
	}

	protected static function delete_form( $form ) {
		return (bool) FrmForm::destroy( $form->id );
	}

	public function test_job_position_form_serialization() {
		$form      = self::get_form( 'Job position' );
		$form_data = $this->serialize_form( $form );

		$fields = $form_data['fields'];
		$this->assertEquals( 7, count( $fields ) );

		$field = $fields[0];
		$this->assertField( $field, 'text' );

		$field = $fields[3];
		$this->assertField( $field, 'textarea' );

		$field = $fields[4];
		$this->assertEquals( 3, count( $field['options'] ) );
		$this->assertField( $field, 'select' );

		$field = $fields[5];
		$this->assertField( $field, 'file', array( 'is_file' => true ) );

		$field = $fields[6];
		$this->assertField(
			$field,
			'checkbox',
			array(
				'basetype' => 'acceptance',
				'schema'   => 'boolean',
				'required' => false,
			)
		);
	}

	public function test_job_position_form_submission_serialization() {
		$form = self::get_form( 'Job position' );

		$form_data = $this->serialize_form( $form );

		$store = self::store();
		foreach ( $store as $name => $object ) {
			if ( 'job-position-submission' === $name ) {
				$submission = $object;
				break;
			}
		}

		if ( ! isset( $submission ) ) {
			throw new Exception( 'Job position submission not found' );
		}

		$payload = $this->serialize_submission( $submission, $form_data );

		$this->assertSame( 'ESMERALDA LAGO', $payload['your-name'] );
		$this->assertSame( '729949531', $payload['your-phone'] );
		$this->assertSame( 'Lorem ipsum dolor sit amer', $payload['applicant_notes'] );
		$this->assertSame( 'Option 2', $payload['position'] );
		$this->assertTrue( $payload['acceptance'] );
	}

	public function test_contact_form_serialization() {
		$form = self::get_form( 'Contact Form' );

		$form_data = $this->serialize_form( $form );

		$fields = $form_data['fields'];
		$this->assertEquals( 13, count( $fields ) );

		$field = $fields[0];
		$this->assertField( $field, 'text' );

		$field = $fields[2];
		$this->assertField( $field, 'tel' );

		$field = $fields[3];
		$this->assertField( $field, 'url', array( 'required' => false ) );

		$field = $fields[9];
		$this->assertEqualSets(
			array(
				'value' => 'm',
				'label' => 'Male',
			),
			$field['options'][0]
		);
		$this->assertField(
			$field,
			'select',
			array(
				'basetype' => 'radio',
				'required' => false,
			)
		);

		$field = $fields[12];
		$this->assertEqualSets(
			array(
				'value' => 'DevOps',
				'label' => 'DevOps',
			),
			$field['options'][2]
		);
		$this->assertField(
			$field,
			'select',
			array(
				'schema'   => 'array',
				'basetype' => 'checkbox',
				'required' => false,
				'is_multi' => true,
			)
		);
	}

	public function test_contact_form_submission_serialization() {
		$form = self::get_form( 'Contact Form' );

		$form_data = $this->serialize_form( $form );

		$store = self::store();
		foreach ( $store as $name => $object ) {
			if ( 'contact-submission' === $name ) {
				$submission = $object;
				break;
			}
		}

		if ( ! isset( $submission ) ) {
			throw new Exception( 'Contact submission not found' );
		}

		$payload = $this->serialize_submission( $submission, $form_data );

		$this->assertSame( 'VICENTA SERRA', $payload['your-name'] );
		$this->assertSame( 'https://www.codeccoop.org', $payload['website'] );
		$this->assertSame( 'm', $payload['gender'] );
		$this->assertEqualSets( array( 'Web development', 'Sys admin' ), $payload['skills'] );
	}

	public function test_form_templates() {
		$this->run_test_form_templates();
	}
}
