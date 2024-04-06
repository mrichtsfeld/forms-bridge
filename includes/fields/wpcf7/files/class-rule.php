<?php

class WPCT_WPCF7_Files_Rule extends Contactable\SWV\Rule
{
    public const rule_name = 'files';

    public function validate($context)
    {
        $field = $this->get_property('field');
        $input = $_FILES[$field]['name'] ?? '';
        $input = wpcf7_array_flatten($input);
        $input = wpcf7_exclude_blank($input);

        $acceptable_filetypes = [];
        foreach ((array) $this->get_property('accept') as $accept) {
            if (preg_match('/^\.[a-z0-9]+$/i', $accept)) {
                $acceptable_filetypes[] = strtolower($accept);
            } else {
                foreach (wpcf7_convert_mime_to_ext($accept) as $ext) {
                    $acceptable_filetypes[] = sprintf(
                        '.%s',
                        strtolower(trim($ext, ' .'))
                    );
                }
            }
        }

        $acceptable_filetypes = array_unique($acceptable_filetypes);
        foreach ($input as $i) {
            $last_period_pos = strrpos($i, '.');

            if (false === $last_period_pos) { // no period
                return $this->create_error();
            }

            $suffix = strtolower(substr($i, $last_period_pos));
            if (! in_array($suffix, $acceptable_filetypes, true)) {
                return $this->create_error();
            }
        }

        $max = $this->get_property('maxfiles');
        $min = $this->get_property('minfiles');

        if (sizeof($input) > $max) {
			$this->properties['error'] = 'upload_files_max';
            return $this->create_error();
        } elseif (sizeof($input) < $min) {
			$this->properties['error'] = 'upload_files_min';
            return $this->create_error();
        }

		return true;
    }
}
