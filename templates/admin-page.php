<?php
/**
 * Admin page template for email settings
 *
 * @package CustomAPIEmails
 * @since 1.0.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
?>

<div class="wrap">
    <h1><?php echo esc_html( get_admin_page_title() ); ?></h1>
    
    <form action="options.php" method="post">
        <?php
        // Output security fields
        settings_fields( 'custom_email_options' );
        
        // Output setting sections and their fields
        do_settings_sections( 'custom-email-templates' );
        
        // Output save settings button
        submit_button( __( 'Save Settings', 'custom-api-emails' ) );
        ?>
    </form>

    <div class="email-template-variables">
        <h3><?php esc_html_e( 'Available Variables', 'custom-api-emails' ); ?></h3>
        
        <h4><?php esc_html_e( 'Password Reset Email', 'custom-api-emails' ); ?></h4>
        <ul>
            <li><code>{code}</code> - <?php esc_html_e( 'The reset code', 'custom-api-emails' ); ?></li>
            <li><code>{expiry}</code> - <?php esc_html_e( 'The expiration time', 'custom-api-emails' ); ?></li>
            <li><code>{email}</code> - <?php esc_html_e( 'The user email', 'custom-api-emails' ); ?></li>
            <li><code>{site_name}</code> - <?php esc_html_e( 'Your website name', 'custom-api-emails' ); ?></li>
        </ul>

        <h4><?php esc_html_e( 'OTP Email', 'custom-api-emails' ); ?></h4>
        <ul>
            <li><code>{otp}</code> - <?php esc_html_e( 'The OTP code', 'custom-api-emails' ); ?></li>
            <li><code>{site_name}</code> - <?php esc_html_e( 'Your website name', 'custom-api-emails' ); ?></li>
        </ul>

        <p class="description">
            <?php esc_html_e( 'HTML is supported in email content. If no HTML is used, line breaks will be automatically converted to paragraphs.', 'custom-api-emails' ); ?>
        </p>
    </div>
</div>

<style>
    .email-template-variables {
        margin-top: 30px;
        padding: 20px;
        background: #fff;
        border: 1px solid #ccd0d4;
        box-shadow: 0 1px 1px rgba(0,0,0,.04);
    }
    .email-template-variables h3 {
        margin-top: 0;
    }
    .email-template-variables code {
        background: #f0f0f1;
        padding: 2px 6px;
    }
</style> 