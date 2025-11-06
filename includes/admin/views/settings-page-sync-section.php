<!-- Phase 3: Order Sync Status Section -->
<!-- This should be added to the settings-page.php after the Rating Settings section -->

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

    <!-- Next Sync Info Box -->
    <?php if ($is_scheduled && $next_sync) : ?>
    <div class="codguard-next-sync-info">
        <span class="dashicons dashicons-clock"></span>
        <div class="info-text">
            <strong><?php _e('Automatic Sync Scheduled', 'CodGuard-Woocommerce'); ?></strong>
            <p><?php printf(__('Orders from yesterday will be automatically uploaded to CodGuard at %s.', 'CodGuard-Woocommerce'), '<strong>' . esc_html($next_sync) . '</strong>'); ?></p>
        </div>
    </div>
    <?php endif; ?>

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

    <!-- Sync History (Optional) -->
    <?php 
    $sync_history = get_option('codguard_sync_history', array());
    if (!empty($sync_history)) :
    ?>
    <div class="codguard-sync-history">
        <h3><?php _e('Recent Sync History', 'CodGuard-Woocommerce'); ?></h3>
        <table>
            <thead>
                <tr>
                    <th><?php _e('Date/Time', 'CodGuard-Woocommerce'); ?></th>
                    <th><?php _e('Status', 'CodGuard-Woocommerce'); ?></th>
                    <th><?php _e('Orders', 'CodGuard-Woocommerce'); ?></th>
                    <th><?php _e('Details', 'CodGuard-Woocommerce'); ?></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach (array_slice($sync_history, 0, 5) as $entry) : ?>
                <tr>
                    <td><?php echo esc_html(date_i18n(get_option('date_format') . ' ' . get_option('time_format'), $entry['timestamp'])); ?></td>
                    <td>
                        <span class="status-<?php echo esc_attr($entry['status']); ?>">
                            <?php echo $entry['status'] === 'success' ? __('Success', 'CodGuard-Woocommerce') : __('Failed', 'CodGuard-Woocommerce'); ?>
                        </span>
                    </td>
                    <td><?php echo esc_html($entry['count']); ?></td>
                    <td><?php echo esc_html($entry['message']); ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php endif; ?>

    <!-- Help Section -->
    <div class="codguard-info-box" style="margin-top: 20px;">
        <h3><?php _e('How Order Sync Works', 'CodGuard-Woocommerce'); ?></h3>
        <ul style="margin-left: 20px;">
            <li><?php _e('Orders are automatically synced every day at 02:00 (site local time)', 'CodGuard-Woocommerce'); ?></li>
            <li><?php _e('Only COD (Cash on Delivery) orders are synced', 'CodGuard-Woocommerce'); ?></li>
            <li><?php _e('Order status is mapped to outcomes based on your configuration above', 'CodGuard-Woocommerce'); ?></li>
            <li><?php printf(__('Successful orders (status: %s) are reported as outcome: 1', 'CodGuard-Woocommerce'), '<code>' . esc_html($settings['good_status']) . '</code>'); ?></li>
            <li><?php printf(__('Refused orders (status: %s) are reported as outcome: -1', 'CodGuard-Woocommerce'), '<code>' . esc_html($settings['refused_status']) . '</code>'); ?></li>
            <li><?php _e('You can trigger a manual sync anytime using the "Sync Now" button', 'CodGuard-Woocommerce'); ?></li>
            <li><?php _e('View detailed logs in WooCommerce → Status → Logs → select "codguard"', 'CodGuard-Woocommerce'); ?></li>
        </ul>
    </div>
</div>
