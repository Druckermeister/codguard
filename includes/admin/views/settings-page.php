<?php
/**
 * Admin Settings Page Template
 * Includes Phase 1 (all original sections) + Phase 3 (sync status)
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

// Get order statuses and payment gateways
$order_statuses = codguard_get_order_statuses();
$payment_gateways = codguard_get_payment_gateways();
$is_enabled = CodGuard_Settings_Manager::is_enabled();
?>

<div class="wrap codguard-settings-wrap">
    <h1><?php _e('CodGuard Settings', 'CodGuard-Woocommerce'); ?></h1>
    
    <div class="codguard-settings-header">
        <p><?php _e('Configure your CodGuard integration to manage cash-on-delivery payments based on customer ratings.', 'CodGuard-Woocommerce'); ?></p>
        
        <?php if ($is_enabled) : ?>
            <div class="codguard-status codguard-status-enabled">
                <span class="dashicons dashicons-yes-alt"></span>
                <?php _e('Plugin Enabled', 'CodGuard-Woocommerce'); ?>
            </div>
        <?php else : ?>
            <div class="codguard-status codguard-status-disabled">
                <span class="dashicons dashicons-warning"></span>
                <?php _e('Plugin Disabled', 'CodGuard-Woocommerce'); ?>
            </div>
        <?php endif; ?>
    </div>

    <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
        <input type="hidden" name="action" value="codguard_save_settings">
        <?php wp_nonce_field('codguard_settings_save', 'codguard_nonce'); ?>

        <!-- Section 1: API Configuration -->
        <div class="codguard-settings-section">
            <h2><?php _e('API Configuration', 'CodGuard-Woocommerce'); ?></h2>
            <p class="description"><?php _e('Enter your CodGuard API credentials. You can find these in your CodGuard dashboard.', 'CodGuard-Woocommerce'); ?></p>

            <table class="form-table" role="presentation">
                <tbody>
                    <!-- Shop ID -->
                    <tr>
                        <th scope="row">
                            <label for="shop_id">
                                <?php _e('Shop ID', 'CodGuard-Woocommerce'); ?>
                                <span class="required">*</span>
                            </label>
                        </th>
                        <td>
                            <input 
                                type="text" 
                                name="shop_id" 
                                id="shop_id" 
                                value="<?php echo esc_attr($settings['shop_id']); ?>" 
                                class="regular-text" 
                                required
                            >
                            <p class="description">
                                <?php _e('Your unique shop identifier from CodGuard.', 'CodGuard-Woocommerce'); ?>
                            </p>
                        </td>
                    </tr>

                    <!-- Public Key -->
                    <tr>
                        <th scope="row">
                            <label for="public_key">
                                <?php _e('Public Key', 'CodGuard-Woocommerce'); ?>
                                <span class="required">*</span>
                            </label>
                        </th>
                        <td>
                            <input 
                                type="text" 
                                name="public_key" 
                                id="public_key" 
                                value="<?php echo esc_attr($settings['public_key']); ?>" 
                                class="regular-text" 
                                required
                                minlength="10"
                            >
                            <p class="description">
                                <?php _e('Your API public key (minimum 10 characters).', 'CodGuard-Woocommerce'); ?>
                            </p>
                        </td>
                    </tr>

                    <!-- Private Key -->
                    <tr>
                        <th scope="row">
                            <label for="private_key">
                                <?php _e('Private Key', 'CodGuard-Woocommerce'); ?>
                                <span class="required">*</span>
                            </label>
                        </th>
                        <td>
                            <?php if (!empty($settings['private_key'])) : ?>
                                <input 
                                    type="password" 
                                    name="private_key" 
                                    id="private_key" 
                                    value="<?php echo esc_attr($settings['private_key']); ?>" 
                                    class="regular-text" 
                                    placeholder="<?php _e('••••••••••••••••', 'CodGuard-Woocommerce'); ?>"
                                    minlength="10"
                                >
                                <p class="description">
                                    <?php _e('Private key is set. Leave blank to keep current value, or enter a new key to update.', 'CodGuard-Woocommerce'); ?>
                                </p>
                            <?php else : ?>
                                <input 
                                    type="password" 
                                    name="private_key" 
                                    id="private_key" 
                                    value="" 
                                    class="regular-text" 
                                    required
                                    minlength="10"
                                >
                                <p class="description">
                                    <?php _e('Your API private key (minimum 10 characters). Keep this secure!', 'CodGuard-Woocommerce'); ?>
                                </p>
                            <?php endif; ?>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Section 2: Order Status Mapping -->
        <div class="codguard-settings-section">
            <h2><?php _e('Order Status Mapping', 'CodGuard-Woocommerce'); ?></h2>
            <p class="description"><?php _e('Map WooCommerce order statuses to CodGuard outcomes for order reporting.', 'CodGuard-Woocommerce'); ?></p>

            <table class="form-table" role="presentation">
                <tbody>
                    <!-- Successful Order Status -->
                    <tr>
                        <th scope="row">
                            <label for="good_status">
                                <?php _e('Successful Order Status', 'CodGuard-Woocommerce'); ?>
                            </label>
                        </th>
                        <td>
                            <select name="good_status" id="good_status" class="regular-text">
                                <?php foreach ($order_statuses as $status_slug => $status_name) : ?>
                                    <option value="<?php echo esc_attr(str_replace('wc-', '', $status_slug)); ?>" <?php selected($settings['good_status'], str_replace('wc-', '', $status_slug)); ?>>
                                        <?php echo esc_html($status_name); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <p class="description">
                                <?php _e('Orders with this status will be marked as successful (outcome: 1) when reported to CodGuard.', 'CodGuard-Woocommerce'); ?>
                            </p>
                        </td>
                    </tr>

                    <!-- Refused Order Status -->
                    <tr>
                        <th scope="row">
                            <label for="refused_status">
                                <?php _e('Refused Order Status', 'CodGuard-Woocommerce'); ?>
                            </label>
                        </th>
                        <td>
                            <select name="refused_status" id="refused_status" class="regular-text">
                                <?php foreach ($order_statuses as $status_slug => $status_name) : ?>
                                    <option value="<?php echo esc_attr(str_replace('wc-', '', $status_slug)); ?>" <?php selected($settings['refused_status'], str_replace('wc-', '', $status_slug)); ?>>
                                        <?php echo esc_html($status_name); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <p class="description">
                                <?php _e('Orders with this status will be marked as refused (outcome: -1) when reported to CodGuard.', 'CodGuard-Woocommerce'); ?>
                            </p>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Section 3: Payment Method Configuration -->
        <div class="codguard-settings-section">
            <h2><?php _e('Payment Method Configuration', 'CodGuard-Woocommerce'); ?></h2>
            <p class="description"><?php _e('Select which payment methods should trigger customer rating checks.', 'CodGuard-Woocommerce'); ?></p>

            <table class="form-table" role="presentation">
                <tbody>
                    <tr>
                        <th scope="row">
                            <label>
                                <?php _e('Cash on Delivery Methods', 'CodGuard-Woocommerce'); ?>
                            </label>
                        </th>
                        <td>
                            <?php if (!empty($payment_gateways)) : ?>
                                <fieldset>
                                    <?php foreach ($payment_gateways as $gateway_id => $gateway_title) : ?>
                                        <label style="display: block; margin-bottom: 8px;">
                                            <input 
                                                type="checkbox" 
                                                name="cod_methods[]" 
                                                value="<?php echo esc_attr($gateway_id); ?>"
                                                <?php checked(in_array($gateway_id, $settings['cod_methods'])); ?>
                                            >
                                            <?php echo esc_html($gateway_title); ?>
                                        </label>
                                    <?php endforeach; ?>
                                </fieldset>
                                <p class="description">
                                    <?php _e('Select all payment methods that should trigger customer rating checks. Typically, this includes cash on delivery methods.', 'CodGuard-Woocommerce'); ?>
                                </p>
                            <?php else : ?>
                                <p class="description">
                                    <?php _e('No payment gateways are currently available. Please configure your WooCommerce payment methods first.', 'CodGuard-Woocommerce'); ?>
                                </p>
                            <?php endif; ?>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Section 4: Rating Settings -->
        <div class="codguard-settings-section">
            <h2><?php _e('Rating Settings', 'CodGuard-Woocommerce'); ?></h2>
            <p class="description"><?php _e('Configure how customer ratings affect payment method availability.', 'CodGuard-Woocommerce'); ?></p>

            <table class="form-table" role="presentation">
                <tbody>
                    <!-- Rating Tolerance -->
                    <tr>
                        <th scope="row">
                            <label for="rating_tolerance">
                                <?php _e('Rating Tolerance', 'CodGuard-Woocommerce'); ?>
                            </label>
                        </th>
                        <td>
                            <input 
                                type="number" 
                                name="rating_tolerance" 
                                id="rating_tolerance" 
                                value="<?php echo esc_attr($settings['rating_tolerance']); ?>" 
                                min="0" 
                                max="100" 
                                step="1"
                                class="small-text"
                            > %
                            <p class="description">
                                <?php _e('Customers with a rating below this threshold will not be able to use COD payment methods. Recommended: 30-40%.', 'CodGuard-Woocommerce'); ?>
                            </p>
                        </td>
                    </tr>

                    <!-- Rejection Message -->
                    <tr>
                        <th scope="row">
                            <label for="rejection_message">
                                <?php _e('Rejection Message', 'CodGuard-Woocommerce'); ?>
                            </label>
                        </th>
                        <td>
                            <textarea 
                                name="rejection_message" 
                                id="rejection_message" 
                                rows="3" 
                                class="large-text"
                                maxlength="500"
                                required
                            ><?php echo esc_textarea($settings['rejection_message']); ?></textarea>
                            <p class="description">
                                <?php _e('This message will be displayed to customers whose rating is below the tolerance threshold. Maximum 500 characters.', 'CodGuard-Woocommerce'); ?>
                            </p>
                        </td>
                    </tr>

                    <!-- Notification Email -->
                    <tr>
                        <th scope="row">
                            <label for="notification_email">
                                <?php _e('Notification Email', 'CodGuard-Woocommerce'); ?>
                            </label>
                        </th>
                        <td>
                            <input 
                                type="email" 
                                name="notification_email" 
                                id="notification_email" 
                                value="<?php echo esc_attr($settings['notification_email']); ?>" 
                                class="regular-text"
                            >
                            <p class="description">
                                <?php _e('Email address for API error notifications and alerts. Default: info@codguard.com', 'CodGuard-Woocommerce'); ?>
                            </p>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Save Button -->
        <p class="submit">
            <?php submit_button(__('Save Settings', 'CodGuard-Woocommerce'), 'primary', 'submit', false); ?>
        </p>
    </form>

    <!-- PHASE 3: Order Sync Status Section -->
    <?php if (class_exists('CodGuard_Order_Sync')) : ?>
    <div class="codguard-settings-section codguard-sync-status-section">
        <h2><?php _e('Order Sync Status', 'CodGuard-Woocommerce'); ?></h2>
        <p class="description"><?php _e('Daily order synchronization with CodGuard API. Orders are uploaded at 02:00 local time.', 'CodGuard-Woocommerce'); ?></p>

        <?php
        // Get sync status
        $is_scheduled = CodGuard_Order_Sync::is_scheduled();
        $next_sync = CodGuard_Order_Sync::get_next_sync_time();
        $last_sync = get_option('codguard_last_sync_time', false);
        $last_sync_status = get_option('codguard_last_sync_status', 'unknown');
        $last_sync_count = get_option('codguard_last_sync_count', 0);
        ?>

        <!-- Sync Status Grid -->
        <div class="codguard-sync-status-grid">
            <!-- Schedule Status -->
            <div class="codguard-sync-status-item">
                <h4><?php _e('Schedule Status', 'CodGuard-Woocommerce'); ?></h4>
                <div class="value">
                    <?php if ($is_scheduled && $is_enabled) : ?>
                        <span class="codguard-sync-badge success">
                            <span class="dashicons dashicons-yes-alt"></span>
                            <?php _e('Active', 'CodGuard-Woocommerce'); ?>
                        </span>
                    <?php else : ?>
                        <span class="codguard-sync-badge error">
                            <span class="dashicons dashicons-warning"></span>
                            <?php _e('Inactive', 'CodGuard-Woocommerce'); ?>
                        </span>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Next Sync Time -->
            <div class="codguard-sync-status-item">
                <h4><?php _e('Next Scheduled Sync', 'CodGuard-Woocommerce'); ?></h4>
                <div class="value <?php echo $is_scheduled ? 'success' : 'pending'; ?>">
                    <?php 
                    if ($next_sync) {
                        echo esc_html($next_sync);
                    } else {
                        _e('Not scheduled', 'CodGuard-Woocommerce');
                    }
                    ?>
                </div>
            </div>

            <!-- Last Sync Status -->
            <div class="codguard-sync-status-item">
                <h4><?php _e('Last Sync', 'CodGuard-Woocommerce'); ?></h4>
                <div class="value">
                    <?php if ($last_sync) : ?>
                        <span id="codguard-last-sync">
                            <?php echo esc_html(human_time_diff($last_sync, current_time('timestamp')) . ' ' . __('ago', 'CodGuard-Woocommerce')); ?>
                        </span>
                        <br>
                        <span class="codguard-sync-badge <?php echo $last_sync_status === 'success' ? 'success' : 'error'; ?>">
                            <?php
                            if ($last_sync_status === 'success') {
                                /* translators: %d: number of orders synced */
                                printf(__('%d orders synced', 'CodGuard-Woocommerce'), $last_sync_count);
                            } else {
                                _e('Failed', 'CodGuard-Woocommerce');
                            }
                            ?>
                        </span>
                    <?php else : ?>
                        <span class="codguard-sync-badge pending">
                            <?php _e('Never run', 'CodGuard-Woocommerce'); ?>
                        </span>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Manual Sync Button -->
        <div style="margin-top: 20px;">
            <button type="button" id="codguard-manual-sync" class="button button-secondary">
                <span class="dashicons dashicons-update"></span>
                <span class="button-text"><?php _e('Sync Now', 'CodGuard-Woocommerce'); ?></span>
            </button>
            <p class="description" style="margin-top: 10px;">
                <?php _e('Manually trigger order synchronization for yesterday\'s orders. This will upload all COD orders from the previous day to CodGuard.', 'CodGuard-Woocommerce'); ?>
            </p>
        </div>

        <!-- Sync Message Container -->
        <div id="codguard-sync-message" style="display: none;"></div>
    </div>
    <?php endif; ?>

</div>
