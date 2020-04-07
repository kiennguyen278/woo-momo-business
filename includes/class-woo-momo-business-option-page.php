<?php

if (!class_exists('Woo_MoMo_Business_Option_Page')) :
    class Woo_MoMo_Business_Option_Page
    {
        private $Woo_MoMo_Business_Options;

        public function __construct()
        {
            add_action('admin_menu', array($this, 'register_options_page'));
            register_activation_hook(__FILE__, array($this, 'option_page_data'));
            $this->Woo_MoMo_Business_Options = get_option('woo_momo_business');
        }

        function register_options_page()
        {
            add_options_page('MoMo for WooCommerce', 'Woo MoMo Business', 'manage_options', 'momo-business-for-woo-option', array(
                $this,
                'option_page'
            ));
        }

        function option_page()
        {
            require_once('admin/option-page.php');
        }

        function option_page_data()
        {
            $Woo_MoMo_Business_Options = array();
            $Woo_MoMo_Business_Options["license_key"] = "";
            add_option('woo_momo_business', $Woo_MoMo_Business_Options);
        }

    }

endif;
