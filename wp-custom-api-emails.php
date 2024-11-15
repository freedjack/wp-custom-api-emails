<?php
/**
 * Plugin Name: Custom API Emails Manager
 * Description: Manage custom email templates for registration and password reset
 * Version: 1.0.0
 * Author: Your Name
 */

if (!defined('ABSPATH')) {
    exit;
}

class CustomAPIEmails {
    private static $instance = null;

    public static function getInstance() {
        if (self::$instance == null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function __construct() {
        add_action('admin_menu', array($this, 'addAdminMenu'));
        add_action('admin_init', array($this, 'registerSettings'));
        add_filter('bdpwr_code_email_subject', array($this, 'customResetSubject'), 10, 1);
        add_filter('bdpwr_code_email_text', array($this, 'customResetContent'), 10, 4);
        add_action('wp_mail_from', array($this, 'customEmailFrom'));
        add_action('wp_mail_from_name', array($this, 'customEmailFromName'));
        add_filter( 'bdpwr_selection_string' , function( $string ) {
            return '0123456789';
        }, 10, 4);
    }

    public function addAdminMenu() {
        add_menu_page(
            'Custom Email Templates',
            'Email Templates',
            'manage_options',
            'custom-email-templates',
            array($this, 'renderAdminPage'),
            'dashicons-email'
        );
    }

    public function registerSettings() {
        register_setting('custom_email_options', 'custom_email_settings');
        
        // Registration Email Settings
        add_settings_section(
            'registration_email_section',
            'New User Registration Email',
            null,
            'custom-email-templates'
        );

        // Reset Password Email Settings
        add_settings_section(
            'reset_email_section',
            'Password Reset Email',
            null,
            'custom-email-templates'
        );

        // Add settings fields
        $this->addEmailFields('registration_email_section', 'registration');
        $this->addEmailFields('reset_email_section', 'reset');
    }

    private function addEmailFields($section, $type) {
        add_settings_field(
            "{$type}_email_subject",
            'Email Subject',
            array($this, 'renderTextField'),
            'custom-email-templates',
            $section,
            array('field' => "{$type}_email_subject")
        );

        add_settings_field(
            "{$type}_email_content",
            'Email Content',
            array($this, 'renderTextArea'),
            'custom-email-templates',
            $section,
            array('field' => "{$type}_email_content")
        );
    }

    public function renderTextField($args) {
        $options = get_option('custom_email_settings');
        $value = isset($options[$args['field']]) ? $options[$args['field']] : '';
        echo "<input type='text' class='regular-text' name='custom_email_settings[{$args['field']}]' value='" . esc_attr($value) . "'>";
    }

    public function renderTextArea($args) {
        $options = get_option('custom_email_settings');
        $value = isset($options[$args['field']]) ? $options[$args['field']] : '';
        echo "<textarea class='large-text' rows='10' name='custom_email_settings[{$args['field']}]'>" . esc_textarea($value) . "</textarea>";
        
        if ($args['field'] === 'reset_email_content') {
            echo "<p class='description'>Available variables: {code}, {expiry}, {site_name}, {user_email}<br>HTML is supported. Use &lt;br&gt; for line breaks.</p>";
        }
    }

    public function renderAdminPage() {
        if (!current_user_can('manage_options')) {
            wp_die(__('You do not have sufficient permissions to access this page.'));
        }
        ?>
        <div class="wrap">
            <h1>Custom Email Templates</h1>
            <form method="post" action="options.php">
                <?php
                settings_fields('custom_email_options');
                do_settings_sections('custom-email-templates');
                submit_button();
                ?>
            </form>
        </div>
        <?php
    }

    public function customResetSubject($subject) {
        $options = get_option('custom_email_settings');
        return !empty($options['reset_email_subject']) ? $options['reset_email_subject'] : $subject;
    }

    public function customResetContent($text, $email, $code, $expiry) {
        $options = get_option('custom_email_settings');
        if (!empty($options['reset_email_content'])) {
            $content = $options['reset_email_content'];
            
            // Basic sanitization
            $code = trim($code);
            $user_email = sanitize_email($email);
            $expiry = sanitize_text_field($expiry);
            
            // Replace variables
            $replacements = array(
                '{code}' => $code,
                '{expiry}' => $expiry,
                '{site_name}' => get_bloginfo('name'),
                '{user_email}' => $user_email
            );
            
            $content = str_replace(array_keys($replacements), array_values($replacements), $content);
            
            // Ensure proper HTML formatting
            if (strpos($content, '<') === false) {
                $content = wpautop($content);
            }
            
            // Set content type to HTML
            add_filter('wp_mail_content_type', function() {
                return 'text/html';
            });
            
            return $content;
        }
        return $text;
    }

    public function customEmailFrom($email) {
        $options = get_option('custom_email_settings');
        return !empty($options['from_email']) ? $options['from_email'] : $email;
    }

    public function customEmailFromName($name) {
        $options = get_option('custom_email_settings');
        return !empty($options['from_name']) ? $options['from_name'] : $name;
    }
}

// Initialize the plugin
function init_custom_api_emails() {
    return CustomAPIEmails::getInstance();
}
add_action('plugins_loaded', 'init_custom_api_emails');
