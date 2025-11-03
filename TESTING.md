# CodGuard WooCommerce Plugin - Testing & Deployment Guide

## Pre-Deployment Testing Checklist

### Environment Setup
- [ ] WordPress 5.0+ installed
- [ ] WooCommerce 4.0+ installed and activated
- [ ] PHP 7.4+ configured
- [ ] WordPress debug mode enabled (WP_DEBUG = true)
- [ ] WooCommerce logger accessible

### Installation Testing
- [ ] Plugin activates without errors
- [ ] Plugin requires WooCommerce (test without WC installed)
- [ ] Plugin deactivates cleanly
- [ ] Default settings created on first activation
- [ ] No PHP warnings or notices in debug.log

### Admin Interface Testing

#### Menu & Access
- [ ] "CodGuard" menu appears under WooCommerce
- [ ] Page loads for admin users
- [ ] Page blocked for non-admin users (shop_manager can access)
- [ ] Page blocked for subscribers/customers

#### Settings Display
- [ ] All 8 fields render correctly
- [ ] Order statuses populate from WooCommerce
- [ ] Payment gateways populate correctly
- [ ] Current values display correctly
- [ ] Private key masked when set
- [ ] Status indicator shows "Disabled" initially
- [ ] CSS loads and styles apply correctly

### Form Validation Testing

#### Required Fields
- [ ] Submit with empty Shop ID shows error
- [ ] Submit with empty Public Key shows error
- [ ] Submit with empty Private Key (first time) shows error
- [ ] Submit with empty Rejection Message shows error

#### Field Validation
- [ ] Public Key < 10 chars shows error
- [ ] Private Key < 10 chars shows error
- [ ] Rating Tolerance < 0 shows error
- [ ] Rating Tolerance > 100 shows error
- [ ] Rejection Message > 500 chars shows error

#### Boundary Testing
- [ ] Rating Tolerance = 0 saves correctly
- [ ] Rating Tolerance = 100 saves correctly
- [ ] Rating Tolerance = 50 (mid-range) saves correctly
- [ ] Rejection Message at exactly 500 chars saves

### Data Storage Testing

#### Save Functionality
- [ ] Valid data saves successfully
- [ ] Success notice appears after save
- [ ] Error notice appears on validation failure
- [ ] Settings persist after browser refresh
- [ ] Settings persist after logout/login
- [ ] Multiple saves don't create duplicate options

#### Update Functionality
- [ ] Can update Shop ID
- [ ] Can update Public Key
- [ ] Can update Private Key (masked field)
- [ ] Can change order statuses
- [ ] Can add/remove COD methods
- [ ] Can adjust rating tolerance
- [ ] Can edit rejection message

#### Auto-Enable Logic
- [ ] Plugin disabled without credentials
- [ ] Plugin enabled when all 3 keys provided
- [ ] Plugin disabled if any key removed
- [ ] Status indicator updates correctly

### Security Testing

#### Nonce Verification
- [ ] Form submission requires valid nonce
- [ ] Expired nonce shows error
- [ ] Tampered nonce rejected

#### Capability Checks
- [ ] Admin can access settings
- [ ] Shop Manager can access settings
- [ ] Editor cannot access settings
- [ ] Subscriber cannot access settings
- [ ] Logged-out user cannot access

#### Input Sanitization
- [ ] HTML tags stripped from text fields
- [ ] Scripts stripped from all inputs
- [ ] SQL injection attempts sanitized
- [ ] Special characters handled correctly

#### Output Escaping
- [ ] Settings values properly escaped
- [ ] No XSS vulnerabilities in displayed data
- [ ] Private key never exposed in page source

### Edge Cases & Error Handling

#### No Payment Gateways
- [ ] Settings page loads without gateways
- [ ] Appropriate message displayed
- [ ] Form can still be saved

#### Custom Order Statuses
- [ ] Custom statuses appear in dropdown
- [ ] Custom statuses can be selected
- [ ] Custom statuses save correctly

#### Long Inputs
- [ ] Very long Shop ID (>100 chars) handled
- [ ] Very long API keys (>255 chars) handled
- [ ] Rejection message at max length works

#### Special Characters
- [ ] Settings with quotes save correctly
- [ ] Settings with apostrophes save correctly
- [ ] Settings with special chars (é, ñ, etc.) work
- [ ] Emoji in rejection message handled

### Helper Functions Testing

#### Settings Getters
```php
// Test these in functions.php or theme
$settings = codguard_get_settings();
$shop_id = codguard_get_shop_id();
$keys = codguard_get_api_keys();
$tolerance = codguard_get_tolerance();
$cod_methods = codguard_get_cod_methods();
$statuses = codguard_get_status_mappings();
$message = codguard_get_rejection_message();
$enabled = codguard_is_enabled();
```

- [ ] `codguard_get_settings()` returns array
- [ ] `codguard_get_shop_id()` returns string
- [ ] `codguard_get_api_keys()` returns array with 'public' and 'private'
- [ ] `codguard_get_tolerance()` returns integer
- [ ] `codguard_get_cod_methods()` returns array
- [ ] `codguard_get_status_mappings()` returns array with 'good' and 'refused'
- [ ] `codguard_get_rejection_message()` returns string
- [ ] `codguard_is_enabled()` returns boolean

### Database Testing

#### Options Table
- [ ] `codguard_settings` option created
- [ ] Option stored as serialized array
- [ ] Option updates correctly
- [ ] No orphaned options after deactivation
- [ ] Option uses autoload = yes (for performance)

#### Transients
- [ ] Success transient created after save
- [ ] Success transient deleted after display
- [ ] Error transient created on validation fail
- [ ] Error transient deleted after display
- [ ] Transients expire correctly (45 seconds)

### Performance Testing
- [ ] Settings page loads in < 1 second
- [ ] Form submission processes in < 2 seconds
- [ ] No N+1 queries
- [ ] Database queries optimized
- [ ] CSS/JS files load quickly

### Browser Compatibility
- [ ] Chrome/Edge (latest)
- [ ] Firefox (latest)
- [ ] Safari (latest)
- [ ] Mobile browsers (iOS Safari, Chrome Mobile)

### Responsive Design
- [ ] Desktop (1920x1080) displays correctly
- [ ] Laptop (1366x768) displays correctly
- [ ] Tablet (768x1024) displays correctly
- [ ] Mobile (375x667) displays correctly
- [ ] Form usable on all screen sizes

## Deployment Checklist

### Pre-Deployment
- [ ] All tests passing
- [ ] No PHP errors/warnings
- [ ] Debug mode tested
- [ ] Version number updated
- [ ] Changelog updated
- [ ] README.md completed
- [ ] readme.txt completed

### Code Quality
- [ ] WordPress Coding Standards followed
- [ ] All functions documented
- [ ] No deprecated functions used
- [ ] Translation-ready (all strings use textdomain)
- [ ] Security best practices implemented

### Files & Structure
- [ ] All required files present
- [ ] File permissions correct (644 for files, 755 for directories)
- [ ] .gitignore configured
- [ ] No development files included
- [ ] No sensitive data in repository

### Documentation
- [ ] Installation instructions clear
- [ ] Configuration steps documented
- [ ] API reference included
- [ ] Helper functions documented
- [ ] Screenshots prepared (for WordPress.org)

### Deployment Steps

1. **Prepare Package**
   ```bash
   cd /path/to/wp-content/plugins
   zip -r codguard-woocommerce.zip codguard-woocommerce/ -x "*.git*" "*.DS_Store" "node_modules/*"
   ```

2. **Test on Staging**
   - [ ] Upload to staging site
   - [ ] Activate plugin
   - [ ] Configure test settings
   - [ ] Verify all functionality
   - [ ] Check error logs

3. **Production Deployment**
   - [ ] Backup production database
   - [ ] Upload plugin to production
   - [ ] Activate plugin
   - [ ] Configure production settings
   - [ ] Test admin panel
   - [ ] Monitor error logs (first 24 hours)

4. **Post-Deployment**
   - [ ] Admin can access settings
   - [ ] Settings save correctly
   - [ ] No errors in logs
   - [ ] Plugin status correct
   - [ ] Documentation accessible

### Rollback Plan
If issues occur:
1. Deactivate plugin via WordPress admin
2. Delete plugin files (if necessary)
3. Restore database from backup (if corrupted)
4. Investigate logs
5. Fix issues
6. Redeploy

## Known Limitations (Phase 1)

- Private key stored in plaintext (consider encryption in future)
- No API connection testing yet (Phase 2)
- No order synchronization yet (Phase 3)
- No bulk actions for existing orders
- No import/export settings feature

## Testing Tools

### Recommended Plugins
- Query Monitor (check database queries)
- Debug Bar (check PHP errors)
- WP Mail Logging (for future email notifications)

### Manual Testing Commands

```php
// In functions.php or code snippets plugin
add_action('init', function() {
    if (current_user_can('manage_options')) {
        // Test settings retrieval
        $settings = codguard_get_settings();
        error_log(print_r($settings, true));
        
        // Test enabled status
        $enabled = codguard_is_enabled();
        error_log('Plugin enabled: ' . ($enabled ? 'Yes' : 'No'));
        
        // Test helper functions
        error_log('Shop ID: ' . codguard_get_shop_id());
        error_log('Tolerance: ' . codguard_get_tolerance());
    }
});
```

## Bug Reporting Template

```
**Bug Description:**
[Clear description of the issue]

**Steps to Reproduce:**
1. 
2. 
3. 

**Expected Behavior:**
[What should happen]

**Actual Behavior:**
[What actually happened]

**Environment:**
- WordPress Version: 
- WooCommerce Version: 
- PHP Version: 
- Plugin Version: 
- Browser: 

**Screenshots/Logs:**
[Attach relevant screenshots or error logs]
```

## Success Criteria

Phase 1 is considered complete when:
- ✅ All functionality tests pass
- ✅ All security tests pass
- ✅ No PHP errors or warnings
- ✅ Settings save and persist correctly
- ✅ Helper functions work as expected
- ✅ Ready for Phase 2 integration
- ✅ Documentation complete

---

**Last Updated**: 2025-11-01
**Phase**: 1 - Admin Panel & Data Storage
