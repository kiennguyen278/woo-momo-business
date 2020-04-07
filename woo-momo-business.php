<?php
/**
 * Plugin Name: MoMo Business for WooCommerce
 * Author: Nhựt FS
 * Author URI: https://nhutfs.net
 * Description: Add MoMo payment gateway for WooCommerce
 * Version: 1.0.2
 * Text Domain: woo-momo-business
 */

if (!defined('ABSPATH')) {
    exit;
}

if (!in_array('woocommerce/woocommerce.php', apply_filters('active_plugins', get_option('active_plugins')))) {
    return;
}

if (!class_exists('WooMoMoBusiness')) :
    class WooMoMoBusiness
    {
        protected $Option_Page;

        /**
         * WooMoMoBusiness constructor.
         */
        function __construct()
        {
            add_action('init', array($this, 'initialize'));
        }

        function initialize()
        {
            //defines
            $this->define('WOO_MOMO_BUSINESS_PLUGIN_PATH', plugin_dir_path(__FILE__));
            $this->define('WOO_MOMO_BUSINESS_PLUGIN_BASENAME', plugin_basename(__FILE__));
            $this->define('WOO_MOMO_BUSINESS_PLUGIN_URL', plugin_dir_url(__FILE__));

            //includes
            $this->woo_momo_business_include('includes/class-woo-momo-business-payment-gateway.php');
            $this->woo_momo_business_include('includes/class-woo-momo-business-api-response.php');
            $this->woo_momo_business_include('includes/class-woo-momo-business-api-ipn.php');

            //action
            add_filter('woocommerce_payment_gateways', array($this, 'add_gateway_class'));
            add_filter('plugin_action_links_' . WOO_MOMO_BUSINESS_PLUGIN_BASENAME, array($this, 'add_settings_link'));
            add_action('wp_enqueue_scripts', array($this, 'plugin_assets'), 1);


            //call class
            new MoMo_Business_Response();
            new MoMo_Business_IPN();
        }

        /**
         * define
         *
         * @param $name
         * @param bool $value
         */
        function define($name, $value = true)
        {
            if (!defined($name)) {
                define($name, $value);
            }
        }

        /**
         * get path
         *
         * @param string $path
         *
         * @return string
         */
        function get_path($path = '')
        {
            return WOO_MOMO_BUSINESS_PLUGIN_PATH . $path;
        }

        /**
         * include
         *
         * @param $file
         */
        function woo_momo_business_include($file)
        {
            $path = $this->get_path($file);
            if (file_exists($path)) {
                include_once($path);
            }
        }

        /**
         * @param $methods
         * @return array
         */
        public function add_gateway_class($methods)
        {
            $methods[] = 'Woo_MoMo_Business_Payment_Gateway';
            return $methods;
        }

        /**
         * @param $links
         * @return array
         */
        public function add_settings_link($links)
        {
            $plugin_links = array(
                '<a href="' . admin_url('admin.php?page=wc-settings&tab=checkout&section=woo_momo_business') . '">' . __('Thiết lập', 'woo-momo-business') . '</a>'
            );
            return array_merge($plugin_links, $links);
        }


        public function plugin_assets()
        {
            wp_enqueue_style('woo-momo-style', WOO_MOMO_BUSINESS_PLUGIN_URL . 'assets/css/woo-momo-business.css', array(), '1.0.0', 'all');
        }
    }

    new WooMoMoBusiness();
endif;