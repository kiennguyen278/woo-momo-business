<?php

if (!defined('ABSPATH')) {
    exit;
}

if (!class_exists('WooMoMoLicense')) :
    class WooMoMoBusinessLicense
    {
        function check_license()
        {
            $host = "https://license.nhutfs.net/api/";
            $api_host = $host . "check-license";
            $woo_momo_business_data = get_option('woo_momo_business');
            $current_domain = $_SERVER['SERVER_NAME'];
            $license_key = $woo_momo_business_data['license_key'];
            if ($license_key == NULL) {
                $license_key = '0';
            }
            $data = array(
                "domain" => $current_domain,
                "license-key" => $license_key,
                "product-id" => WOO_MOMO_BUSINESS_PRODUCT_ID
            );
            $result = $this->execPostRequestAPI($api_host, json_encode($data));
            $jsonResult = json_decode($result, true);
            if ($jsonResult['result']['status'] == "active" && $jsonResult['result']['product-id'] == WOO_MOMO_BUSINESS_PRODUCT_ID) {
                if (!file_exists(WOO_MOMO_BUSINESS_LICENSE_FILE_PATH)) {
                    $content = $jsonResult['result']['status'];
                    $fp = fopen(WOO_MOMO_BUSINESS_LICENSE_FILE_PATH, "wb");
                    fwrite($fp, $content);
                    fclose($fp);
                }
            } else {
                if (file_exists(WOO_MOMO_BUSINESS_LICENSE_FILE_PATH)) {
                    unlink(WOO_MOMO_BUSINESS_LICENSE_FILE_PATH);
                }
            }
        }

        function check_license_file_type()
        {
            if (file_exists(WOO_MOMO_BUSINESS_LICENSE_FILE_PATH)) {
                return true;
            } else {
                return false;
            }
        }

        /**
         * @param $url
         * @param $data
         * @return bool|string
         */
        public function execPostRequestAPI($url, $data)
        {
            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
            curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                    'Content-Type: application/json;charset=UTF-8'
                )
            );
            curl_setopt($ch, CURLOPT_TIMEOUT, 400);
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 0);
            $result = curl_exec($ch);
            curl_close($ch);
            return $result;
        }
    }
endif;
