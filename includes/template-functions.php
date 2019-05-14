<?php
/**
 * Template Functions
 *
 * @package Email_Encoder_Bundle
 * @category WordPress Plugins
 */

// this is an include only WP file
if (!defined('ABSPATH')) {
  die;
}

if (!is_admin()):

    /**
     * Template function for encoding email
     * @global Eeb_Site $Eeb_Site
     * @param string $email
     * @param string $display  if non given will be same as email
     * @param string $extra_attrs  Optional
     * @param string $method Optional, else the default setted method will; be used
     * @return string
     */
    if (!function_exists('eeb_email')):
        function eeb_email($email, $display = null, $extra_attrs = '', $method = null) {
            global $Eeb_Site;
            return $Eeb_Site->encode_email($email, $display, $extra_attrs, $method);
        }
    endif;


    /**
     * Template function for encoding content
     * @global Eeb_Site $Eeb_Site
     * @param string $content
     * @param string $method Optional, default null
     * @return string
     */
    if (!function_exists('eeb_content')):
        function eeb_content($content, $method = null) {
            global $Eeb_Site;
            return $Eeb_Site->encode_content($content, $method);
        }
    endif;

    /**
     * Template function for encoding emails in the given content
     * @global Eeb_Site $Eeb_Site
     * @param string $content
     * @param boolean $enc_tags Optional, default true
     * @param boolean $enc_mailtos  Optional, default true
     * @param boolean $enc_plain_emails Optional, default true
     * @param boolean $enc_input_fields Optional, default true
     * @return string
     */
    if (!function_exists('eeb_email_filter')):
        function eeb_email_filter($content, $enc_tags = true, $enc_mailtos = true, $enc_plain_emails = true, $enc_input_fields = true) {
            global $Eeb_Site;
            return $Eeb_Site->encode_email_filter($content, $enc_tags, $enc_mailtos, $enc_plain_emails, $enc_input_fields);
        }
    endif;

    /**
     * Template function for getting HTML of the encoder form (to put it on the site)
     * @global Eeb_Site $Eeb_Site
     * @return string
     */
    if (!function_exists('eeb_form')):
        function eeb_form() {
            global $Eeb_Site;
            return $Eeb_Site->get_encoder_form();
        }
    endif;

endif;
