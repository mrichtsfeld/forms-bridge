<?php

namespace WPCT_ERP_FORMS\Integrations;

use WPCT_ERP_FORMS\Integrations\Integration;

class WPCF7 extends Integration
{

    public function register()
    {
        add_filter('wpcf7_before_send_mail', function ($form, &$abort, $submission) {
            $this->do_submission($submission, $form);
        }, 10, 3);
    }

    public function serialize_submission($submission, $form = null)
    {
        $data = $submission->get_posted_data();
        $data['id'] = $submission->get_posted_data_hash();

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
}
