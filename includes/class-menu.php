<?php

namespace WPCT_ERP_FORMS;

use WPCT_ABSTRACT\Menu as BaseMenu;

class Menu extends BaseMenu
{
	static protected $settings_class = '\WPCT_ERP_FORMS\Settings';

    protected function render_page()
    {
        ob_start();
        ?>
        <div class="wrap">
            <h1><?= $this->name ?></h1>
            <form action="options.php" method="post">
            <?php
            settings_fields($this->settings->get_group_name());
			do_settings_sections($this->settings->get_group_name());
			submit_button();
			?>
            </form>
        </div>
<?php
        $output = ob_get_clean();
        echo apply_filters('wpct_epr_forms_menu_page_content', $output);
    }
}
