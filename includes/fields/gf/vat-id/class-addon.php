<?php

namespace WPCT_ERP_FORMS\GF\Fields\VatID;

use GFForms;
use GFAddOn;
use GF_Fields;

GFForms::include_addon_framework();

class Addon extends GFAddOn
{
    protected $_version = '1.0';
    protected $_slug = 'wpct-erp-forms-vat-id-field';
    protected $_title = 'Gravity Forms VatID validated text field';
    protected $_short_title = 'VatID field';
	protected $_full_path;

    /**
     * @var object $_instance If available, contains an instance of this class.
     */
    private static $_instance = null;

    /**
     * Returns an instance of this class, and stores it in the $_instance property.
     *
     * @return object $_instance An instance of this class.
     */
    public static function get_instance()
    {
        if (self::$_instance == null) {
            self::$_instance = new self();
        }

        return self::$_instance;
    }

    public function __construct()
    {
        $this->_full_path = __FILE__;
        parent::__construct();
    }

    /**
     * Include the field early so it is available when entry exports are being performed.
     */
    public function pre_init()
    {
        parent::pre_init();
        if (
            $this->is_gravityforms_supported() &&
            class_exists('GF_Field') &&
            class_exists('GF_Fields')
        ) {
            GF_Fields::register(new GFField());
        }
    }
}
