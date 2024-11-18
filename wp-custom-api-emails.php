<?php
/**
 * Plugin Name: Custom API Emails Manager
 * Plugin URI: https://yourwebsite.com/plugins/custom-api-emails
 * Description: Manage custom email templates for registration, password reset, and OTP verification.
 * Version: 1.0.0
 * Requires at least: 5.0
 * Requires PHP: 7.2
 * Author: Your Name
 * Author URI: https://yourwebsite.com
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: custom-api-emails
 *
 * @package CustomAPIEmails
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Main plugin class
 *
 * @since 1.0.0
 */
class Custom_API_Emails {

    /**
     * Instance of this class
     *
     * @since 1.0.0
     * @var object
     */
    private static $instance = null;

    /**
     * Plugin options
     *
     * @since 1.0.0
     * @var array
     */
    private $options;

    /**
     * Get singleton instance
     *
     * @since 1.0.0
     * @return Custom_API_Emails
     */
    public static function get_instance() {
        if ( null === self::$instance ) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Constructor
     *
     * @since 1.0.0
     */
    private function __construct() {
        $this->options = get_option( 'custom_email_settings', array() );
        $this->init_hooks();
    }


    /**
     * Initialize plugin components
     *
     * @since 1.0.0
     * @return void
     */
    private function init_components() {
        $this->otp_handler = new Custom_API_Emails_OTP( $this );
    }

    /**
     * Initialize WordPress hooks
     *
     * @since 1.0.0
     * @return void
     */
    private function init_hooks() {
        // Admin hooks
        add_action( 'admin_menu', array( $this, 'add_admin_menu' ) );
        add_action( 'admin_init', array( $this, 'register_settings' ) );

        // Email customization hooks
        add_filter( 'bdpwr_code_email_subject', array( $this, 'custom_reset_subject' ), 10, 1 );
        add_filter( 'bdpwr_code_email_text', array( $this, 'custom_reset_content' ), 10, 4 );
        add_filter( 'custom_otp_email_subject', array( $this, 'custom_otp_subject' ), 10, 1 );
        add_filter( 'custom_otp_email_message', array( $this, 'custom_otp_content' ), 10, 2 );
        add_action( 'wp_mail_from', array( $this, 'custom_email_from' ) );
        add_action( 'wp_mail_from_name', array( $this, 'custom_email_from_name' ) );
    }

    /**
     * Add admin menu
     *
     * @since 1.0.0
     * @return void
     */
    public function add_admin_menu() {
        add_menu_page(
            __( 'Email Templates', 'custom-api-emails' ),
            __( 'Email Templates', 'custom-api-emails' ),
            'manage_options',
            'custom-email-templates',
            array( $this, 'render_admin_page' ),
            'dashicons-email'
        );
    }

    /**
     * Register plugin settings
     *
     * @since 1.0.0
     * @return void
     */
    public function register_settings() {
        register_setting( 'custom_email_options', 'custom_email_settings' );

        // Email From Settings
        add_settings_section(
            'email_from_section',
            __( 'Email From Settings', 'custom-api-emails' ),
            null,
            'custom-email-templates'
        );

        add_settings_field(
            'from_email',
            __( 'From Email', 'custom-api-emails' ),
            array( $this, 'render_text_field' ),
            'custom-email-templates',
            'email_from_section',
            array( 'field' => 'from_email' )
        );

        add_settings_field(
            'from_name',
            __( 'From Name', 'custom-api-emails' ),
            array( $this, 'render_text_field' ),
            'custom-email-templates',
            'email_from_section',
            array( 'field' => 'from_name' )
        );

        // Password Reset Email Settings
        add_settings_section(
            'reset_email_section',
            __( 'Password Reset Email', 'custom-api-emails' ),
            null,
            'custom-email-templates'
        );

        $this->add_email_fields( 'reset_email_section', 'reset' );

        // OTP Email Settings
        add_settings_section(
            'otp_email_section',
            __( 'OTP Email', 'custom-api-emails' ),
            null,
            'custom-email-templates'
        );

        $this->add_email_fields( 'otp_email_section', 'otp' );
    }

    /**
     * Add email fields for a section
     *
     * @since 1.0.0
     * @param string $section Section ID.
     * @param string $prefix Field prefix.
     * @return void
     */
    private function add_email_fields( $section, $prefix ) {
        add_settings_field(
            "{$prefix}_email_subject",
            __( 'Subject', 'custom-api-emails' ),
            array( $this, 'render_text_field' ),
            'custom-email-templates',
            $section,
            array( 'field' => "{$prefix}_email_subject" )
        );

        add_settings_field(
            "{$prefix}_email_content",
            __( 'Content', 'custom-api-emails' ),
            array( $this, 'render_textarea_field' ),
            'custom-email-templates',
            $section,
            array( 'field' => "{$prefix}_email_content" )
        );
    }

    /**
     * Render admin page
     *
     * @since 1.0.0
     * @return void
     */
    public function render_admin_page() {
        // Check user capabilities
        if ( ! current_user_can( 'manage_options' ) ) {
            return;
        }

        include plugin_dir_path( __FILE__ ) . 'templates/admin-page.php';
    }

    /**
     * Custom reset email subject
     *
     * @since 1.0.0
     * @param string $subject Default subject.
     * @return string
     */
    public function custom_reset_subject( $subject ) {
        return ! empty( $this->options['reset_email_subject'] )
            ? $this->options['reset_email_subject']
            : $subject;
    }

    /**
     * Custom reset email content
     *
     * @since 1.0.0
     * @param string $content Default content.
     * @param string $code Reset code.
     * @param string $expiry Expiry time.
     * @param string $email User email.
     * @return string
     */
    public function custom_reset_content( $content, $code, $expiry, $email ) {
        if ( empty( $this->options['reset_email_content'] ) ) {
            return $content;
        }

        $message = $this->options['reset_email_content'];
        $replacements = array(
            '{code}'      => $code,
            '{expiry}'    => $expiry,
            '{email}'     => $email,
            '{site_name}' => get_bloginfo( 'name' ),
        );

        $message = str_replace( array_keys( $replacements ), array_values( $replacements ), $message );

        return strpos( $message, '<' ) === false ? wpautop( $message ) : $message;
    }

    /**
     * Custom email from address
     *
     * @since 1.0.0
     * @param string $email Default from email.
     * @return string
     */
    public function custom_email_from( $email ) {
        return ! empty( $this->options['from_email'] )
            ? $this->options['from_email']
            : $email;
    }

    /**
     * Custom email from name
     *
     * @since 1.0.0
     * @param string $name Default from name.
     * @return string
     */
    public function custom_email_from_name( $name ) {
        return ! empty( $this->options['from_name'] )
            ? $this->options['from_name']
            : $name;
    }

    /**
     * Custom OTP email subject
     *
     * @since 1.0.0
     * @param string $subject Default subject.
     * @return string
     */
    public function custom_otp_subject( $subject ) {
        return ! empty( $this->options['otp_email_subject'] )
            ? $this->options['otp_email_subject']
            : $subject;
    }

    /**
     * Custom OTP email content
     *
     * @since 1.0.0
     * @param string $content Default content.
     * @param string $otp OTP code.
     * @return string
     */
    public function custom_otp_content( $content, $otp ) {
        if ( empty( $this->options['otp_email_content'] ) ) {
            $content = __( 'Your one-time password is: {otp}. This code will expire in 10 minutes.', 'custom-api-emails' );
        } else {
            $content = $this->options['otp_email_content'];
        }

        $replacements = array(
            '{otp}'       => $otp,
            '{site_name}' => get_bloginfo( 'name' ),
        );

        $content = str_replace( array_keys( $replacements ), array_values( $replacements ), $content );

        // Add HTML formatting if plain text
        if ( strpos( $content, '<' ) === false ) {
            $content = wpautop( $content );
        }

        return $content;
    }

    /**
     * Render text field
     *
     * @since 1.0.0
     * @param array $args Field arguments.
     * @return void
     */
    public function render_text_field( $args ) {
        $field = $args['field'];
        $value = isset( $this->options[$field] ) ? $this->options[$field] : '';
        printf(
            '<input type="text" id="%1$s" name="custom_email_settings[%1$s]" value="%2$s" class="regular-text">',
            esc_attr( $field ),
            esc_attr( $value )
        );
    }

    /**
     * Render textarea field
     *
     * @since 1.0.0
     * @param array $args Field arguments.
     * @return void
     */
    public function render_textarea_field( $args ) {
        $field = $args['field'];
        $value = isset( $this->options[$field] ) ? $this->options[$field] : '';
        printf(
            '<textarea id="%1$s" name="custom_email_settings[%1$s]" rows="10" class="large-text">%2$s</textarea>',
            esc_attr( $field ),
            esc_textarea( $value )
        );
    }
}

/**
 * Initialize the plugin
 *
 * @since 1.0.0
 * @return Custom_API_Emails
 */
function custom_api_emails() {
    return Custom_API_Emails::get_instance();
}

// Initialize the plugin when WordPress loads.
add_action( 'plugins_loaded', 'custom_api_emails' );
