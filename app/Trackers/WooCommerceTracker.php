<?php
declare(strict_types=1);

namespace WPSiteActivityLog\Trackers;

if (!defined('ABSPATH')) exit;

use WPSiteActivityLog\Log\ActivityLogger;

class WooCommerceTracker
{
    private static ?self $instance = null;
    private ActivityLogger $logger;

    private function __construct()
    {
        $this->logger = ActivityLogger::get_instance();
    }

    private function __clone() {}

    public function __wakeup(): void
    {
        throw new \Exception('Cannot unserialize WooCommerceTracker');
    }

    public static function get_instance(): self
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function init(): void
    {
        add_action('woocommerce_order_status_changed', [$this, 'track_order_status'], 10, 4);
        add_action('woocommerce_new_product', [$this, 'track_new_product'], 10, 2);
        add_action('woocommerce_update_product', [$this, 'track_update_product'], 10, 2);
        add_action('woocommerce_product_set_stock', [$this, 'track_stock_change'], 10, 1);
        add_action('woocommerce_new_coupon', [$this, 'track_new_coupon'], 10, 2);
        add_action('before_delete_post', [$this, 'track_delete_wc_item'], 10, 2);
    }

    public function track_order_status(int $order_id, string $old_status, string $new_status, $order): void
    {
        $order_number = is_object($order) && method_exists($order, 'get_order_number')
            ? (string)$order->get_order_number()
            : (string)$order_id;

        $total = is_object($order) && method_exists($order, 'get_formatted_order_total')
            ? wp_strip_all_tags((string)$order->get_formatted_order_total())
            : '';

        $currency = is_object($order) && method_exists($order, 'get_currency')
            ? (string)$order->get_currency()
            : '';

        $this->logger->log('wc.order_status', sprintf(__('Order #%s', 'loghaven-site-logs'), $order_number), [
            'order_id' => $order_id,
            'order_number' => $order_number,
            'old_status' => $old_status,
            'new_status' => $new_status,
            'total' => $total,
            'currency' => $currency,
        ], $order_id);
    }

    public function track_new_product(int $product_id, $product = null): void
    {
        $name = is_object($product) && method_exists($product, 'get_name')
            ? $product->get_name()
            : get_the_title($product_id);

        $sku = is_object($product) && method_exists($product, 'get_sku')
            ? $product->get_sku()
            : '';

        $price = is_object($product) && method_exists($product, 'get_price')
            ? (string)$product->get_price()
            : '';

        $this->logger->log('wc.product_created', $name ?: sprintf(__('Product #%d', 'loghaven-site-logs'), $product_id), [
            'product_id' => $product_id,
            'sku' => $sku,
            'price' => $price,
        ], $product_id);
    }

    public function track_update_product(int $product_id, $product = null): void
    {
        // Avoid duplicate logging on autosave or non-admin calls
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return;
        }

        $name = is_object($product) && method_exists($product, 'get_name')
            ? $product->get_name()
            : get_the_title($product_id);

        $price = is_object($product) && method_exists($product, 'get_price')
            ? (string)$product->get_price()
            : '';

        $stock = is_object($product) && method_exists($product, 'get_stock_quantity')
            ? $product->get_stock_quantity()
            : null;

        $this->logger->log('wc.product_updated', $name ?: sprintf(__('Product #%d', 'loghaven-site-logs'), $product_id), [
            'product_id' => $product_id,
            'price' => $price,
            'stock_quantity' => $stock,
        ], $product_id);
    }

    public function track_stock_change($product): void
    {
        if (!is_object($product) || !method_exists($product, 'get_id')) {
            return;
        }

        $product_id = (int)$product->get_id();
        $name = method_exists($product, 'get_name') ? $product->get_name() : get_the_title($product_id);
        $stock = method_exists($product, 'get_stock_quantity') ? $product->get_stock_quantity() : null;

        $this->logger->log('wc.stock_changed', $name ?: sprintf(__('Product #%d', 'loghaven-site-logs'), $product_id), [
            'product_id' => $product_id,
            'new_stock' => $stock,
        ], $product_id);
    }

    public function track_new_coupon(int $coupon_id, $coupon = null): void
    {
        $code = is_object($coupon) && method_exists($coupon, 'get_code')
            ? $coupon->get_code()
            : get_the_title($coupon_id);

        $this->logger->log('wc.coupon_created', $code ?: sprintf(__('Coupon #%d', 'loghaven-site-logs'), $coupon_id), [
            'coupon_id' => $coupon_id,
            'code' => $code,
        ], $coupon_id);
    }

    public function track_delete_wc_item(int $post_id, \WP_Post $post): void
    {
        if ($post->post_type === 'product') {
            $this->logger->log('wc.product_deleted', $post->post_title ?: sprintf(__('Product #%d', 'loghaven-site-logs'), $post_id), [
                'product_id' => $post_id,
            ], $post_id);
        } elseif ($post->post_type === 'shop_coupon') {
            $this->logger->log('wc.coupon_deleted', $post->post_title ?: sprintf(__('Coupon #%d', 'loghaven-site-logs'), $post_id), [
                'coupon_id' => $post_id,
            ], $post_id);
        }
    }
}
