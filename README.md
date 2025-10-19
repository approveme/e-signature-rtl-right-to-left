# WP E-Signature - RTL (Right-to-Left) Support Plugin

Version: 1.8.0  
Author: ApproveMe.com

## Overview

This plugin adds comprehensive Right-to-Left (RTL) language support to WP E-Signature for languages like Arabic (العربية), Hebrew (עברית), Persian (فارسی), Urdu (اردو), and other RTL languages.

## Requirements

- WordPress 5.0 or higher
- WP E-Signature plugin (main plugin must be installed first)
- WordPress language set to an RTL language

## Installation

1. Install and activate the main WP E-Signature plugin
2. Upload the RTL plugin to `/wp-content/plugins/e-signature-rtl-right-to-left/`
3. Activate the RTL plugin through the WordPress Plugins menu
4. Set WordPress language to an RTL language (Settings → General → Site Language)

## Features

### ✅ Automatic RTL Detection
- Automatically detects when WordPress is in RTL mode
- No configuration needed - works out of the box

### ✅ Frontend Support
- Document signing pages display in RTL
- Signature pads aligned properly for RTL
- Form elements (inputs, checkboxes, radio buttons) RTL-aware
- Two-column layouts flip automatically

### ✅ Admin Area Support
- TinyMCE editor with RTL/LTR direction controls
- Settings pages display correctly in RTL
- Add-ons page formatted for RTL
- Document lists in proper RTL order

### ✅ PDF Generation
- PDFs maintain RTL formatting
- Audit trails display correctly in RTL
- Signature placement is RTL-aware

### ✅ Mobile Support
- Optimized mobile RTL styles
- Touch-friendly RTL interface
- Responsive design for all screen sizes

## Quick Start

### Enable RTL Mode

1. Go to **WordPress Admin → Settings → General**
2. Set **Site Language** to an RTL language (e.g., "العربية" for Arabic)
3. Save changes
4. RTL support will activate automatically

### Create RTL Documents

1. Go to **E-Signature → Add New Document**
2. Use the TinyMCE editor RTL/LTR buttons to set text direction
3. Add fields and signers as normal
4. Documents will display in RTL for signers

### Test RTL Display

1. Create a test document with RTL text
2. Send to a test email address
3. Open the signing link
4. Verify RTL layout and styling

## What's New in Version 1.8.0

### Major Improvements

✅ **Proper Plugin Initialization**
- Enabled the `esig_rtl_init()` method
- Fixed plugin loading sequence
- Better integration with main plugin

✅ **WordPress Standard Compliance**
- Replaced `echo` statements with `wp_enqueue_style()`
- Proper style registration and dependencies
- Version cache busting

✅ **Enhanced Frontend Support**
- Automatic `dir="rtl"` attribute on HTML element
- RTL body classes for better styling control
- JavaScript helpers for dynamic RTL layout

✅ **Improved Admin Experience**
- RTL styles only load on E-Signature admin pages
- Better performance with conditional loading
- Fixed screen detection logic

✅ **Better PDF Support**
- Fixed RTL stylesheet filter
- Properly appends RTL styles to PDF exports
- Maintains RTL formatting in downloads

✅ **Code Quality**
- Fixed linter errors
- Improved documentation
- Added helper functions
- Better error handling

## File Structure

```
e-signature-rtl-right-to-left/
├── esig-rtl.php              # Main plugin file
├── README.md                 # This file
└── assets/
    └── css/
        ├── rtl.css                  # Main frontend RTL styles
        ├── rtl-admin.css            # Admin area RTL styles
        ├── rtl-basic-mobile.css     # Mobile RTL styles
        ├── rtl-basic.css            # Basic RTL styles
        ├── rtl-pdf.css              # PDF export RTL styles
        └── rtl-print.css            # Print media RTL styles
```

## Technical Details

### Hooks Used

```php
// Frontend
add_action("wp_enqueue_scripts", "esig_rtl_frontend_styles", 20);
add_filter("body_class", "add_rtl_body_class", 10, 1);
add_action("wp_head", "add_rtl_html_direction", 1);

// Admin
add_action("admin_enqueue_scripts", "enqueue_admin_styles", 20);

// PDF
add_filter("esig-pdf-export-stylesheet", "esig_rtl_pdf_styles", 10, 1);

// Templates
add_filter("esig_document_template", "add_rtl_template_support", 10, 3);
```

### Detection Functions

- `is_rtl_enabled()` - Checks if WordPress RTL mode is active
- `is_esignature_page()` - Detects E-Signature frontend pages
- `is_esignature_admin_page()` - Detects E-Signature admin pages

## Troubleshooting

### RTL Not Working?

1. **Check WordPress Language**
   - Go to Settings → General
   - Verify Site Language is set to an RTL language
   - Save changes if needed

2. **Clear Cache**
   - Clear browser cache (Ctrl+Shift+Delete)
   - Clear WordPress cache (if using caching plugin)
   - Clear CDN cache (if applicable)

3. **Verify Plugin Active**
   - Go to Plugins page
   - Ensure both WP E-Signature and RTL plugins are active
   - Try deactivating and reactivating the RTL plugin

4. **Check File Permissions**
   - Verify CSS files exist in `assets/css/` folder
   - Ensure files are readable by WordPress

### PDF Not RTL?

1. Verify WordPress is in RTL mode
2. Check that `rtl-pdf.css` file exists
3. Ensure Save as PDF add-on is active
4. Try generating a new PDF (not cached version)

### Admin Not RTL?

1. Confirm you're on an E-Signature admin page
2. Check WordPress admin language setting
3. Clear browser cache
4. Try a different browser

## Browser Support

- ✅ Chrome 90+
- ✅ Firefox 88+
- ✅ Safari 14+
- ✅ Edge 90+
- ✅ Mobile browsers (iOS Safari, Chrome Mobile)

## Performance

- CSS files only load when needed (RTL mode + E-Signature pages)
- Mobile-specific styles only for mobile devices
- Print styles only load for print media
- Admin styles only on admin pages
- Minimal JavaScript (< 1KB)

## Support

For issues or questions:
1. Check the [complete documentation](../e-signature/documentation/RTL_SUPPORT.md)
2. Review the Troubleshooting section above
3. Contact ApproveMe support

## Developer Resources

### Custom RTL Styles

Add to your theme's `functions.php`:

```php
add_action('wp_enqueue_scripts', 'custom_esig_rtl_styles', 25);
function custom_esig_rtl_styles() {
    if (is_rtl()) {
        wp_enqueue_style('custom-esig-rtl', 
            get_stylesheet_directory_uri() . '/css/esig-rtl-custom.css', 
            array('esig-rtl-css'), 
            '1.0'
        );
    }
}
```

### Filter PDF Styles

```php
add_filter('esig-pdf-export-stylesheet', 'custom_rtl_pdf', 15, 1);
function custom_rtl_pdf($stylesheet) {
    if (is_rtl()) {
        $stylesheet .= '
            .custom-class {
                direction: rtl;
                text-align: right;
            }
        ';
    }
    return $stylesheet;
}
```

## Changelog

### 1.8.0 (October 19, 2025)
- ✅ Enabled proper plugin initialization
- ✅ Replaced echo with wp_enqueue_style
- ✅ Added dir="rtl" to HTML element
- ✅ Added RTL body classes
- ✅ Improved PDF RTL support
- ✅ Added JavaScript RTL helpers
- ✅ Better page detection
- ✅ Fixed linter errors
- ✅ Comprehensive documentation

### 1.7.9
- Legacy version with basic RTL support

## License

This plugin is licensed under the same terms as the WP E-Signature plugin.

## Credits

**Developed by:** ApproveMe.com  
**Authors:** Kevin Michael Gray, Abu Shoaib  
**Website:** https://www.approveme.com/

---

*For complete technical documentation, see: `/wp-content/plugins/e-signature/documentation/RTL_SUPPORT.md`*

