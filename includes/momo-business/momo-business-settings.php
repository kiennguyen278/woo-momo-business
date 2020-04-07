<?php

if (!defined('ABSPATH')) {
    exit;
}

return array(
    'enabled' => array(
        'title' => __('Bật/Tắt', 'woo-momo-business'),
        'type' => 'checkbox',
        'label' => __('Bật phương thức thanh toán', 'woo-momo-business'),
        'default' => 'yes'
    ),
    'title' => array(
        'title' => __('Tiêu đề', 'woo-momo-business'),
        'type' => 'text',
        'description' => __('Tên phương thức thanh toán.', 'woo-momo-business'),
        'default' => __('Ví điện tử MoMo', 'woo-momo-business'),
        'desc_tip' => true,
    ),
    'description' => array(
        'title' => __('Mô tả', 'woo-momo-business'),
        'type' => 'textarea',
        'description' => __('Mô tả phương thức thanh toán.', 'woo-momo-business'),
        'default' => __('Thực hiện Thanh toán qua ví điện tử MoMo.', 'woo-momo-business'),
        'desc_tip' => true,
    ),
    'order_desc' => array(
        'title' => __('Nội dung thanh toán', 'woo-momo-business'),
        'type' => 'text',
        'description' => __('Nội dung thanh toán giúp chủ shop nhận biết được thanh toán cho đơn hàng nào. Với {{orderid}} là mã đơn hàng.', 'woo-momo-business'),
        'default' => __('{{orderid}}', 'woo-momo-business'),
        'desc_tip' => false,
    ),
    'order_created' => array(
        'title' => __('Trạng thái đơn hàng khi đơn hàng được tạo', 'woo-momo-business'),
        'type' => 'select',
        'options' => array(
            'pending' => __('Chờ thanh toán', 'woocommerce'),
            'processing' => __('Đang xử lý', 'woocommerce'),
            'on-hold' => __('Tạm giữ', 'woocommerce'),
            'completed' => __('Đã hoàn thành', 'woocommerce'),
            'cancelled' => __('Đã hủy', 'woocommerce'),
            'refunded' => __('Đã hoàn lại tiền', 'woocommerce'),
            'failed' => __('Thất bại', 'woocommerce')
        ),
        'default' => 'pending',
        'desc_tip' => true,
    ),
    'payment_success' => array(
        'title' => __('Trạng thái đơn hàng khi thanh toán thành công', 'woo-momo-business'),
        'type' => 'select',
        'options' => array(
            'pending' => __('Chờ thanh toán', 'woocommerce'),
            'processing' => __('Đang xử lý', 'woocommerce'),
            'on-hold' => __('Tạm giữ', 'woocommerce'),
            'completed' => __('Đã hoàn thành', 'woocommerce'),
            'cancelled' => __('Đã hủy', 'woocommerce'),
            'refunded' => __('Đã hoàn lại tiền', 'woocommerce'),
            'failed' => __('Thất bại', 'woocommerce')
        ),
        'default' => 'processing',
        'desc_tip' => true,
    ),
    'payment_failed' => array(
        'title' => __('Trạng thái đơn hàng khi thanh toán thất bại', 'woo-momo-business'),
        'type' => 'select',
        'options' => array(
            'pending' => __('Chờ thanh toán', 'woocommerce'),
            'processing' => __('Đang xử lý', 'woocommerce'),
            'on-hold' => __('Tạm giữ', 'woocommerce'),
            'completed' => __('Đã hoàn thành', 'woocommerce'),
            'cancelled' => __('Đã hủy', 'woocommerce'),
            'refunded' => __('Đã hoàn lại tiền', 'woocommerce'),
            'failed' => __('Thất bại', 'woocommerce')
        ),
        'default' => 'cancelled',
        'desc_tip' => true,
    ),
    'payment_notice_successful' => array(
        'title' => __('Thông báo khi thanh toán thành công', 'woo-momo-business'),
        'type' => 'textarea',
        'description' => __('{{orderid}} Mã đơn hàng. {{amount}} Số tiền. {{transaction}} Mã giao dịch tại MoMo.', 'woo-momo-business'),
        'default' => __('Quý khách đã thanh toán {{amount}} thành công cho đơn hàng {{orderid}}. Mã giao dịch tại MoMo {{transaction}}. Xin chân thành cảm ơn quý khách!', 'woo-momo-business'),
        'desc_tip' => false,
    ),
    'button_label' => array(
        'title' => __('Nút thanh toán', 'woo-momo-business'),
        'type' => 'text',
        'description' => __('Thay đổi tên nút thanh toán.', 'woo-momo-business'),
        'default' => __('Thanh toán qua MoMo', 'woo-momo-business'),
        'desc_tip' => true,
    ),
    'partner_code' => array(
        'title' => __('Partner code', 'woo-momo-business'),
        'type' => 'text',
        'description' => __('Cung cấp bởi MoMo.', 'woo-momo-business'),
        'desc_tip' => false,
    ),
    'access_key' => array(
        'title' => __('Access key', 'woo-momo-business'),
        'type' => 'password',
        'description' => __('Cung cấp bởi MoMo.', 'woo-momo-business'),
        'desc_tip' => false,
    ),
    'secret_key' => array(
        'title' => __('Secret key', 'woo-momo-business'),
        'type' => 'password',
        'description' => __('Cung cấp bởi MoMo.', 'woo-momo-business'),
        'desc_tip' => false,
    ),
    'api_endpoint' => array(
        'title' => __('API endpoint', 'woo-momo-business'),
        'type' => 'text',
        'description' => __('Cung cấp bởi MoMo.', 'woo-momo-business'),
        'desc_tip' => false,
    )
);
