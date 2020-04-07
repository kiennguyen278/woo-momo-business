<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

if ( ! class_exists( 'MoMo_Business_IPN' ) ) {
    class MoMo_Business_IPN {

        /**
         * MoMo_Business_IPN constructor.
         */
        public function __construct() {

            $this->action();
        }

        public function action() {
            add_action( 'wp_ajax_woo_momo_business_ipn', array( $this, 'checkResponse' ) );
            add_action( 'wp_ajax_nopriv_woo_momo_business_ipn', array( $this, 'checkResponse' ) );
        }

        public function checkResponse() {
            header( "content-type: application/x-www-form-urlencoded" );
            http_response_code( 200 ); //200 - Everything will be 200 Oke
            if ( ! empty( $_POST ) ) {
                $response = array();
                try {
                    $partnerCode  = $_POST["partnerCode"];
                    $accessKey    = $_POST["accessKey"];
                    $orderId      = $_POST["orderId"];
                    $localMessage = $_POST["localMessage"];
                    $message      = $_POST["message"];
                    $transId      = $_POST["transId"];
                    $orderInfo    = $_POST["orderInfo"];
                    $amount       = $_POST["amount"];
                    $errorCode    = $_POST["errorCode"];
                    $responseTime = $_POST["responseTime"];
                    $requestId    = $_POST["requestId"];
                    $extraData    = $_POST["extraData"];
                    $payType      = $_POST["payType"];
                    $orderType    = $_POST["orderType"];
                    $extraData    = $_POST["extraData"];
                    $m2signature  = $_POST["signature"]; //MoMo signature


                    //Checksum
                    $rawHash = "partnerCode=" . $partnerCode . "&accessKey=" . $accessKey . "&requestId=" . $requestId . "&amount=" . $amount . "&orderId=" . $orderId . "&orderInfo=" . $orderInfo .
                        "&orderType=" . $orderType . "&transId=" . $transId . "&message=" . $message . "&localMessage=" . $localMessage . "&responseTime=" . $responseTime . "&errorCode=" . $errorCode .
                        "&payType=" . $payType . "&extraData=" . $extraData;

                    global $woocommerce;
                    $order = $order = new WC_Order( $orderId );
                    $gateway   = new Woo_MoMo_Business_Payment_Gateway;
                    $secretKey = $gateway->get_option( 'secret_key' );

                    $partnerSignature = hash_hmac( "sha256", $rawHash, $secretKey );
                    if ( $m2signature == $partnerSignature ) {
                        if ( $errorCode == '0' ) {
                            $transStatus = 'Thanh toán thành công. Mã giao dịch tại MoMo: ' . $transId . '. Nội dung thanh toán: ' . $orderInfo;
                            $order->update_status( $gateway->get_option( 'payment_success' ) );
                            $order->add_order_note( __( $transStatus, 'woo-momo-business' ) );
                            $woocommerce->cart->empty_cart();
                        } else if ( $errorCode == '49' ) {
                            $order->update_status($gateway->get_option('payment_failed'));
                            $order->add_order_note( __( 'Khách hàng huỷ giao dịch', 'woo-momo-business' ) );
                            $woocommerce->cart->empty_cart();
                        } else if ( $errorCode == '36' ) {
                            $order->update_status($gateway->get_option('payment_failed'));
                            $order->add_order_note( __( 'Giao dịch đã hết hạn', 'woo-momo-business' ) );
                            $woocommerce->cart->empty_cart();
                        } else {
                            $order->update_status($gateway->get_option('payment_failed'));
                            $order->add_order_note( __( 'Giao dịch thất bại', 'woo-momo-business' ) );
                            $woocommerce->cart->empty_cart();
                        }
                    } else {
                        $order->update_status($gateway->get_option('payment_failed'));
                        $woocommerce->cart->empty_cart();
                    }

                } catch ( Exception $e ) {
                    echo $response['message'] = $e;
                }
                $debugger                     = array();
                $debugger['rawData']          = $rawHash;
                $debugger['momoSignature']    = $m2signature;
                $debugger['partnerSignature'] = $partnerSignature;

                if ( $m2signature == $partnerSignature ) {
                    $response['message'] = "Received payment result success";
                } else {
                    $response['message'] = "ERROR! Fail checksum";
                }
                $response['debugger'] = $debugger;
                echo json_encode( $response );
            }
            die();
        }
    }
}
