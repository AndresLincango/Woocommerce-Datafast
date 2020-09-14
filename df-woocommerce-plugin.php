<?php

/*
Plugin Name: Datafast - Plugin para WooCommerce
Plugin URI: https://inti.ec
Description: Módulo para pagos con tarjetas de crédito a través de Datafast
Version: 1.0
Author: Andrés Lincango
Author URI: http://inti.ec
Text Domain: df_woocommerce
License: GPLv3
*/

add_action('plugins_loaded', 'df_woocommerce_plugin');

include(dirname(__FILE__) . '/includes/df-woocommerce-helper.php');
include(dirname(__FILE__) . '/includes/df-woocommerce-checker.php');
include(dirname(__FILE__) . '/includes/df-woocommerce-requester.php');
include(dirname(__FILE__) . '/includes/df-woocommerce-callback.php');
include(dirname(__FILE__) . '/includes/df-woocommerce-data-dump.php');
register_activation_hook(__FILE__, array('WC_Datafast_Database_Helper', 'create_database'));
register_deactivation_hook(__FILE__, array('WC_Datafast_Database_Helper', 'delete_database'));

//require( dirname( __FILE__ ) . '/includes/df-woocommerce-refund.php' );

//function datafast_woocommerce_order_refunded($order_id, $refund_id) {
//  $refund = new WC_Datafast_Refund();
//  $refund->refund($order_id);
//}
//
//add_action( 'woocommerce_order_refunded', 'datafast_woocommerce_order_refunded', 10, 2 );

if (!function_exists('df_woocommerce_plugin')) {
    function df_woocommerce_plugin()
    {
        class WC_Gateway_Datafast extends WC_Payment_Gateway
        {
            public function __construct()
            {
                $this->id = 'df_woocommerce';
                $this->icon = apply_filters('woocomerce_paymentez_icon', plugins_url('/assets/imgs/datafast.png', __FILE__));
                $this->method_title = 'Datafast Plugin';
                $this->method_description = 'Este módulo te permite procesar págos con tarjetas de crédito para WooCommerce a través de Datafast.';

                $this->init_settings();
                $this->init_form_fields();

                $this->company_name = $this->get_option('company_name');
                $this->enviroment = $this->get_option('staging');
                $this->title = $this->get_option('title');
                $this->description = $this->get_option('description');

                $this->app_identity_client = $this->get_option('app_identity_client');
                $this->app_auth_client = $this->get_option('app_auth_client');
                $this->app_mid = $this->get_option('app_mid');
                $this->app_tid = $this->get_option('app_tid');
                $this->server_test_url = $this->get_option('server_test_url');
                $this->server_live_url = $this->get_option('server_live_url');
                $this->datafast_woocommerce_url_success = $this->get_option('datafast_woocommerce_url_success');

                add_action('woocommerce_update_options_payment_gateways_' . $this->id, array(&$this, 'process_admin_options'));

                add_action('woocommerce_receipt_df_woocommerce', array(&$this, 'receipt_page'));
            }

            public function init_form_fields()
            {
                $this->form_fields = require(dirname(__FILE__) . '/includes/admin/datafast-settings.php');
            }

            function admin_options()
            {
                $logo = plugins_url('/assets/imgs/datafast.png', __FILE__);
                ?>
                <p>
                    <img style='width: 30%;position: relative;display: inherit;' src='<?php echo $logo; ?>'>
                </p>
                <h2><?php 'Gateway de Datafast'; ?></h2>
                <table class="form-table">
                    <?php $this->generate_settings_html(); ?>
                </table>
                <?php
            }

            function receipt_page($order)
            {
                echo $this->generate_datafast_form($order);
            }

            public function generate_datafast_form($order_id)
            {
                $order_transaction_id = WC_Datafast_Requester::get_params_post($order_id);
                $datafastObj = new WC_Gateway_Datafast();
                $server_callback_url = $datafastObj->datafast_woocommerce_url_success . "/datafast_action/";
                ?>

                <script type="text/javascript"
                        src="https://www.datafast.com.ec/js/dfAdditionalValidations1.js"></script>
                <script src="https://test.oppwa.com/v1/paymentWidgets.js?checkoutId=<?php echo $order_transaction_id; ?>"></script>
                <form action="http://import.com/datafast_action/" class="paymentWidgets"
                      data-brands="VISA MASTER DINERS">
                </form>

                <?php
            }

            public function process_payment($orderId)
            {
                $order = new WC_Order($orderId);
                return array(
                    'result' => 'success',
                    'redirect' => $order->get_checkout_payment_url(true)
                );
            }
        }
    }
}

function add_df_woocommerce_plugin($methods)
{
    $methods[] = 'WC_Gateway_Datafast';
    return $methods;
}

add_filter('woocommerce_payment_gateways', 'add_df_woocommerce_plugin');