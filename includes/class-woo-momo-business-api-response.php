<?php

if (!defined('ABSPATH')) {
    exit;
}

if (!class_exists('MoMo_Business_Response')) {
    class MoMo_Business_Response
    {

        /**
         * MoMo_Business_Response constructor.
         */
        public function __construct()
        {

            $this->action();
        }

        public function action()
        {
            add_action('wp_ajax_woo_momo_business_response', array($this, 'checkResponse'));
            add_action('wp_ajax_nopriv_woo_momo_business_response', array($this, 'checkResponse'));
        }

        public function checkResponse()
        {
            if (!empty($_GET)) {
                $partnerCode = $_GET["partnerCode"];
                $accessKey = $_GET["accessKey"];
                $orderId = $_GET["orderId"];
                $localMessage = $_GET["localMessage"];
                $message = $_GET["message"];
                $transId = $_GET["transId"];
                $orderInfo = $_GET["orderInfo"];
                $amount = $_GET["amount"];
                $errorCode = $_GET["errorCode"];
                $responseTime = $_GET["responseTime"];
                $requestId = $_GET["requestId"];
                $extraData = $_GET["extraData"];
                $payType = $_GET["payType"];
                $orderType = $_GET["orderType"];
                $m2signature = $_GET["signature"]; //MoMo signature

                //Checksum
                $rawHash = "partnerCode=" . $partnerCode . "&accessKey=" . $accessKey . "&requestId=" . $requestId . "&amount=" . $amount . "&orderId=" . $orderId . "&orderInfo=" . $orderInfo .
                    "&orderType=" . $orderType . "&transId=" . $transId . "&message=" . $message . "&localMessage=" . $localMessage . "&responseTime=" . $responseTime . "&errorCode=" . $errorCode .
                    "&payType=" . $payType . "&extraData=" . $extraData;

                global $woocommerce;
                $order = $order = new WC_Order($orderId);
                $gateway = new Woo_MoMo_Business_Payment_Gateway;
                $secretKey = $gateway->get_option('secret_key');

                $partnerSignature = hash_hmac("sha256", $rawHash, $secretKey);

                //echo "<script>console.log('Debug huhu Objects: " . $rawHash . "' );</script>";
                //echo "<script>console.log('Debug huhu Objects: " . $partnerSignature . "' );</script>";

                if ($m2signature == $partnerSignature) {
                    if ($errorCode == '0') {
                        $transStatus = 'Thanh toán thành công. Mã giao dịch tại MoMo: ' . $transId . '. Nội dung thanh toán: ' . $orderInfo;
                        $order->update_status($gateway->get_option('payment_success'));
                        $order->add_order_note(__($transStatus, 'woo-momo-business'));
                        $woocommerce->cart->empty_cart();
                        $url = wc_get_checkout_url() . 'order-received/' . $order->get_id() . '/?key=' . $order->order_key . '&' . $rawHash . '&signature=' . $partnerSignature;
                        wp_redirect($url);
                    } else if ($errorCode == '49') {
                        $order->update_status($gateway->get_option('payment_failed'));
                        $order->add_order_note(__('Khách hàng huỷ giao dịch', 'woo-momo-business'));
                        $woocommerce->cart->empty_cart();
                        $url = wc_get_checkout_url() . 'order-received/' . $order->get_id() . '/?key=' . $order->order_key . '&' . $rawHash . '&signature=' . $partnerSignature;
                        wp_redirect($url);
                    } else if ($errorCode == '36') {
                        $order->update_status($gateway->get_option('payment_failed'));
                        $order->add_order_note(__('Giao dịch đã hết hạn', 'woo-momo-business'));
                        $woocommerce->cart->empty_cart();
                        $url = wc_get_checkout_url() . 'order-received/' . $order->get_id() . '/?key=' . $order->order_key . '&' . $rawHash . '&signature=' . $partnerSignature;
                        wp_redirect($url);
                    } else {
                        $order->update_status($gateway->get_option('payment_failed'));
                        $order->add_order_note(__('Giao dịch thất bại', 'woo-momo-business'));
                        $woocommerce->cart->empty_cart();
                        $url = wc_get_checkout_url() . 'order-received/' . $order->get_id() . '/?key=' . $order->order_key . '&' . $rawHash . '&signature=' . $partnerSignature;
                        wp_redirect($url);
                    }
                } else {
                    $order->update_status($gateway->get_option('payment_failed'));
                    $order->add_order_note(__('Có lỗi trong quá trình thanh toán', 'woo-momo-business'));
                    $woocommerce->cart->empty_cart();
                    $url = wc_get_checkout_url() . 'order-received/' . $order->get_id() . '/?key=' . $order->order_key . '&' . $rawHash . '&signature=' . $partnerSignature;
                    wp_redirect($url);
                }
            }
            die();
        }
    }
}
