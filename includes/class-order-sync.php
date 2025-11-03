<?php
/**
 * Order Sync Class
 * Handles daily order synchronization with CodGuard API
 * 
 * @package CodGuard
 * @since 2.0.0
 * @version 2.0.9
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

class CodGuard_Order_Sync {

    /**
     * Cron hook name
     */
    const CRON_HOOK = 'codguard_daily_order_sync';

    /**
     * Initialize order sync functionality
     */
    public function __construct() {
        // Register cron schedule
        add_filter('cron_schedules', array($this, 'add_cron_schedule'));
        
        // Register cron hook
        add_action(self::CRON_HOOK, array($this, 'sync_orders'));
        
        // AJAX handler for manual sync
        add_action('wp_ajax_codguard_manual_sync', array($this, 'ajax_manual_sync'));
    }

    /**
     * Maybe schedule sync - called on init
     * This ensures the cron is scheduled when plugin is enabled
     */
    public function maybe_schedule_sync() {
        if (!codguard_is_enabled()) {
            return;
        }

        if (!wp_next_scheduled(self::CRON_HOOK)) {
            $this->schedule_sync();
        }
    }

    /**
     * Add custom cron schedule for daily at 02:00
     *
     * @param array $schedules Existing schedules
     * @return array Modified schedules
     */
    public function add_cron_schedule($schedules) {
        $schedules['codguard_daily'] = array(
            'interval' => DAY_IN_SECONDS,
            'display'  => __('Once Daily (CodGuard)', 'codguard')
        );
        return $schedules;
    }

    /**
     * Schedule the daily sync at 02:00 local time
     */
    public function schedule_sync() {
        // Don't schedule if plugin is not enabled
        if (!codguard_is_enabled()) {
            codguard_log('Sync not scheduled: Plugin not enabled', 'info');
            return;
        }

        // Clear any existing schedules first
        $this->clear_schedule();

        // Calculate next 02:00 timestamp
        $next_run = $this->calculate_next_sync_time();

        // Schedule the event
        $scheduled = wp_schedule_event($next_run, 'codguard_daily', self::CRON_HOOK);

        if ($scheduled !== false) {
            codguard_log('Order sync scheduled for: ' . date('Y-m-d H:i:s', $next_run), 'info');
            update_option('codguard_last_schedule_time', $next_run);
        } else {
            codguard_log('Failed to schedule order sync', 'error');
        }
    }

    /**
     * Clear scheduled sync
     */
    public function clear_schedule() {
        $timestamp = wp_next_scheduled(self::CRON_HOOK);
        if ($timestamp) {
            wp_unschedule_event($timestamp, self::CRON_HOOK);
            codguard_log('Order sync schedule cleared', 'info');
        }
    }

    /**
     * Get next sync time (02:00 local time)
     *
     * @return int Unix timestamp
     */
    private function calculate_next_sync_time() {
        // Get WordPress timezone
        $timezone_string = wp_timezone_string();
        $timezone = new DateTimeZone($timezone_string);
        
        // Current time in site's timezone
        $now = new DateTime('now', $timezone);
        
        // Target time: 02:00 today
        $target = new DateTime('today 02:00:00', $timezone);
        
        // If 02:00 has passed today, schedule for tomorrow
        if ($now >= $target) {
            $target->modify('+1 day');
        }
        
        return $target->getTimestamp();
    }

    /**
     * Main sync function - called by cron
     */
    public function sync_orders() {
        // Check if plugin is enabled
        if (!codguard_is_enabled()) {
            codguard_log('Sync skipped: Plugin not enabled', 'warning');
            return;
        }

        codguard_log('Starting daily order sync', 'info');

        // Update last sync attempt time
        update_option('codguard_last_sync_attempt', current_time('mysql'));

        // Get orders from previous day
        $orders = $this->get_orders_from_yesterday();

        if (empty($orders)) {
            codguard_log('No orders found for yesterday', 'info');
            update_option('codguard_last_sync', current_time('mysql'));
            update_option('codguard_last_sync_status', 'success');
            update_option('codguard_last_sync_count', 0);
            return;
        }

        codguard_log(sprintf('Found %d orders to sync', count($orders)), 'info');

        // Prepare order data for API
        $order_data = $this->prepare_order_data($orders);

        if (empty($order_data)) {
            codguard_log('No valid orders to sync after filtering', 'info');
            update_option('codguard_last_sync', current_time('mysql'));
            update_option('codguard_last_sync_status', 'success');
            update_option('codguard_last_sync_count', 0);
            return;
        }
    }

    /**
     * Get orders from yesterday
     *
     * @return array Array of WC_Order objects
     */
    private function get_orders_from_yesterday() {
        // Get timezone
        $timezone_string = wp_timezone_string();
        $timezone = new DateTimeZone($timezone_string);
        
        // Yesterday's date range
        $yesterday_start = new DateTime('yesterday 00:00:00', $timezone);
        $yesterday_end = new DateTime('yesterday 23:59:59', $timezone);

        // Query ALL orders from yesterday - no payment method filter
        $args = array(
            'limit'        => -1,
            'date_created' => $yesterday_start->getTimestamp() . '...' . $yesterday_end->getTimestamp(),
            'return'       => 'objects',
        );

        // Get orders
        $orders = wc_get_orders($args);

        codguard_log(sprintf(
            'Querying ALL orders from %s to %s',
            $yesterday_start->format('Y-m-d H:i:s'),
            $yesterday_end->format('Y-m-d H:i:s')
        ), 'debug');

        codguard_log(sprintf('Found %d total orders', count($orders)), 'debug');

        return $orders;
    }

    /**
     * Prepare order data for API
     * 
     * Now uploads ONLY orders matching configured statuses
     * Refused status gets outcome = -1, successful status gets outcome = 1
     *
     * @param array $orders Array of WC_Order objects
     * @return array Formatted order data
     */
    private function prepare_order_data($orders) {
        $shop_id = codguard_get_shop_id();
        $status_mappings = codguard_get_status_mappings();
        $order_data = array();
        
        // Get configured statuses
        $successful_status = $status_mappings['good'];
        $refused_status = $status_mappings['refused'];

        codguard_log(sprintf(
            'Filtering orders: Successful=%s, Refused=%s',
            $successful_status,
            $refused_status
        ), 'debug');

        foreach ($orders as $order) {
            $order_status = $order->get_status();
            
            // Skip orders that don't match either configured status
            if ($order_status !== $successful_status && $order_status !== $refused_status) {
                codguard_log(sprintf(
                    'Skipping order #%d: Status "%s" does not match configured statuses',
                    $order->get_id(),
                    $order_status
                ), 'debug');
                continue;
            }

            // Get billing info
            $billing_email = $order->get_billing_email();
            $billing_phone = $order->get_billing_phone();
            $billing_country = $order->get_billing_country();
            $billing_postcode = $order->get_billing_postcode();
            $billing_address = $this->format_address($order);
            $payment_method = $order->get_payment_method();

            // Skip if no email (required field)
            if (empty($billing_email)) {
                codguard_log(sprintf('Skipping order #%d: No email address', $order->get_id()), 'warning');
                continue;
            }

            // Determine outcome based on status
            // Refused status = -1, successful status = 1
            $outcome = ($order_status === $refused_status) ? -1 : 1;

            // Add to order data
            $order_data[] = array(
                'eshop_id'     => (int) $shop_id,
                'email'        => $billing_email,
                'code'         => $order->get_order_number(),
                'status'       => $order_status,
                'outcome'      => $outcome,
                'phone'        => $billing_phone ?: '',
                'country_code' => $billing_country ?: '',
                'postal_code'  => $billing_postcode ?: '',
                'address'      => $billing_address,
            );

            codguard_log(sprintf(
                'Order #%d added: Status=%s, Payment=%s, Email=%s, Outcome=%d',
                $order->get_id(),
                $order_status,
                $payment_method,
                $billing_email,
                $outcome
            ), 'debug');
        }

        codguard_log(sprintf(
            'Prepared %d orders for upload (refused=%d, successful=%d)',
            count($order_data),
            $refused_status,
            $successful_status
        ), 'info');

        return $order_data;
    }

    /**
     * Format billing address
     *
     * @param WC_Order $order Order object
     * @return string Formatted address
     */
    private function format_address($order) {
        $address_parts = array_filter(array(
            $order->get_billing_address_1(),
            $order->get_billing_address_2(),
            $order->get_billing_city(),
            $order->get_billing_state(),
        ));

        return implode(', ', $address_parts);
    }

    /**
     * Send order data to CodGuard API
     *
     * @param array $order_data Prepared order data
     * @return array|WP_Error API response or error
     */
    private function send_to_api($order_data) {
        if (empty($order_data)) {
            return new WP_Error('no_orders', 'No orders to sync');
        }

        $keys = codguard_get_api_keys();
        $url = 'https://api.codguard.com/api/orders/import';

        $body = array(
            'orders' => $order_data
        );

        codguard_log(sprintf('Sending %d orders to API', count($order_data)), 'debug');

        $response = wp_remote_post($url, array(
            'timeout' => 30,
            'headers' => array(
                'Content-Type'       => 'application/json',
                'X-API-PUBLIC-KEY'   => $keys['public'],
                'X-API-PRIVATE-KEY'  => $keys['private'],
            ),
            'body' => wp_json_encode($body),
        ));

        // Check for WP errors
        if (is_wp_error($response)) {
            return $response;
        }

        $status_code = wp_remote_retrieve_response_code($response);
        $response_body = wp_remote_retrieve_body($response);

        codguard_log(sprintf('API Response: Status %d, Body: %s', $status_code, $response_body), 'debug');

        // Check status code
        if ($status_code !== 200 && $status_code !== 201) {
            return new WP_Error(
                'api_error',
                sprintf('API returned status code %d: %s', $status_code, $response_body)
            );
        }

        // Parse response
        $data = json_decode($response_body, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            return new WP_Error('json_error', 'Invalid JSON response from API');
        }

        return $data;
    }

    /**
     * AJAX manual sync
     */
    public function ajax_manual_sync() {
        // Verify nonce
        if (!check_ajax_referer('codguard_admin', 'nonce', false)) {
            wp_send_json_error(array(
                'message' => __('Security check failed.', 'codguard')
            ));
        }

        // Check permissions
        if (!current_user_can('manage_woocommerce')) {
            wp_send_json_error(array(
                'message' => __('You do not have sufficient permissions.', 'codguard')
            ));
        }

        // Get orders from yesterday
        $orders = $this->get_orders_from_yesterday();
        
        if (empty($orders)) {
            wp_send_json_success(array(
                'message' => __('No orders found for yesterday.', 'codguard'),
                'count' => 0
            ));
            return;
        }

        // Prepare and send
        $order_data = $this->prepare_order_data($orders);
        
        if (empty($order_data)) {
            wp_send_json_success(array(
                'message' => __('No valid orders found for yesterday (missing email addresses).', 'codguard'),
                'count' => 0
            ));
            return;
        }

        $result = $this->send_to_api($order_data);

        if (is_wp_error($result)) {
            // Update sync status
            update_option('codguard_last_sync_status', 'failed');
            update_option('codguard_last_sync_error', $result->get_error_message());
            
            wp_send_json_error(array(
                'message' => $result->get_error_message()
            ));
            return;
        }

        // Update sync status
        update_option('codguard_last_sync', current_time('mysql'));
        update_option('codguard_last_sync_status', 'success');
        update_option('codguard_last_sync_count', count($order_data));
        delete_option('codguard_last_sync_error');

        wp_send_json_success(array(
            'message' => sprintf(__('%d orders synced successfully. Refused status = -1, others = 1.', 'codguard'), count($order_data)),
            'count' => count($order_data)
        ));
    }

    /**
     * Get next scheduled sync time
     *
     * @return string|bool Formatted date string or false if not scheduled
     */
    public static function get_next_sync_time() {
        $timestamp = wp_next_scheduled(self::CRON_HOOK);
        
        if (!$timestamp) {
            return false;
        }

        return date_i18n(get_option('date_format') . ' ' . get_option('time_format'), $timestamp);
    }

    /**
     * Check if sync is scheduled
     *
     * @return bool True if scheduled
     */
    public static function is_scheduled() {
        return (bool) wp_next_scheduled(self::CRON_HOOK);
    }

    /**
     * Get last sync time
     *
     * @return string|bool Formatted date string or false if never run
     */
    public static function get_last_sync_time() {
        $last_sync = get_option('codguard_last_sync');
        
        if (!$last_sync) {
            return false;
        }

        return date_i18n(get_option('date_format') . ' ' . get_option('time_format'), strtotime($last_sync));
    }

    /**
     * Get last sync status
     *
     * @return string 'success', 'failed', or 'never'
     */
    public static function get_last_sync_status() {
        $status = get_option('codguard_last_sync_status');
        
        if (!$status) {
            return 'never';
        }

        return $status;
    }

    /**
     * Get last sync count
     *
     * @return int Number of orders synced
     */
    public static function get_last_sync_count() {
        return (int) get_option('codguard_last_sync_count', 0);
    }
}
