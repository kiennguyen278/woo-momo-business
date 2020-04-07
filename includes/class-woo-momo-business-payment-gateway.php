<?php
if (!defined('ABSPATH')) {
    exit;
}

if (!class_exists('Woo_MoMo_Business_Payment_Gateway')) :
    class Woo_MoMo_Business_Payment_Gateway extends WC_Payment_Gateway
    {
        /**
         * Woo_MoMo_Business_Payment_Gateway constructor.
         */
        public function __construct()
        {
            $this->id = 'woo_momo_business';
            $this->has_fields = false;
            $this->method_title = __('Ví điện tử MoMo', 'woo-momo-business');
            $this->method_description = __('Thực hiện thanh toán qua ví điện tử MoMo, sử dụng tài khoản dành cho doanh nghiệp.', 'woo-momo-business');

            // Load the settings.
            $this->init_form_fields();
            $this->init_settings();

            // Define user set variables
            $this->title = $this->get_option('title');
            $this->description = $this->get_option('description');
            $this->instructions = "";

            // Actions
            add_action('woocommerce_update_options_payment_gateways_' . $this->id, array($this, 'process_admin_options'));
            add_filter('woocommerce_order_button_text', array($this, 'order_button_name'));
            add_action('woocommerce_thankyou_' . $this->id, array($this, 'momo_business_payment_results'));
        }


        public function init_form_fields()
        {
            $this->form_fields = include('momo-business/momo-business-settings.php');
        }


        /**
         * @param $order_id
         */
        public function momo_business_payment_results($order_id)
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
                $secretKey = $this->get_option('secret_key');
                $partnerSignature = hash_hmac("sha256", $rawHash, $secretKey);
                if ($m2signature == $partnerSignature) {
                    if ($errorCode == '0') {
                        $array = array(
                            '{{orderid}}' => $order_id,
                            '{{amount}}' => number_format($amount, '0', '.', '.') . 'đ',
                            '{{transaction}}' => $transId
                        );
                        $payment_notice_successful = strtr($this->get_option('payment_notice_successful'), $array);
                        ?>
                        <section class="momo-payment-gateway-wrapper">
                            <div class="momo-payment-gateway-notification">
                                <img src="<?php echo WOO_MOMO_BUSINESS_PLUGIN_URL . 'assets/images/correct.svg'; ?>"
                                     alt="Giao dịch thành công">
                                <h2 class="notification-title">Giao dịch thành công</h2>
                                <p class="notification-content"><?php echo wpautop($payment_notice_successful); ?></p>
                            </div>
                        </section>
                        <?php
                    } else if ($errorCode == '49') {
                        ?>
                        <section class="momo-payment-gateway-wrapper">
                            <div class="momo-payment-gateway-notification">
                                <img src="<?php echo WOO_MOMO_BUSINESS_PLUGIN_URL . 'assets/images/caution.svg'; ?>"
                                     alt="Cảnh báo">
                                <h2 class="notification-title">Giao dịch thất bại</h2>
                                <p class="notification-content">Khách hàng huỷ giao dịch</p>
                            </div>
                        </section>
                        <?php
                    } else if ($errorCode == '36') {
                        ?>
                        <section class="momo-payment-gateway-wrapper">
                            <div class="momo-payment-gateway-notification">
                                <img src="<?php echo WOO_MOMO_BUSINESS_PLUGIN_URL . 'assets/images/caution.svg'; ?>"
                                     alt="Cảnh báo">
                                <h2 class="notification-title">Giao dịch thất bại</h2>
                                <p class="notification-content">Giao dịch đã hết hạn</p>
                            </div>
                        </section>
                        <?php
                    } else {
                        ?>
                        <section class="momo-payment-gateway-wrapper">
                            <div class="momo-payment-gateway-notification">
                                <img src="<?php echo WOO_MOMO_BUSINESS_PLUGIN_URL . 'assets/images/caution.svg'; ?>"
                                     alt="Cảnh báo">
                                <h2 class="notification-title">Giao dịch thất bại</h2>
                                <p class="notification-content">Đã xảy ra lỗi trong quá trình giao dịch. Vui lòng
                                    thử lại sau!</p>
                            </div>
                        </section>
                        <?php
                    }
                } else {
                    ?>
                    <section class="momo-payment-gateway-wrapper">
                        <div class="momo-payment-gateway-notification">
                            <img src="<?php echo WOO_MOMO_BUSINESS_PLUGIN_URL . 'assets/images/caution.svg'; ?>"
                                 alt="Cảnh báo">
                            <h2 class="notification-title">Cảnh báo</h2>
                            <p class="notification-content">Chữ ký không hợp lệ</p>
                        </div>
                    </section>
                    <?php
                }
            }
        }

        /**
         * @param int $order_id
         * @return array
         */
        public function process_payment($order_id)
        {
            $order = wc_get_order($order_id);
            $order->update_status($this->get_option('order_created'));

            return array(
                'result' => 'success',
                'redirect' => $this->get_pay_url($order)
            );
        }

        /**
         * @param $order
         * @return mixed
         */
        public function get_pay_url($order)
        {
            $merchantName = get_bloginfo('name');
            $returnUrl = admin_url('admin-ajax.php?action=woo_momo_business_response&type=international');
            $notifyUrl = admin_url('admin-ajax.php?action=woo_momo_business_ipn&type=international');
            $endpoint = $this->get_option('api_endpoint');
            $partnerCode = $this->get_option('partner_code');
            $accessKey = $this->get_option('access_key');
            $secretKey = $this->get_option('secret_key');
            $array = array(
                '{{orderid}}' => $order->get_id()
            );
            $orderInfo = strtr($this->get_option('order_desc'), $array);
            $amount = number_format($order->get_total(), 0, '.', '');
            $orderId = strval($order->get_id());
            $extraData = "merchantName=" . $merchantName;
            $requestId = time() . "";
            $requestType = "captureMoMoWallet";
            $rawHash = "partnerCode=" . $partnerCode . "&accessKey=" . $accessKey . "&requestId=" . $requestId . "&amount=" . $amount . "&orderId=" . $orderId . "&orderInfo=" . $orderInfo . "&returnUrl=" . $returnUrl . "&notifyUrl=" . $notifyUrl . "&extraData=" . $extraData;
            $signature = hash_hmac("sha256", $rawHash, $secretKey);
            $data = array(
                'partnerCode' => $partnerCode,
                'accessKey' => $accessKey,
                'requestId' => $requestId,
                'amount' => $amount,
                'orderId' => $orderId,
                'orderInfo' => $orderInfo,
                'returnUrl' => $returnUrl,
                'notifyUrl' => $notifyUrl,
                'extraData' => $extraData,
                'requestType' => $requestType,
                'signature' => $signature
            );
            $result = $this->execPostRequest($endpoint, json_encode($data));
            $jsonResult = json_decode($result, true);
            return $jsonResult['payUrl'];
        }

        /**
         * @param $url
         * @param $data
         * @return bool|string
         */
        public function execPostRequest($url, $data)
        {
            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
            curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                    'Content-Type: application/json;charset=UTF-8',
                    'Content-Length: ' . strlen($data)
                )
            );
            curl_setopt($ch, CURLOPT_TIMEOUT, 5);
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
            $result = curl_exec($ch);
            curl_close($ch);
            return $result;
        }

        /**
         * @param $order_button_name
         * @return string
         */
        public function order_button_name($order_button_name)
        {
            $chosen_payment_method = WC()->session->get('chosen_payment_method');
            if ($chosen_payment_method == 'woo_momo_business') {
                $order_button_name = $this->get_option('button_label');;
            } ?>
            <script type="text/javascript">
                (function ($) {
                    $('form.checkout').on('change', 'input[name^="payment_method"]', function () {
                        var t = {
                            updateTimer: !1, dirtyInput: !1,
                            reset_update_checkout_timer: function () {
                                clearTimeout(t.updateTimer)
                            }, trigger_update_checkout: function () {
                                t.reset_update_checkout_timer(), t.dirtyInput = !1,
                                    $(document.body).trigger("update_checkout")
                            }
                        };
                        t.trigger_update_checkout();
                    });
                })(jQuery);
            </script><?php
            return $order_button_name;
        }

        /**
         * @return bool
         */
        public function isValidCurrency()
        {
            return in_array(get_woocommerce_currency(), array('VND'));
        }

        public function admin_options()
        {
            if ($this->isValidCurrency()) {
                parent::admin_options();
            } else {
                ?>
                <div class="inline error">
                    <p>
                        <strong><?php _e('Phương thức thanh toán không khả dụng', 'woo-momo-business'); ?></strong>:
                        <?php _e('MoMo không hỗ trợ đơn vị tiền tệ của bạn. Hiện tại, MoMo chỉ hỗ trợ đơn vị tiền tệ Việt Nam Đồng (VND).', 'woo-momo-business'); ?>
                    </p>
                </div>
                <?php
            }
        }
    }
endif;
