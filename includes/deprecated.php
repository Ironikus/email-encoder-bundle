<?php
/**
 * Deprecated template Functions
 *
 * @package Email_Encoder_Bundle
 * @category WordPress Plugins
 */

// this is an include only WP file
if (!defined('ABSPATH')) {
  die;
}

/**
 * Template function for encoding email
 *
 * @deprecated
 *
 * @global Eeb_Site $Eeb_Site
 * @param string $email
 * @param string $display  if non given will be same as email
 * @param string $method Optional, else the default setted method will; be used
 * @param string $extra_attrs  Optional
 * @return string
 */
if (!function_exists('encode_email')):
    function encode_email($email, $display = null, $method = null, $extra_attrs = '') {
        return eeb_email($email, $display, $extra_attrs, $method);
    }
endif;

/**
 * Template function for encoding content
 *
 * @deprecated
 *
 * @global Eeb_Site $Eeb_Site
 * @param string $content
 * @param string $method Optional, default null
 * @return string
 */
if (!function_exists('encode_content')):
    function encode_content($content, $method = null) {
        return eeb_content($content, $method);
    }
endif;

/**
 * Template function for encoding emails in the given content
 *
 * @deprecated
 *
 * @global Eeb_Site $Eeb_Site
 * @param string $content
 * @param boolean $enc_tags Optional, default true
 * @param boolean $enc_mailtos  Optional, default true
 * @param boolean $enc_plain_emails Optional, default true
 * @return string
 */
if (!function_exists('encode_email_filter')):
    function encode_email_filter($content, $enc_tags = true, $enc_mailtos = true, $enc_plain_emails = true) {
        return eeb_email_filter($content, $enc_tags, $enc_mailtos, $enc_plain_emails);
    }
endif;
