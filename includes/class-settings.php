<?php

namespace WPCT_ERP_FORMS;

class Settings extends Abstract\Settings
{
    private $_default_endpoint = '/api/private/crm-lead';

    public function register()
    {
        $setting_name = $this->group_name . '_general';
        $this->register_setting(
            $setting_name,
            [
                'notification_receiver' => 'admin@example.coop'
            ],
        );

        $this->register_field('notification_receiver', $setting_name);

        $setting_name = $this->group_name . '_api';
        $this->register_setting(
            $setting_name,
            [
                'endpoints' => [
                    [
                        'form_id' => 0,
                        'endpoint' => $this->_default_endpoint,
                    ],
                ],
            ],
        );

        $this->register_field('endpoints', $setting_name);
    }
}
