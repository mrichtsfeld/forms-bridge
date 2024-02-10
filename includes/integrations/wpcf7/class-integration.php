<?php

namespace WPCT_ERP_FORMS\WPCF7;

use WPCT_ERP_FORMS\Abstract\Integration as BaseIntegration;
use WPCT_ERP_FORMS\WPCF7\Fields\Iban\Field as IbanField;
use WPCT_ERP_FORMS\WPCF7\Fields\Conditional\Field as ConditionalField;

// Fields
require_once dirname(__FILE__, 3) . '/fields/wpcf7/iban/class-field.php';
require_once dirname(__FILE__, 3) . '/fields/wpcf7/conditional/class-field.php';

class Integration extends BaseIntegration
{
    public static $fields = [
        IbanField::class,
        ConditionalField::class
    ];

    protected function __construct()
    {
        parent::__construct();

        add_filter('wpcf7_before_send_mail', function ($form, &$abort, $submission) {
            $this->do_submission($submission, $form);
        }, 10, 3);

        add_filter('wpcf7_form_elements', function ($tags) {
            $plugin_url = plugin_dir_url(dirname(__FILE__, 4) . '/wpct-erp-forms.php');
            $script_url = $plugin_url . 'assets/js/conditional-fields.js';
            $style_url = $plugin_url . 'assets/css/wpct7-theme.css';
            ob_start();
?>
            <script src="<?= $script_url ?>" type="module"></script>
            <link rel="stylesheet" href="<?= $style_url ?>" />
            <style>
                .wpcf7-form-control-conditional-wrap {
                    display: none
                }
            </style>
<?php
            $assets = ob_get_clean();
            return $tags . $assets;
        }, 90, 1);
    }

    public function serialize_submission($submission, $form = null)
    {
        $data = $submission->get_posted_data();
        $data['id'] = $submission->get_posted_data_hash();

        $contact_form = $submission->get_contact_form();
        foreach (array_keys($data) as $field) {
            $value = $data[$field];
            $tag = array_shift($contact_form->scan_form_tags('name=' . $field));
            if (!$tag) continue;

            $type = array_shift(array_map(function ($opt) {
                return substr($opt, 5);
            }, array_filter($tag->options, function ($opt) {
                return strstr($opt, 'type:');
            })));

            if (!$type) continue;

            if ($type === 'text' && $value === 'wpct-empty') {
                $data[$field] = null;
            } else if ($type === 'date' && $value === '0001-01-01') {
                $data[$field] = null;
            } else if ($type === 'tel' && $value === '+000000000') {
                $data[$field] = null;
            } else if ($type === 'number' && $value == -1234567890) {
                $data[$field] = null;
            } else if ($type === 'url' && $value === 'https://wpct-empty.com') {
                $data[$field] = null;
            } else if ($type === 'email' && $value === 'wpct-empty@mail.com') {
                $data[$field] = null;
            } else if (in_array($type, ['checkbox', 'select', 'radio']) && $value[0] === 'wpct-empty') {
                $data[$field] = null;
            }
        }

        return $data;
    }

    public function serialize_form($form)
    {
        return [
            'id' => $form->id(),
            'title' => $form->title(),
            'name' => $form->name(),
            'properties' => $form->get_properties(),
            'tag' => $form->unit_tag(),
            'locale' => $form->locale(),
        ];
    }

    public function get_files($submission, $form)
    {
        $files = [];
        $uploads = $submission->uploaded_files();
        foreach ($uploads as $file_name => $paths) {
            $files[$file_name] = $paths[0];
        };

        return $files;
    }
}
