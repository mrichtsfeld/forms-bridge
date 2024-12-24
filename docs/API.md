# API

## Table of Contents

1. [Getters](#getters)
2. [Filters](#filters)
3. [Actions](#actions)

## Getters

### `forms_bridge_form`

Get the current form.

#### Arguments

1. `any $default`: Default value.
2. `integer $form_id`: If declared, try to return form by ID instead of the current form.

#### Returns

1. `array|null $form_data`: Form data.

#### Example

```php
$form_data = apply_filters('forms_bridge_form', null);
if (!empty($form)) {
	// do something
}
```

### `forms_bridge_forms`

Get available forms.

#### Arguments

1. `mixed $default`: Fallback value.

#### Returns

1. `array $forms_data`: Available forms as list of form data.

#### Example

```php
$forms_data = apply_filters('forms_bridge_forms', []);
foreach ($forms_data as $form_data) {
	// do something
}
```

### `forms_bridge_form_hooks`

Get available form hooks for the current form.

#### Arguments

1. `mixed $default`: Fallback value.
2. `integer $form_id`: If declared, try to return form hooks from the form with this id instead of the form hooks from the current form.

#### Returns

1. `array $hooks`: List of given form available hooks instances.

#### Example

```php
$hooks = apply_filters('forms_bridge_form_hooks', [], 13);
foreach ($hooks as $hook) {
	// do something
}
```

### `forms_bridge_submission`

Gets the current form submission.

#### Arguments

1. `mixed $default`: Fallback value.

#### Returns

1. `array|null $submission`: Current form's submission data.

#### Example

```php
$submission = apply_filters('forms_bridge_submission', null);
if ($submission) {
	// do something
}
```

### `forms_bridge_uploads`

Gets the current form's submission uploaded files.

#### Arguments

1. `mixed $default`: Fallback value.

#### Returns

1. `array|null`: Current form's submission uploaded files.

#### Example

```php
$uploads = apply_filters('forms_bridge_uploads', []);
foreach ($uploads as $uplad) {
	// do something
}
```

## Filters

### `forms_bridge_payload`

Filters the submission data to be sent to the backend.

#### Arguments

1. `array $payload`: Submission payload.
2. `array $uploads`: Submission uploaded files.
3. `array $form_data`: Form data.
4. `array $hook`: Hook data.

#### Example

```php
add_filter('forms_bridge_payload', function ($payload, $uploads, $form_data, $form_hook) {
	return $payload;
}, 10, 4);
```

### `forms_bridge_payload_{$hook_name}`

Filters the submission data to be sent to the backend for a given form hook.

#### Arguments

1. `array $payload`: Submission payload.
2. `array $uploads`: Submission uploaded files.
3. `array $form_data`: Form data.

#### Example

```php
add_filter('forms_bridge_payload_contact', function ($payload, $uploads, $form_data) {
	return $payload;
}, 10, 3);
```

### `forms_bridge_attachments`

Filters attached files to be sent to the backend.

#### Arguments

1. `array $uploads`: Submission attached files.
2. `array $form_data`: Form data.
3. `array $form_hook`: Form hook data.

#### Example

```php
add_filter('forms_bridge_attachments', function ($attachments, $form_data, $form_hook) {
	return $attachments;
}, 10, 3);
```

### `forms_bridge_attachments_{$hook_name}`

Filters attached files to be sent to the backend for a given form hook.

#### Arguments

1. `array $uploads`: Submission attached files.
2. `array $form_data`: Form data.

#### Example

```php
add_filter('forms_bridge_attachments_contact', function ($attachments, $form_data) {
	return $attachments;
}, 10, 2);
```

### `forms_bridge_prune_empties`

Control if Forms Bridge should clean up the submission data and prune its empty fields.

#### Arguments

1. `boolean $prune`: False by default.

#### Example

```php
add_filter('forms_bridge_prune_empties', '__return_true');
```

## Actions

### `forms_bridge_before_submission`

Action to do just before submission has been sent to the backend.

#### Arguments

1. `array $payload`: Submission prepared payload data.
2. `array $attachments`: Submission attached files.
3. `array $form_data`: Form data.

#### Example

```php
add_action('forms_bridge_before_submission', function ($payload, $attachments, $form_data) {
	// do something
}, 10, 3);
```

### `forms_bridge_after_submission`

Action to do after the submission has been succesfuly sent to the backend.

#### Arguments

1. `array $response`: HTTP response data
2. `array $payload`: Submission payload.
2. `array $attachments`: Submission attached files.
3. `array $form_data`: Form data.

#### Example

```php
add_action('forms_bridge_after_submission', function ($payload, $attachments, $form_data) {
	// do something
}, 10, 3);
```

### `forms_bridge_on_failure`

Action to do after a request connexion error with the backend.

#### Arguments

1. `array $payload`: Submission payload.
2. `array $attachments`: Submission attached files.
3. `array $form_data`: Form data.
4. `array $error_data`: Error data.

#### Example

```php
add_action('forms_bridge_on_failure', function ($payload, $attachments, $form_data, $error_data) {
	// do something
}, 10, 4);
```

### `forms_bridge_before_submit`

Fired before Forms Bridge submits data to a REST API.

#### Arguments

1. `string $endpoint`: Target endpoint.
2. `array $submission`: Submission data.
3. `array $attachments`: Array of attached files.
4. `Form_Hook $hook`: Instance of the form hook who triggers the request.

### `forms_bridge_after_submit`

Fired with the response data from the REST API.