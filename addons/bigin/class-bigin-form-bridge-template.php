<?php

namespace FORMS_BRIDGE;

if (!defined('ABSPATH')) {
    exit();
}

class Bigin_Form_Bridge_Template extends Zoho_Form_Bridge_Template
{
    /**
     * Handles the template api name.
     *
     * @var string
     */
    protected $api = 'bigin';

    /**
     * Template default config getter.
     *
     * @return array
     */
    protected static function defaults()
    {
        return wpct_plugin_merge_object(
            [
                'fields' => [
                    [
                        'ref' => '#bridge',
                        'name' => 'scope',
                        'value' => 'ZohoBigin.modules.ALL',
                    ],
                ],
                'bridge' => [
                    'backend' => 'Zoho API',
                    'scope' => 'ZohoBigin.modules.ALL',
                ],
            ],
            parent::defaults(),
            self::schema()
        );
    }

    public function use($fields, $integration)
    {
        add_filter(
            'forms_bridge_template_data',
            function ($data, $template_id) {
                if ($template_id !== $this->id) {
                    return $data;
                }

                $index = array_search(
                    'Tag',
                    array_column($data['bridge']['custom_fields'], 'name')
                );

                if ($index !== false) {
                    $field = &$data['bridge']['custom_fields'][$index];

                    if (!empty($field['value'])) {
                        $tags = array_filter(
                            array_map(
                                'trim',
                                explode(',', strval($field['value']))
                            )
                        );
                        for ($i = 0; $i < count($tags); $i++) {
                            $data['bridge']['custom_fields'][] = [
                                'name' => "Tag[{$i}].name",
                                'value' => $tags[$i],
                            ];
                        }
                    }

                    array_splice($data['bridge']['custom_fields'], $index, 1);
                }

                return $data;
            },
            10,
            2
        );

        return parent::use($fields, $integration);
    }
}
