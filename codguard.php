<?php
/**
 * Plugin Name: CodGuard for WooCommerce
 * Plugin URI: https://codguard.com
 * Description: Integrates with the CodGuard API to manage cash-on-delivery payment options based on customer ratings and synchronize order data.
 * Version: 2.0.8
 * Author: CodGuard
 * Author URI: https://codguard.com
 * Text Domain: codguard
 * Domain Path: /languages
 * Requires at least: 5.0
 * Requires PHP: 7.4
 * WC requires at least: 4.0
 * WC tested up to: 8.0
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

// Define plugin constants
define('CODGUARD_VERSION', '2.0.8');
define('CODGUARD_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('CODGUARD_PLUGIN_URL', plugin_dir_url(__FILE__));
define('CODGUARD_PLUGIN_BASENAME', plugin_basename(__FILE__));

/**
 * Check if WooCommerce is active
 */
function codguard_is_woocommerce_active() {
    // Check for single site
    if (in_array('woocommerce/woocommerce.php', apply_filters('active_plugins', get_option('active_plugins', array())))) {
        return true;
    }
    
    // Check for multisite
    if (is_multisite()) {
        $plugins = get_site_option('active_sitewide_plugins');
        if (isset($plugins['woocommerce/woocommerce.php'])) {
            return true;
        }
    }
    
    // Alternative check: see if WooCommerce class exists
    return class_exists('WooCommerce');
}

/**
 * Display admin notice if WooCommerce is not active
 */
function codguard_woocommerce_missing_notice() {
    ?>
    <div class="notice notice-error">
        <p><?php _e('CodGuard for WooCommerce requires WooCommerce to be installed and active.', 'codguard'); ?></p>
    </div>
    <?php
}

/**
 * Initialize the plugin
 */
function codguard_init() {
    // Check if WooCommerce is active
    if (!codguard_is_woocommerce_active()) {
        add_action('admin_notices', 'codguard_woocommerce_missing_notice');
        return;
    }

    // Load plugin files
    require_once CODGUARD_PLUGIN_DIR . 'includes/functions.php';
    require_once CODGUARD_PLUGIN_DIR . 'includes/class-settings-manager.php';
    require_once CODGUARD_PLUGIN_DIR . 'includes/admin/class-admin-settings.php';
    require_once CODGUARD_PLUGIN_DIR . 'includes/class-order-sync.php';

    // Initialize admin settings
    if (is_admin()) {
        CodGuard_Admin_Settings::init();
    }

    // Initialize order sync if plugin is enabled
    if (function_exists('codguard_is_enabled') && codguard_is_enabled()) {
        $order_sync = new CodGuard_Order_Sync();
        
        // Schedule sync if not already scheduled
        add_action('init', array($order_sync, 'maybe_schedule_sync'));
    }
}
add_action('plugins_loaded', 'codguard_init');

/**
 * Plugin activation hook
 */
function codguard_activate() {
    // Check if WooCommerce is active
    if (!codguard_is_woocommerce_active()) {
        deactivate_plugins(plugin_basename(__FILE__));
        wp_die(__('CodGuard for WooCommerce requires WooCommerce to be installed and active.', 'codguard'));
    }

    // Load required files
    require_once CODGUARD_PLUGIN_DIR . 'includes/class-settings-manager.php';

    // Initialize default settings if not exists
    if (!get_option('codguard_settings')) {
        $defaults = CodGuard_Settings_Manager::get_default_settings();
        update_option('codguard_settings', $defaults);
    }
    
    // Load order sync class
    require_once CODGUARD_PLUGIN_DIR . 'includes/class-order-sync.php';
    
    // Schedule cron if plugin is enabled
    if (function_exists('codguard_is_enabled') && codguard_is_enabled()) {
        $order_sync = new CodGuard_Order_Sync();
        $order_sync->schedule_sync();
    }
}
register_activation_hook(__FILE__, 'codguard_activate');

/**
 * Plugin deactivation hook
 */
function codguard_deactivate() {
    // Clear scheduled cron
    wp_clear_scheduled_hook('codguard_daily_order_sync');
    
    // Clear any transients
    delete_transient('codguard_settings_saved');
    delete_transient('codguard_settings_errors');
    
    // Log deactivation
    if (function_exists('codguard_log')) {
        codguard_log('Plugin deactivated, cron schedule cleared', 'info');
    }
}
register_deactivation_hook(__FILE__, 'codguard_deactivate');

/**
 * Load plugin text domain for translations
 */
function codguard_load_textdomain() {
    load_plugin_textdomain('codguard', false, dirname(CODGUARD_PLUGIN_BASENAME) . '/languages');
}
add_action('init', 'codguard_load_textdomain');

/**
 * Declare HPOS (High-Performance Order Storage) compatibility
 */
add_action('before_woocommerce_init', function() {
    if (class_exists('\Automattic\WooCommerce\Utilities\FeaturesUtil')) {
        \Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility('custom_order_tables', __FILE__, true);
    }
});

/**
 * Add action links to plugin page
 */
function codguard_plugin_action_links($links) {
    $settings_link = '<a href="' . admin_url('admin.php?page=codguard-settings') . '">' . __('Settings', 'codguard') . '</a>';
    array_unshift($links, $settings_link);
    return $links;
}
add_filter('plugin_action_links_' . CODGUARD_PLUGIN_BASENAME, 'codguard_plugin_action_links');
