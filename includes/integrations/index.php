<?php

require_once 'Integration.php';

if (defined('WPCF7_VERSION')) {
    require_once 'contactform7/index.php';
}

if (class_exists('GFForms')) {
    require_once 'gravityforms/index.php';
}
