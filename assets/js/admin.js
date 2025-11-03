/**
 * CodGuard Admin JavaScript
 * Includes Phase 1 (custom status creation) and Phase 3 (manual sync) functionality
 */

(function($) {
    'use strict';

    const CodGuardAdmin = {
        
        init: function() {
            this.bindEvents();
        },

        bindEvents: function() {
            // Phase 1: Custom status creation
            $('#codguard-create-status').on('click', this.createCustomStatus);
            
            // Phase 3: Manual order sync
            $('#codguard-manual-sync').on('click', this.manualSync);
        },

        /**
         * Create custom order status (Phase 1)
         */
        createCustomStatus: function(e) {
            e.preventDefault();
            
            const $button = $(this);
            const $input = $('#custom_status_name');
            const $message = $('#codguard-status-message');
            const statusName = $input.val().trim();

            // Validation
            if (!statusName) {
                CodGuardAdmin.showMessage($message, 'error', codguardAdminData.i18n.emptyStatusName);
                return;
            }

            if (statusName.length > 50) {
                CodGuardAdmin.showMessage($message, 'error', codguardAdminData.i18n.statusTooLong);
                return;
            }

            // Disable button and show loading
            $button.prop('disabled', true).text(codguardAdminData.i18n.creating);

            // AJAX request
            $.ajax({
                url: ajaxurl,
                type: 'POST',
                data: {
                    action: 'codguard_create_order_status',
                    nonce: codguardAdminData.nonce,
                    status_name: statusName
                },
                success: function(response) {
                    if (response.success) {
                        CodGuardAdmin.showMessage($message, 'success', response.data.message);
                        $input.val('');
                        
                        // Add new status to dropdowns
                        const statusSlug = response.data.slug;
                        const statusLabel = response.data.label;
                        
                        $('#good_status, #refused_status').append(
                            $('<option></option>')
                                .attr('value', statusSlug)
                                .text(statusLabel)
                        );

                        // Suggest page reload
                        setTimeout(function() {
                            if (confirm(codguardAdminData.i18n.reloadPage)) {
                                location.reload();
                            }
                        }, 1500);
                    } else {
                        CodGuardAdmin.showMessage($message, 'error', response.data.message || codguardAdminData.i18n.genericError);
                    }
                },
                error: function() {
                    CodGuardAdmin.showMessage($message, 'error', codguardAdminData.i18n.genericError);
                },
                complete: function() {
                    $button.prop('disabled', false).text(codguardAdminData.i18n.createStatus);
                }
            });
        },

        /**
         * Manual order sync (Phase 3)
         */
        manualSync: function(e) {
            e.preventDefault();
            
            const $button = $(this);
            const $message = $('#codguard-sync-message');
            const $icon = $button.find('.dashicons');
            const originalText = $button.find('.button-text').text();

            // Confirm action
            if (!confirm(codguardAdminData.i18n.confirmSync)) {
                return;
            }

            // Show loading state
            $button.prop('disabled', true);
            $button.find('.button-text').text(codguardAdminData.i18n.syncing);
            $icon.removeClass('dashicons-update').addClass('dashicons-update-alt codguard-spinning');
            $message.slideUp();

            // AJAX request
            $.ajax({
                url: ajaxurl,
                type: 'POST',
                data: {
                    action: 'codguard_manual_sync',
                    nonce: codguardAdminData.nonce
                },
                success: function(response) {
                    if (response.success) {
                        CodGuardAdmin.showMessage(
                            $message, 
                            'success', 
                            response.data.message || codguardAdminData.i18n.syncSuccess
                        );
                        
                        // Update sync status if element exists
                        if ($('#codguard-last-sync').length) {
                            $('#codguard-last-sync').text(codguardAdminData.i18n.justNow);
                        }
                        
                        // Reload page after 2 seconds to show updated status
                        setTimeout(function() {
                            location.reload();
                        }, 2000);
                    } else {
                        CodGuardAdmin.showMessage(
                            $message, 
                            'error', 
                            response.data.message || codguardAdminData.i18n.syncFailed
                        );
                    }
                },
                error: function(xhr, status, error) {
                    console.error('Sync error:', error);
                    CodGuardAdmin.showMessage(
                        $message, 
                        'error', 
                        codguardAdminData.i18n.syncError
                    );
                },
                complete: function() {
                    // Restore button state
                    $button.prop('disabled', false);
                    $button.find('.button-text').text(originalText);
                    $icon.removeClass('dashicons-update-alt codguard-spinning').addClass('dashicons-update');
                }
            });
        },

        /**
         * Show message helper
         */
        showMessage: function($container, type, message) {
            const cssClass = type === 'success' ? 'notice-success' : 'notice-error';
            
            $container
                .removeClass('notice-success notice-error')
                .addClass('notice ' + cssClass)
                .html('<p>' + message + '</p>')
                .slideDown();

            // Auto-hide success messages
            if (type === 'success') {
                setTimeout(function() {
                    $container.slideUp();
                }, 5000);
            }
        }
    };

    // Initialize on document ready
    $(document).ready(function() {
        CodGuardAdmin.init();
    });

})(jQuery);
