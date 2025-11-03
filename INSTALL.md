# CodGuard WooCommerce Plugin - Quick Installation Guide

## Prerequisites

Before installing, ensure you have:
- ✅ WordPress 5.0 or higher
- ✅ WooCommerce 4.0 or higher installed and activated
- ✅ PHP 7.4 or higher
- ✅ CodGuard account with API credentials

## Installation Methods

### Method 1: WordPress Admin Upload (Recommended)

1. **Download the Plugin**
   - Download the `codguard-woocommerce.zip` file

2. **Upload via WordPress Admin**
   - Log in to your WordPress admin panel
   - Navigate to `Plugins > Add New`
   - Click `Upload Plugin` button at the top
   - Click `Choose File` and select `codguard-woocommerce.zip`
   - Click `Install Now`

3. **Activate the Plugin**
   - After installation, click `Activate Plugin`
   - You'll be redirected to the Plugins page

4. **Configure Settings**
   - Go to `WooCommerce > CodGuard` in the admin menu
   - Enter your API credentials
   - Configure your settings (see Configuration section below)

### Method 2: FTP/SFTP Upload

1. **Extract the Plugin**
   - Unzip `codguard-woocommerce.zip` on your computer

2. **Upload via FTP**
   - Connect to your server via FTP/SFTP
   - Navigate to `/wp-content/plugins/`
   - Upload the entire `codguard-woocommerce` folder

3. **Activate via WordPress Admin**
   - Log in to WordPress admin
   - Go to `Plugins > Installed Plugins`
   - Find "CodGuard for WooCommerce"
   - Click `Activate`

4. **Configure Settings**
   - Go to `WooCommerce > CodGuard`
   - Enter your API credentials

### Method 3: WP-CLI (For Developers)

```bash
# Navigate to WordPress root directory
cd /path/to/wordpress

# Install the plugin
wp plugin install /path/to/codguard-woocommerce.zip

# Activate the plugin
wp plugin activate codguard-woocommerce

# Verify activation
wp plugin list | grep codguard
```

## Configuration Steps

### 1. Access Settings Page
- Navigate to `WooCommerce > CodGuard` in WordPress admin
- The settings page will load

### 2. Configure API Credentials

**Required Fields:**
- **Shop ID**: Your CodGuard shop identifier
  - Get this from your CodGuard dashboard
  - Example: `shop_123456`

- **Public Key**: Your API public key
  - Minimum 10 characters
  - Get this from CodGuard dashboard > API Settings

- **Private Key**: Your API private key
  - Minimum 10 characters
  - Keep this secure and private
  - Get this from CodGuard dashboard > API Settings

### 3. Configure Order Status Mapping

- **Successful Order Status**
  - Select the status that indicates a successful order
  - Default: `Completed`
  - This maps to `outcome: 1` in CodGuard API

- **Refused Order Status**
  - Select the status that indicates a refused/cancelled order
  - Default: `Cancelled`
  - This maps to `outcome: -1` in CodGuard API

### 4. Select Payment Methods

- **Cash on Delivery Methods**
  - Check all payment methods that should trigger rating checks
  - Typically includes: Cash on Delivery, Bank Transfer COD, etc.
  - Multiple selections allowed

### 5. Configure Rating Settings

- **Rating Tolerance**
  - Set the minimum rating threshold (0-100%)
  - Default: 35%
  - Recommended: 30-40%
  - Customers below this rating cannot use COD

- **Rejection Message**
  - Customize the message shown to customers below threshold
  - Maximum 500 characters
  - Default: "Unfortunately, we cannot offer Cash on Delivery for this order."
  - Make it polite and helpful

### 6. Save Settings

- Click `Save Settings` button
- Look for success message
- Plugin will auto-enable once all API credentials are entered

## Verification Steps

After installation and configuration:

### 1. Check Plugin Status
- Go to `WooCommerce > CodGuard`
- Look for green "Plugin Enabled" badge
- If yellow "Plugin Disabled", verify API credentials

### 2. Verify Settings Saved
- Refresh the settings page
- All fields should show your saved values
- Private key should be masked (••••••)

### 3. Check for Errors
- Look for any error messages on the settings page
- Check WordPress debug log if enabled
- WooCommerce > Status > Logs > Select "codguard"

### 4. Test Helper Functions (Optional)
Add this code to test if settings are accessible:

```php
// Add to functions.php temporarily
add_action('init', function() {
    if (current_user_can('manage_options')) {
        error_log('CodGuard Enabled: ' . (codguard_is_enabled() ? 'Yes' : 'No'));
        error_log('Shop ID: ' . codguard_get_shop_id());
        error_log('Tolerance: ' . codguard_get_tolerance() . '%');
    }
});
```

Check your debug.log for the output.

## Troubleshooting

### Plugin Won't Activate
**Issue**: Error message appears when activating

**Solutions**:
- Ensure WooCommerce is installed and active
- Check PHP version (requires 7.4+)
- Check WordPress version (requires 5.0+)
- Look for plugin conflicts

### Settings Won't Save
**Issue**: Settings page shows errors or won't save

**Solutions**:
- Check all required fields are filled
- Verify keys are at least 10 characters
- Check rating tolerance is between 0-100
- Clear browser cache and try again

### Menu Not Appearing
**Issue**: "CodGuard" menu not visible under WooCommerce

**Solutions**:
- Verify plugin is activated
- Check user has `manage_woocommerce` capability
- Try logging out and back in
- Check for JavaScript errors in browser console

### Plugin Disabled Status
**Issue**: Settings saved but status shows "Plugin Disabled"

**Solutions**:
- Verify all three API credentials are entered:
  - Shop ID
  - Public Key
  - Private Key
- Re-enter credentials and save again
- Check for validation errors

## Getting API Credentials

1. **Sign up at CodGuard**
   - Visit https://codguard.com
   - Create an account or log in

2. **Access API Settings**
   - Go to Dashboard > Settings > API
   - Find your Shop ID
   - Generate API keys if not already created

3. **Copy Credentials**
   - Shop ID
   - Public Key (API Key)
   - Private Key (API Secret)

4. **Enter in WordPress**
   - Paste into WooCommerce > CodGuard settings
   - Save settings

## Next Steps

After Phase 1 installation is complete:

### Phase 2: Customer Rating Check (Coming Soon)
- Customer ratings will be checked at checkout
- COD methods blocked for low-rated customers
- Custom message displayed to customers

### Phase 3: Daily Order Upload (Coming Soon)
- Orders automatically synced to CodGuard
- Customer ratings updated based on order outcomes
- Scheduled daily via WP-Cron

## Support

Need help?

- **Documentation**: https://codguard.com/docs
- **Email Support**: support@codguard.com
- **Plugin Issues**: Check WordPress debug logs
- **WooCommerce Issues**: WooCommerce > Status > Logs

## Uninstallation

To completely remove the plugin:

1. **Deactivate Plugin**
   - Go to Plugins > Installed Plugins
   - Click "Deactivate" under CodGuard

2. **Delete Plugin**
   - After deactivation, click "Delete"
   - Confirm deletion

3. **Clean Up (Optional)**
   - Settings are removed automatically
   - No database cleanup needed

## Security Best Practices

- ✅ Keep API keys secure
- ✅ Use strong, unique private key
- ✅ Don't share API credentials
- ✅ Regularly update the plugin
- ✅ Monitor API usage in CodGuard dashboard
- ✅ Review access logs periodically

---

**Installation Complete!** 🎉

Your CodGuard plugin is now ready to use. Configure your settings and wait for Phase 2 to start checking customer ratings at checkout.

**Version**: 1.0.0  
**Last Updated**: 2025-11-01
