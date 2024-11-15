# Custom API Emails Manager

A WordPress plugin that provides an easy-to-use interface for customizing registration and password reset email templates.
Useful for headless WordPress sites.

## Description

Custom API Emails Manager allows WordPress administrators to customize the email templates used for password reset functionality. The plugin provides a user-friendly interface in the WordPress admin panel to modify email subjects and content while supporting HTML formatting and dynamic variables.

## Features

- Customizable password reset email templates
- HTML email support
- Easy-to-use admin interface
- Variable support for dynamic content
- Secure implementation with WordPress standards
- Custom "From" name and email address support

## Installation
Requires the following plugins:
- REST API Password Reset with Code https://en-gb.wordpress.org/plugins/bdvs-password-reset/

1. Download the plugin files
2. Upload the plugin folder to the `/wp-content/plugins/` directory
3. Activate the plugin through the 'Plugins' menu in WordPress
4. Navigate to 'Email Templates' in the WordPress admin menu

## Usage

### Accessing the Settings

1. Log in to your WordPress admin panel
2. Navigate to 'Email Templates' in the main menu
3. Configure your email templates

### Available Variables

The following variables can be used in password reset email templates:

- `{code}` - The reset code
- `{expiry}` - The expiration time
- `{site_name}` - Your website name
- `{user_email}` - The user's email address

### HTML Support

- The plugin supports HTML in email content
- Use `<br>` tags for line breaks
- If no HTML is detected, the content will be automatically formatted with paragraphs

## Requirements

- WordPress 5.0 or higher
- PHP 7.0 or higher

## Changelog

### 1.0.0
- Initial release
- Basic email template customization
- Password reset email support
- HTML email support
- Variable replacement functionality

## Support

For support questions, bug reports, or feature requests, please submit an issue through the plugin's repository.

## License

This plugin is licensed under the GPL v2 or later.

## Credits

Developed by [Your Name]

---

**Note:** This plugin is designed to work with WordPress's native password reset functionality and may require additional configuration depending on your specific setup.