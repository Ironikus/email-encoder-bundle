<?php
/**
 * Class Eeb_Admin
 *
 * Contains all code necessary for the Admin part
 *
 * @abstract
 *
 * @package Email_Encoder_Bundle
 * @category WordPress Plugins
 */
 
// this is an include only WP file
if (!defined('ABSPATH')) {
  die;
}

if (!class_exists('Eeb_Admin')):

abstract class Eeb_Admin {

    /**
     * @var array
     */
    private $default_options = array(
        'method' => 'enc_ascii',
        'encode_mailtos' => 1,
        'encode_emails' => 0,
        'encode_fields' => 1,
        'filter_posts' => 1,
        'filter_widgets' => 1,
        'filter_comments' => 1,
        'skip_posts' => '',
        'protection_text' => '*protected email*',
        'protection_text_content' => '*protected content*',
        'class_name' => 'mailto-link',
        'filter_rss' => 1,
        'remove_shortcodes_rss' => 1,
        'protection_text_rss' => '*protected email*',
        'widget_logic_filter' => 0,
        'show_encoded_check' => 0,
        'shortcodes_in_widgets' => 0,
        'support_deprecated_names' => 0,
        'own_admin_menu' => 1,
        'powered_by' => 1,
    );

    /**
     * @var array
     */
    protected $options = array();

    /**
     * @var array
     */
    protected $skip_posts = array();

    /**
     * @var string
     */
    protected $method = 'enc_ascii';

    /**
     * @var array
     */
    private $methods = array();

    /**
     * @var boolean
     */
    private $initial_metabox_settings = false;

    /**
     * Constructor
     */
    protected function __construct() {
        // load text domain for translations
        load_plugin_textdomain('email-encoder-bundle', false, dirname(plugin_basename(EMAIL_ENCODER_BUNDLE_FILE)) . '/languages/');

        // set methods
        $this->methods = array(
            'enc_ascii' => array(
                'name' => __('JS Rot13', 'email-encoder-bundle'),
                'description' => __('Recommended, the safest method using a rot13 method in JavaScript', 'email-encoder-bundle'),
            ),
            'enc_escape' => array(
                'name' => __('JS Escape', 'email-encoder-bundle'),
                'description' => __('Pretty safe method using JavaScipt\'s escape function', 'email-encoder-bundle'),
            ),
            'enc_html' => array(
                'name' => __('Html Encode', 'email-encoder-bundle'),
                'description' => __('Not recommended, equal to <a href="http://codex.wordpress.org/Function_Reference/antispambot" target="_blank"><code>antispambot()</code></a> function of WordPress', 'email-encoder-bundle'),
            ),
        );

        // set option values
        $this->set_options();

        // prepare vars
        $skip_posts = $this->options['skip_posts'];
        $skip_posts = str_replace(' ', '', $skip_posts);
        $skip_posts = explode(',', $skip_posts);
        $this->skip_posts = $skip_posts;

        // set uninstall hook
        register_uninstall_hook(EMAIL_ENCODER_BUNDLE_FILE, array('Eeb_Admin', 'uninstall'));

        // add actions
        add_action('wp', array($this, 'wp'));
        add_action('admin_init', array($this, 'admin_init'));
        add_action('admin_menu', array($this, 'admin_menu'));
        add_action('admin_footer_text', array($this, 'admin_footer_text'));
        // todo
   }

    /**
     * Set options from save values or defaults
     */
    private function set_options() {
        // first set defaults
        $this->options = $this->default_options;

        // get saved options
        $saved_options = get_option(EMAIL_ENCODER_BUNDLE_OPTIONS_NAME);

        // backwards compatible (old values)
        if (empty($saved_options)) {
            // check old values
            $saved_options = get_option(EMAIL_ENCODER_BUNDLE_KEY . 'options');

            // cleanup old values
            delete_option(EMAIL_ENCODER_BUNDLE_KEY . 'options');
        } else {
            foreach ($saved_options AS $key => $value) {
                $this->options[$key] = $value;
            }
        }

        // @todo Update current version value
//        $version = get_option('eeb_version');
//        if ($version !== EMAIL_ENCODER_BUNDLE_VERSION) {
//            update_option('eeb_version', $version);
//            delete_option('eeb_version');
//
//            // on first time loading
//            $this->initial_metabox_settings = true;
//        }

        // set encode method
        $this->method = $this->get_method($this->options['method'], 'enc_ascii');

        // set widget_content filter of Widget Logic plugin
        $widget_logic_opts = get_option('widget_logic');
        if (is_array($widget_logic_opts) && key_exists('widget_logic-options-filter', $widget_logic_opts)) {
            $this->options['widget_logic_filter'] = ($widget_logic_opts['widget_logic-options-filter'] == 'checked') ? 1 : 0;
        }
    }

    /**
     * Get method name
     * @param string $method
     * @param string $defaultMethod Optional, default 'enc_html'
     * @return string
     */
    protected function get_method($method, $defaultMethod = 'enc_html') {
        $method = strtolower($method);

        if (!method_exists($this, $method)) {
            $method = $defaultMethod; // set default method
        }

        return $method;
    }

    /**
     * Callback Uninstall
     */
    static public function uninstall() {
        delete_option(EMAIL_ENCODER_BUNDLE_OPTIONS_NAME);
        unregister_setting(EMAIL_ENCODER_BUNDLE_KEY, EMAIL_ENCODER_BUNDLE_OPTIONS_NAME);
    }

    /**
     * Callbacka admin_init
     */
    public function admin_init() {
        // register settings
        register_setting(EMAIL_ENCODER_BUNDLE_KEY, EMAIL_ENCODER_BUNDLE_OPTIONS_NAME);

        // actions and filters
        add_filter('plugin_action_links', array($this, 'plugin_action_links'), 10, 2);
    }

    /**
     * Callback add links on plugin page
     * @param array $links
     * @param string $file
     * @return array
     */
    public function plugin_action_links($links, $file) {
        if ($file == plugin_basename(EMAIL_ENCODER_BUNDLE_FILE)) {
            $page = ($this->options['own_admin_menu']) ? 'admin.php' : 'options-general.php';
            $settings_link = '<a href="' . get_bloginfo('wpurl') . '/wp-admin/' . $page . '?page=' . EMAIL_ENCODER_BUNDLE_ADMIN_PAGE . '">' . __('Settings', 'email-encoder-bundle') . '</a>';
            array_unshift($links, $settings_link);
        }

        return $links;
    }

    /**
     * Callback admin_menu
     */
    public function admin_menu() {
        // add page and menu item
        if ($this->options['own_admin_menu']) {
            // create main menu item
            $page_hook = add_menu_page(__('Email Encoder', 'email-encoder-bundle'), __('Email Encoder', 'email-encoder-bundle'),
                                'manage_options', EMAIL_ENCODER_BUNDLE_ADMIN_PAGE, array($this, 'show_options_page'),
                                plugins_url('images/icon-email-encoder-16.png', EMAIL_ENCODER_BUNDLE_FILE));
        } else {
            // create submenu item under "Settings"
            $page_hook = add_submenu_page('options-general.php', __('Email Encoder', 'email-encoder-bundle'), __('Email Encoder', 'email-encoder-bundle'),
                                'manage_options', EMAIL_ENCODER_BUNDLE_ADMIN_PAGE, array($this, 'show_options_page'));
        }

        // load plugin page
        add_action('load-' . $page_hook, array($this, 'load_options_page'));
    }

    /* -------------------------------------------------------------------------
     *  Admin Options Page
     * ------------------------------------------------------------------------*/

    /**
     * Add text to footer
     */
    function admin_footer_text($text_org) {
      if (@$_GET['page'] != 'email-encoder-bundle-settings') {
        return $text_org;
      }

      $text = '<i><a target="_blank" href="https://wordpress.org/plugins/email-encoder-bundle/">Email Encoder</a> v' . EMAIL_ENCODER_BUNDLE_VERSION . ' by <a href="https://ironikus.com/" title="Visit our site to get more great plugins" target="_blank">Ironikus</a>.';
      $text .= ' Please <a target="_blank" href="https://wordpress.org/support/plugin/email-encoder-bundle/reviews/#new-post" title="Rate the plugin">Rate the plugin ★★★★★</a>.</i> ';

      return $text;
    } // admin_footer_text
  
  
    /**
     * Load admin options page
     */
    public function load_options_page() {
        // set dashboard postbox
        wp_enqueue_script('dashboard');

        // add script for ajax encoder
        wp_enqueue_script('email_encoder', plugins_url('js/email-encoder-bundle.js', EMAIL_ENCODER_BUNDLE_FILE), array('jquery'), EMAIL_ENCODER_BUNDLE_VERSION);
        wp_enqueue_script('email_encoder_admin', plugins_url('js/email-encoder-bundle-admin.js', EMAIL_ENCODER_BUNDLE_FILE), array('jquery'), EMAIL_ENCODER_BUNDLE_VERSION);

        // add help tabs
        $this->add_help_tabs();

        // screen settings
        if (function_exists('add_screen_option')) {
            add_screen_option('layout_columns', array(
                'max' => 2,
                'default' => 2
            ));
        }

        // add meta boxes
        add_meta_box('main_settings', __('Main Settings', 'email-encoder-bundle'), array($this, 'show_meta_box_content'), null, 'normal', 'core', array('main_settings'));
        add_meta_box('additional_settings', __('Additional Settings', 'email-encoder-bundle'), array($this, 'show_meta_box_content'), null, 'normal', 'core', array('additional_settings'));
        add_meta_box('rss_settings', __('RSS Settings', 'email-encoder-bundle'), array($this, 'show_meta_box_content'), null, 'normal', 'core', array('rss_settings'));
        add_meta_box('admin_settings', __('Admin Settings', 'email-encoder-bundle'), array($this, 'show_meta_box_content'), null, 'normal', 'core', array('admin_settings'));
        add_meta_box('encode_form', __('Email Encoder Form', 'email-encoder-bundle'), array($this, 'show_meta_box_content'), null, 'normal', 'core', array('encode_form'));
        add_meta_box('this_plugin', __('Support', 'email-encoder-bundle'), array($this, 'show_meta_box_content'), null, 'side', 'core', array('this_plugin'));
        add_meta_box('other_plugins', __('You Might Need These Plugins', 'email-encoder-bundle'), array($this, 'show_meta_box_content'), null, 'side', 'core', array('other_plugins'));
    }

    /**
     * Show admin options page
     */
    public function show_options_page() {
        $this->set_options();
?>
<style>
#other_plugins img {
  vertical-align: middle;
  height: 24px;
  width: auto;
  padding: 0 5px 0 0;
}

#other_plugins h4 {
  margin: 0;
  padding: 0;
}

#other_plugins p {
  margin: 5px 0 25px 0;
  padding: 0;
}

</style>
        <div class="wrap">
            <h2><?php echo get_admin_page_title() ?> - <em><small><?php _e('Protect Email Addresses From Bots &amp; Scrapers', 'email-encoder-bundle') ?></small></em></h2>

            <?php if (isset($_GET['settings-updated']) && $_GET['settings-updated'] == 'true' && $this->options['own_admin_menu']): ?>
            <div class="updated settings-error" id="setting-error-settings_updated">
                <p><strong><?php _e('Settings saved.' ) ?></strong></p>
            </div>
            <?php endif; ?>

            <?php if ($this->initial_metabox_settings): ?>
                <script type="text/javascript">jQuery(function($){ $('#additional_settings, #rss_settings, #admin_settings, #encode_form').addClass('closed'); });</script>
            <?php endif; ?>

            <form method="post" action="options.php">
                <?php settings_fields(EMAIL_ENCODER_BUNDLE_KEY); ?>

                <input type="hidden" name="<?php echo EMAIL_ENCODER_BUNDLE_KEY ?>_nonce" value="<?php echo wp_create_nonce(EMAIL_ENCODER_BUNDLE_KEY) ?>" />
                <?php wp_nonce_field('closedpostboxes', 'closedpostboxesnonce', false); ?>
                <?php wp_nonce_field('meta-box-order', 'meta-box-order-nonce', false); ?>

                <div id="poststuff">
                    <div id="post-body" class="metabox-holder columns-<?php echo 1 == get_current_screen()->get_columns() ? '1' : '2'; ?>">
                        <!--<div id="post-body-content"></div>-->

                        <div id="postbox-container-1" class="postbox-container">
                            <?php do_meta_boxes('', 'side', ''); ?>
                        </div>

                        <div id="postbox-container-2" class="postbox-container">
                            <?php do_meta_boxes('', 'normal', ''); ?>
                            <?php do_meta_boxes('', 'advanced', ''); ?>
                        </div>
                    </div> <!-- #post-body -->
                </div> <!-- #poststuff -->
            </form>
        </div>
<?php
    }

    /**
     * Show content of metabox (callback)
     * @param array $post
     * @param array $meta_box
     */
    public function show_meta_box_content($post, $meta_box) {
        $key = $meta_box['args'][0];
        $options = $this->options;

        if ($key === 'main_settings') {
?>
            <?php if (is_plugin_active('wp-mailto-links/wp-mailto-links.php')): ?>
                <p class="description"><?php _e('Warning: "WP Mailto Links"-plugin is also activated, which could cause conflicts.', 'email-encoder-bundle') ?></p>
            <?php endif; ?>
            <fieldset class="options">
                <table class="form-table">
                <tr>
                    <th><?php _e('Choose what to protect', 'email-encoder-bundle') ?></th>
                    <td>
                        <label><input type="checkbox" id="encode_mailtos" name="<?php echo EMAIL_ENCODER_BUNDLE_OPTIONS_NAME ?>[encode_mailtos]" value="1" <?php checked('1', (int) $options['encode_mailtos']); ?> />
                            <span><?php _e('Protect mailto links, like f.e. <code>&lt;a href="info@myemail.com"&gt;My Email&lt;/a&gt;</code>', 'email-encoder-bundle') ?></span>
                        <br/><label><input type="checkbox" id="encode_emails" name="<?php echo EMAIL_ENCODER_BUNDLE_OPTIONS_NAME ?>[encode_emails]" value="1" <?php checked('1', (int) $options['encode_emails']); ?> disabled="disabled" />
                            <span><?php _e('Replace plain email addresses to protected mailto links', 'email-encoder-bundle') ?></span>
                            <!--<span class="description notice-form-field-bug"><br/><?php _e('Notice: be careful with this option when using email addresses on form fields, please <a href="http://wordpress.org/extend/plugins/email-encoder-bundle/faq/" target="_blank">check the FAQ</a> for more info.', 'email-encoder-bundle') ?></span>-->
                        </label>
                        <br/><label><input type="checkbox" id="encode_fields" name="<?php echo EMAIL_ENCODER_BUNDLE_OPTIONS_NAME ?>[encode_fields]" value="1" <?php checked('1', (int) $options['encode_fields']); ?> />
                            <span><?php _e('Replace pre-filled email addresses in input fields', 'email-encoder-bundle') ?></span>
                            <span class="description"><?php _e(' - recommended!', 'email-encoder-bundle') ?></span>
                        </label>
                    <br/>
                    </td>
                </tr>
                <tr>
                    <th><?php _e('Apply on', 'email-encoder-bundle') ?></th>
                    <td>
                        <label><input type="checkbox" name="<?php echo EMAIL_ENCODER_BUNDLE_OPTIONS_NAME ?>[filter_posts]" value="1" <?php checked('1', (int) $options['filter_posts']); ?> />
                                <span><?php _e('All posts and pages', 'email-encoder-bundle') ?></span>
                            </label>
                        <br/><label><input type="checkbox" id="<?php echo EMAIL_ENCODER_BUNDLE_OPTIONS_NAME ?>[filter_comments]" name="<?php echo EMAIL_ENCODER_BUNDLE_OPTIONS_NAME ?>[filter_comments]" value="1" <?php checked('1', (int) $options['filter_comments']); ?> />
                            <span><?php _e('All comments', 'email-encoder-bundle') ?></span></label>
                        <br/><label><input type="checkbox" id="<?php echo EMAIL_ENCODER_BUNDLE_OPTIONS_NAME ?>[filter_widgets]" name="<?php echo EMAIL_ENCODER_BUNDLE_OPTIONS_NAME ?>[filter_widgets]" value="1" <?php checked('1', (int) $options['filter_widgets']); ?> />
                            <span><?php if ($this->options['widget_logic_filter']) { _e('All widgets (uses the <code>widget_content</code> filter of the Widget Logic plugin)', 'email-encoder-bundle'); } else { _e('All text widgets', 'email-encoder-bundle'); } ?></span></label>
                    </td>
                </tr>
                <tr>
                    <th><?php _e('Add class to protected mailto links', 'email-encoder-bundle') ?></th>
                    <td><label><input type="text" id="<?php echo EMAIL_ENCODER_BUNDLE_OPTIONS_NAME ?>[class_name]" class="regular-text" name="<?php echo EMAIL_ENCODER_BUNDLE_OPTIONS_NAME ?>[class_name]" value="<?php echo esc_attr($options['class_name']); ?>" />
                        <br/><span class="description"><?php _e('All protected mailto links will get these class(es). Optional, else keep blank.', 'email-encoder-bundle') ?></span></label></td>
                </tr>
                </table>
           </fieldset>

            <p class="submit">
                <input class="button-primary" type="submit" disabled="disabled" value="<?php _e('Save Changes') ?>" />
            </p>
            <br class="clear" />

<?php
        } else if ($key === 'rss_settings') {
?>
            <fieldset class="options">
                <table class="form-table">
                <tr>
                    <th><?php _e('Protect emails in RSS feeds', 'email-encoder-bundle') ?></th>
                    <td><label><input type="checkbox" id="filter_rss" name="<?php echo EMAIL_ENCODER_BUNDLE_OPTIONS_NAME ?>[filter_rss]" value="1" <?php checked('1', (int) $options['filter_rss']); ?> />
                            <span><?php _e('Replace emails in RSS feeds', 'email-encoder-bundle') ?></span></label>
                    </td>
                </tr>
                <tr>
                    <th><?php _e('Remove shortcodes from RSS feeds', 'email-encoder-bundle') ?></th>
                    <td><label><input type="checkbox" id="remove_shortcodes_rss" name="<?php echo EMAIL_ENCODER_BUNDLE_OPTIONS_NAME ?>[remove_shortcodes_rss]" value="1" <?php checked('1', (int) $options['remove_shortcodes_rss']); ?> />
                            <span><?php _e('Remove all shortcodes from the RSS feeds', 'email-encoder-bundle') ?></span></label>
                    </td>
                </tr>
                <tr>
                    <th><?php _e('Set protection text in RSS feeds', 'email-encoder-bundle') ?></th>
                    <td><label><input type="text" id="protection_text" class="regular-text" name="<?php echo EMAIL_ENCODER_BUNDLE_OPTIONS_NAME ?>[protection_text_rss]" value="<?php echo esc_attr($options['protection_text_rss']); ?>" />
                            <br/><span class="description"><?php _e('Used as replacement for email addresses in RSS feeds.', 'email-encoder-bundle') ?></span>
                        </label>
                    </td>
                </tr>
                </table>
            </fieldset>

            <p class="submit">
                <input class="button-primary" type="submit" disabled="disabled" value="<?php _e('Save Changes') ?>" />
            </p>
            <br class="clear" />
<?php
        } else if ($key === 'additional_settings') {
?>
            <fieldset class="options">
                <table class="form-table">
                <tr>
                    <th><?php _e('Choose protection method', 'email-encoder-bundle') ?></th>
                    <td>
                        <?php foreach ($this->methods AS $method => $info): ?>
                            <label>
                                <input type="radio" name="<?php echo EMAIL_ENCODER_BUNDLE_OPTIONS_NAME ?>[method]" class="protection-method" value="<?php echo $method ?>" <?php if ($this->method == $method) echo 'checked="checked"' ?> />
                                <span><?php echo $info['name'] ?></span>
                                - <span class="description"><?php echo $info['description'] ?></span>
                            </label>
                            <br/>
                        <?php endforeach; ?>
                    </td>
                </tr>
                <tr>
                    <th><?php _e('Set <code>&lt;noscript&gt;</code> text', 'email-encoder-bundle') ?></th>
                    <td><label>
                            <span><?php _e('For encoded emails:', 'email-encoder-bundle') ?></span>
                            <br/><input type="text" id="protection_text" class="regular-text" name="<?php echo EMAIL_ENCODER_BUNDLE_OPTIONS_NAME ?>[protection_text]" value="<?php echo esc_attr($options['protection_text']); ?>" />
                        </label>
                        <br/>
                        <br/>
                        <label>
                            <span><?php _e('For other encoded content:', 'email-encoder-bundle') ?></span>
                            <br/><input type="text" id="protection_text_content" class="regular-text" name="<?php echo EMAIL_ENCODER_BUNDLE_OPTIONS_NAME ?>[protection_text_content]" value="<?php echo esc_attr($options['protection_text_content']); ?>" />
                        </label>
                        <br/>
                        <br/><span class="description"><?php _e('Used as <code>&lt;noscript&gt;</code> fallback for JavaScrip methods.', 'email-encoder-bundle') ?></span>
                    </td>
                </tr>
                <tr>
                    <th><?php _e('Exclude posts', 'email-encoder-bundle') ?></th>
                    <td>
                        <label>
                            <span><?php _e('Do <strong>not</strong> apply protection on posts or pages with the folllowing ID:', 'email-encoder-bundle') ?></span>
                            <br/><input type="text" id="<?php echo EMAIL_ENCODER_BUNDLE_OPTIONS_NAME ?>[skip_posts]" class="regular-text" name="<?php echo EMAIL_ENCODER_BUNDLE_OPTIONS_NAME ?>[skip_posts]" value="<?php echo esc_attr($options['skip_posts']); ?>" />
                            <br/><span class="description"><?php _e('Seperate Id\'s by comma, f.e.: 2, 7, 13, 32.', 'email-encoder-bundle') ?></span>
                            <br/><span class="description"><?php _e('Notice: shortcodes still work on these posts.', 'email-encoder-bundle') ?></span>
                        </label>
                    </td>
                </tr>
                <tr>
                    <th><?php _e('Use shortcodes in widgets', 'email-encoder-bundle') ?></th>
                    <td>
                        <label><input type="checkbox" name="<?php echo EMAIL_ENCODER_BUNDLE_OPTIONS_NAME ?>[shortcodes_in_widgets]" value="1" <?php checked('1', (int) $options['shortcodes_in_widgets']); ?> />
                                <span><?php _e('Also use shortcodes in widgets', 'email-encoder-bundle') ?></span>
                                <br/><span class="description"><?php if (!$this->options['widget_logic_filter']) { _e('Notice: only works for text widgets!', 'email-encoder-bundle'); } else { _e('All text widgets', 'email-encoder-bundle'); } ?></span></label>
                        </label>
                    </td>
                </tr>
                <tr>
                    <th><?php _e('Use deprecated names', 'email-encoder-bundle') ?></th>
                    <td><label><input type="checkbox" id="<?php echo EMAIL_ENCODER_BUNDLE_OPTIONS_NAME ?>[support_deprecated_names]" name="<?php echo EMAIL_ENCODER_BUNDLE_OPTIONS_NAME ?>[support_deprecated_names]" value="1" <?php checked('1', (int) $options['support_deprecated_names']); ?> />
                            <span><?php _e('Keep supporting the old names for action, shortcodes and template functions', 'email-encoder-bundle') ?></span>
                            <br /><span class="description">These deprecated will still be available: <code>init_email_encoder_bundle</code>, <code>[encode_email]</code>, <code>[encode_content]</code>, <code>[email_encoder_form]</code>, <code>encode_email()</code>, <code>encode_content()</code>, <code>encode_email_filter()</code></span></label></td>
                </tr>
                </table>
            </fieldset>

            <p class="submit">
                <input class="button-primary" type="submit" disabled="disabled" value="<?php _e('Save Changes') ?>" />
            </p>
            <br class="clear" />
<?php
        } else if ($key === 'admin_settings') {
?>
            <fieldset class="options">
                <table class="form-table">
                <tr>
                    <th><?php _e('Check "succesfully encoded"', 'email-encoder-bundle') ?></th>
                    <td><label><input type="checkbox" id="<?php echo EMAIL_ENCODER_BUNDLE_OPTIONS_NAME ?>[show_encoded_check]" name="<?php echo EMAIL_ENCODER_BUNDLE_OPTIONS_NAME ?>[show_encoded_check]" value="1" <?php checked('1', (int) $options['show_encoded_check']); ?> />
                            <span><?php _e('Show "successfully encoded" text for all encoded content, only when logged in as admin user', 'email-encoder-bundle') ?></span>
                            <br/><span class="description"><?php _e('This way you can check if emails are really encoded on your site.', 'email-encoder-bundle') ?></span>
                        </label>
                    </td>
                </tr>
                <tr>
                    <th><?php _e('Choose admin menu position', 'email-encoder-bundle') ?></th>
                    <td><label><input type="checkbox" id="<?php echo EMAIL_ENCODER_BUNDLE_OPTIONS_NAME ?>[own_admin_menu]" name="<?php echo EMAIL_ENCODER_BUNDLE_OPTIONS_NAME ?>[own_admin_menu]" value="1" <?php checked('1', (int) $options['own_admin_menu']); ?> />
                            <span><?php _e('Show as main menu item', 'email-encoder-bundle') ?></span>
                             <br /><span class="description">When disabled this page will be available under "<?php _e('Settings') ?>".</span>
                        </label>
                    </td>
                </tr>
                </table>
            </fieldset>

            <p class="submit">
                <input class="button-primary" type="submit" disabled="disabled" value="<?php _e('Save Changes') ?>" />
            </p>

            <br class="clear" />
<?php
        } else if ($key === 'encode_form') {
?>
            <p><?php _e('If you like you can also create you own secure mailto links manually with this form. Just copy/paste the generated code and put it in your post, page or template.', 'email-encoder-bundle') ?></p>

            <hr style="border:1px solid #FFF; border-top:1px solid #EEE;" />

            <?php echo $this->get_encoder_form(); ?>

            <hr style="border:1px solid #FFF; border-top:1px solid #EEE;"/>

            <p class="description"><?php _e('You can also put the encoder form on your site by using the shortcode <code>[eeb_form]</code> or the template function <code>eeb_form()</code>.', 'email-encoder-bundle') ?></p>

            <fieldset class="options">
                <table class="form-table">
                <tr>
                    <th><?php _e('Show "powered by"', 'email-encoder-bundle') ?></th>
                    <td><label><input type="checkbox" id="<?php echo EMAIL_ENCODER_BUNDLE_OPTIONS_NAME ?>[powered_by]" name="<?php echo EMAIL_ENCODER_BUNDLE_OPTIONS_NAME ?>[powered_by]" value="1" <?php checked('1', (int) $options['powered_by']); ?> />
                            <span><?php _e('Show a "powered by" link on bottom of the encoder form', 'email-encoder-bundle') ?></span>
                        </label>
                    </td>
                </tr>
                </table>
            </fieldset>

            <p class="submit">
                <input class="button-primary" type="submit" disabled="disabled" value="<?php _e('Save Changes') ?>" />
            </p>
            <br class="clear" />

<?php
        } else if ($key === 'this_plugin') {
?>
            <ul>
                <li><a href="#" class="eeb-help-link"><?php _e('Documentation', 'email-encoder-bundle') ?></a></li>
                <li><a href="http://wordpress.org/support/plugin/email-encoder-bundle#postform" target="_blank"><?php _e('Report a problem', 'email-encoder-bundle') ?></a></li>
                <li><a href="http://wordpress.org/support/plugin/email-encoder-bundle#postform" target="_blank"><?php _e('Get support', 'email-encoder-bundle') ?></a></li>
                <li><strong><a href="http://wordpress.org/support/view/plugin-reviews/email-encoder-bundle" target="_blank"><?php _e('Rate the plugin ★★★★★', 'email-encoder-bundle') ?></a></strong></li>
            </ul>

<?php
        } else if ($key === 'other_plugins') {

            $mulsitie_slug = ( is_multisite() ) ? 'network/' : '';
?>
            <h4><img src="<?php echo plugins_url('images/icon-wp-webhooks.png', EMAIL_ENCODER_BUNDLE_FILE) ?>"> WP Webhooks</h4>
            <p>If you love to automate things, this is for you! WP Webhooks allows you to recieve and send data on your WordPress website from any third party service out there.
                <br /><a href="<?php echo admin_url( $mulsitie_slug . 'plugin-install.php?s=WP+Webhooks&tab=search&type=term'); ?>" target="_blank">Install now for free</a> | <a href="https://wordpress.org/plugins/wp-webhooks/" title="Go to WordPress.org" target="_blank">More info</a>
            </p>

            <h4><img src="<?php echo plugins_url('images/icon-wp-snow.png', EMAIL_ENCODER_BUNDLE_FILE) ?>"> WP Snow</h4>
            <p>Make your website something special with this awesome flakes animation plugin. Make snowflakes, falling snowmans, custom text or any other of the 1300+ icons glide over your website.
                <br /><a href="<?php echo admin_url( $mulsitie_slug . 'plugin-install.php?s=WP+Snow+–+Best+Snow+Effect+Plugin+&tab=search&type=term'); ?>" target="_blank">Install now for free</a> | <a href="https://wordpress.org/plugins/wp-snow/" title="Go to WordPress.org" target="_blank">More info</a>
            </p>
<?php
        }
    }

    /* -------------------------------------------------------------------------
     *  Help Tabs
     * ------------------------------------------------------------------------*/

    /**
     * Add help tabs
     */
    public function add_help_tabs() {
        if (!function_exists('get_current_screen')) {
            return;
        }

        $screen = get_current_screen();

        $screen->set_help_sidebar($this->get_help_text('sidebar'));

        $screen->add_help_tab(array(
            'id' => 'quickstart',
            'title'    => __('Quick Start', 'email-encoder-bundle'),
            'content' => $this->get_help_text('quickstart'),
        ));
        $screen->add_help_tab(array(
            'id' => 'shortcodes',
            'title'    => __('Shortcodes', 'email-encoder-bundle'),
            'content' => $this->get_help_text('shortcodes'),
        ));
        $screen->add_help_tab(array(
            'id' => 'templatefunctions',
            'title'    => __('Template Functions', 'email-encoder-bundle'),
            'content' => $this->get_help_text('templatefunctions'),
        ));
        $screen->add_help_tab(array(
            'id' => 'actions',
            'title'    => __('Action Hook', 'email-encoder-bundle'),
            'content' => $this->get_help_text('actions'),
        ));
        $screen->add_help_tab(array(
            'id' => 'filters',
            'title'    => __('Filter Hooks', 'email-encoder-bundle'),
            'content' => $this->get_help_text('filters'),
        ));
        $screen->add_help_tab(array(
            'id' => 'faq',
            'title'    => __('FAQ', 'email-encoder-bundle'),
            'content' => $this->get_help_text('faq'),
        ));
    }

    /**
     * Get text for given help tab
     * @param string $key
     * @return string
     */
    private function get_help_text($key) {
        if ($key === 'quickstart') {
            $plugin_title = get_admin_page_title();
            $icon_url = plugins_url('images/icon-email-encoder.png', EMAIL_ENCODER_BUNDLE_FILE);
            $quick_start_url = plugins_url('images/quick-start.png', EMAIL_ENCODER_BUNDLE_FILE);
            $version = EMAIL_ENCODER_BUNDLE_VERSION;

            $content = sprintf(__('<h3><img src="%s" width="16" height="16" /> %s - version %s</h3>'
                     . '<p>By default, out-of-the-box all mailto links in your posts, pages, comments and (text) widgets will be encoded and protected. <br/>If you also want to encode plain email address, you have to enable that option.</p>'
                     . '<img src="%s" width="600" height="273" />'
                     , 'email-encoder-bundle'), $icon_url, $plugin_title, $version, $quick_start_url);
        } else if ($key === 'shortcodes') {
            $content = __('<h3>Shortcodes</h3>'
                     . '<p>You can use these shortcodes within your posts or pages.</p>'
                     . '<h4>eeb_email</h4>'
                     . '<p>Create an encoded mailto link:</p>'
                     . '<p><code>[eeb_email email="..." display="..."]</code></p>'
                     . '<ul>'
                     . '<li>"display" is optional or the email wil be shown as display (also protected)</li>'
                     . '<li>"extra_attrs" is optional, example: <code>extra_attrs="target=\'_blank\'"</code></li>'
                     . '<li>"method" is optional, else the method option will be used.</li>'
                     . '</ul>'
                     . '<h4>eeb_content</h4>'
                     . '<p>Encode some text:</p>'
                     . '<p><code>[eeb_content method="..."]...[/eeb_content]</code></p>'
                     . '<ul>'
                     . '<li>"method" is optional, else the method option will be used.</li>'
                     . '</ul>'
                     . '<h4>eeb_form</h4>'
                     . '<p>Create an encoder form:</p>'
                     . '<p><code>[eeb_form]</code></p>'
                     , 'email-encoder-bundle');
        } else if ($key === 'templatefunctions') {
            $content = __('<h3>Template Functions</h3>'
                     . '<h4>eeb_email()</h4>'
                     . '<p>Create an encoded mailto link:</p>'
                     . '<pre><code><&#63;php' . "\n"
                     . 'if (function_exists(\'eeb_email\')) {' . "\n"
                     . '    echo eeb_email(\'info@somedomain.com\');' . "\n"
                     . '}' . "\n"
                     . '&#63;></code></pre>'
                     . '<p>You can pass a few extra optional params (in this order): <code>display</code>, <code>extra_attrs</code>, <code>method</code></p>'
                     . '<h4>eeb_content()</h4>'
                     . '<p>Encode some text:</p>'
                     . '<pre><code><&#63;php' . "\n"
                     . 'if (function_exists(\'eeb_content\')) {' . "\n"
                     . '    echo eeb_content(\'Encode this text\');' . "\n"
                     . '}' . "\n"
                     . '&#63;></code></pre>'
                     . '<p>You can pas an extra optional param: <code>method</code></p>'
                     . '<h4>eeb_email_filter()</h4>'
                     . '<p>Filter given content and encode all email addresses or mailto links:</p>'
                     . '<pre><code><&#63;php' . "\n"
                     . 'if (function_exists(\'eeb_email_filter\')) {' . "\n"
                     . '    echo eeb_email_filter(\'Some content with email like info@somedomein.com or a mailto link\');' . "\n"
                     . '}' . "\n"
                     . '&#63;></code></pre>'
                     . '<p>You can pass a few extra optional params (in this order): <code>enc_tags</code>, <code>enc_mailtos</code>, <code>enc_plain_emails</code>, <code>enc_input_fields</code></p>'
                     . '<h4>eeb_form()</h4>'
                     . '<p>Create an encoder form:</p>'
                     . '<pre><code><&#63;php' . "\n"
                     . 'if (function_exists(\'eeb_form\')) {' . "\n"
                     . '    echo eeb_form();' . "\n"
                     . '}' . "\n"
                     . '&#63;></code></pre>'
                     , 'email-encoder-bundle');
        } else if ($key === 'actions') {
            $content = __('<h3>Action Hooks</h3>'
                     . '<h4>eeb_ready</h4>'
                     . '<p>Add extra code on initializing this plugin, like extra filters for encoding.</p>'
                     . '<pre><code><&#63;php' . "\n"
                     . 'add_action(\'eeb_ready\', \'extra_encode_filters\');' . "\n\n"
                     . 'function extra_encode_filters($eeb_object) {' . "\n"
                     . '    add_filter(\'some_filter\', array($eeb_object, \'callback_filter\'));' . "\n"
                     . '}' . "\n"
                     . '&#63;></code></pre>'
                     , 'email-encoder-bundle');
        } else if ($key === 'filters') {
            $content = __('<h3>Filter Hooks</h3>'
                     . '<h4>eeb_mailto_regexp</h4>'
                     . '<p>You can change the regular expression used to search for mailto links.</p>'
                     . '<pre><code><&#63;php' . "\n"
                     . 'add_filter(\'eeb_mailto_regexp\', \'change_mailto_regexp\');' . "\n\n"
                     . 'function change_mailto_regexp($regexp) {' . "\n"
                     . '    return \'-your regular expression-\';' . "\n"
                     . '}' . "\n"
                     . '&#63;></code></pre>'
                     . '<h4>eeb_email_regexp</h4>'
                     . '<p>You can change the regular expression used to search for mailto links.</p>'
                     . '<pre><code><&#63;php' . "\n"
                     . 'add_filter(\'eeb_email_regexp\', \'change_email_regexp\');' . "\n\n"
                     . 'function change_email_regexp($regexp) {' . "\n"
                     . '    return \'-your regular expression-\';' . "\n"
                     . '}' . "\n"
                     . '&#63;></code></pre>'
                     . '<h4>eeb_form_content</h4>'
                     . '<p>Filter for changing the form layout.</p>'
                     . '<pre><code><&#63;php' . "\n"
                     . 'add_filter(\'eeb_form_content\', \'eeb_form_content\', 10, 4);' . "\n\n"
                     . 'function eeb_form_content($content, $labels, $show_powered_by, $methods) {' . "\n"
                     . '    // add a &lt;div&gt;-wrapper' . "\n"
                     . '    return \'&lt;div class="form-wrapper"&gt;\' . $content . \'&lt;/div&gt;\';' . "\n"
                     . '}' . "\n"
                     . '&#63;></code></pre>'
                     , 'email-encoder-bundle');
        } else if ($key === 'faq') {
            $content = __('<h3>FAQ</h3>'
                     . '<p>Please see the <a href="http://wordpress.org/extend/plugins/email-encoder-bundle/faq/" target="_blank">official FAQ</a>.'
                     , 'email-encoder-bundle');
        } else if ($key === 'sidebar') {
            $content = __('<h4>More Info</h4>'
                     . '<ul>'
                     . '<li><a href="https://profiles.wordpress.org/ironikus/#content-plugins" target="_blank">Quality free plugins</a></li>'
                     . '<li><a href="http://wordpress.org/support/plugin/email-encoder-bundle#postform" target="_blank">Get Support</a></li>'
                     . '<li><a href="https://ironikus.com/" target="_blank">Visit Ironikus</a></li>'
                     . '</ul>'
                     , 'email-encoder-bundle');
        } else {
            $content = '';
        }

        return $content;
    }

    /* -------------------------------------------------------------------------
     * Encoder Form
     * -------------------------------------------------------------------------/

    /**
     * Get the encoder form (to use as a demo, like on the options page)
     * @return string
     */
    public function get_encoder_form() {
        $method_options = '';
        foreach ($this->methods as $method_name => $info) {
            $method_options .= '<option value="' . $method_name . '"' . (($this->method == $method_name) ? ' selected="selected"' : '') . '>' . $info['name'] . '</option>';
        }

        $show_powered_by = (bool) $this->options['powered_by'];
        $powered_by = '';
        if ($show_powered_by) {
            $powered_by .= '<p class="powered-by">' . __('Powered by free', 'email-encoder-bundle') . ' <a rel="external" href="https://wordpress.org/plugins/email-encoder-bundle/">Email Encoder</a></p>';
        }

        $labels = array(
            'email' => __('Email Address:', 'email-encoder-bundle'),
            'display' => __('Display Text:', 'email-encoder-bundle'),
            'mailto' => __('Mailto Link:', 'email-encoder-bundle'),
            'method' => __('Encoding Method:', 'email-encoder-bundle'),
            'create_link' => __('Create Protected Mail Link &gt;&gt;', 'email-encoder-bundle'),
            'output' => __('Protected Mail Link (code):', 'email-encoder-bundle'),
            'method_options' => $method_options,
            'powered_by' => $powered_by,
        );

        extract($labels);

        $form = <<<FORM
<div class="eeb-form">
    <form>
        <fieldset>
            <div class="input">
                <table>
                <tbody>
                    <tr>
                        <th><label for="eeb-email">{$email}</label></th>
                        <td><input type="text" class="regular-text" id="eeb-email" name="eeb-email" /></td>
                    </tr>
                    <tr>
                        <th><label for="eeb-display">{$display}</label></th>
                        <td><input type="text" class="regular-text" id="eeb-display" name="eeb-display" /></td>
                    </tr>
                    <tr>
                        <th>{$mailto}</th>
                        <td><span class="eeb-example"></span></td>
                    </tr>
                    <tr>
                        <th><label for="eeb-encode-method">{$method}</label></th>
                        <td><select id="eeb-encode-method" name="eeb-encode-method" class="postform">
                                {$method_options}
                            </select>
                            <input type="button" id="eeb-ajax-encode" name="eeb-ajax-encode" value="{$create_link}" />
                        </td>
                    </tr>
                </tbody>
                </table>
            </div>
            <div class="eeb-output">
                <table>
                <tbody>
                    <tr>
                        <th><label for="eeb-encoded-output">{$output}</label></th>
                        <td><textarea class="large-text node" id="eeb-encoded-output" name="eeb-encoded-output" cols="50" rows="4"></textarea></td>
                    </tr>
                </tbody>
                </table>
            </div>
            {$powered_by}
        </fieldset>
    </form>
</div>
FORM;

         // apply filters
        $form = apply_filters('eeb_form_content', $form, $labels, $show_powered_by, $this->methods);

        return $form;
    }

} // end class Eeb_Admin

endif;
