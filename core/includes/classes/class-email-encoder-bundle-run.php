<?php

/**
 * Class Email_Encoder_Run
 *
 * Thats where we bring the plugin to life
 *
 * @since 2.0.0
 * @package EEB
 * @author Ironikus <info@ironikus.com>
 */

class Email_Encoder_Run{

	/**
	 * The main page name for our admin page
	 *
	 * @var string
	 * @since 2.0.0
	 */
	private $page_name;

	/**
	 * The main page title for our admin page
	 *
	 * @var string
	 * @since 2.0.0
	 */
	private $page_title;

	/**
	 * Our Email_Encoder_Run constructor.
	 */
	function __construct(){
		$this->page_name    			= EEB()->settings->get_page_name();
		$this->page_title   			= EEB()->settings->get_page_title();
		$this->final_outout_buffer_hook = EEB()->settings->get_final_outout_buffer_hook();
		$this->widget_callback_hook 	= EEB()->settings->get_widget_callback_hook();
		$this->add_hooks();
	}

	/**
	 * Define all of our necessary hooks
	 */
	private function add_hooks(){
		$filter_hook = (bool) EEB()->settings->get_setting( 'filter_hook', true, 'filter_body' );
		if( $filter_hook ){
			$hook_name = 'init';
		} else {
			$hook_name = 'wp';
		}
		
		add_action( 'wp', array( $this, 'display_email_image' ), EEB()->settings->get_hook_priorities( 'display_email_image' ) );
		add_action( 'init', array( $this, 'buffer_final_output' ), EEB()->settings->get_hook_priorities( 'buffer_final_output' ) );
		add_action( 'init', array( $this, 'add_custom_template_tags' ), EEB()->settings->get_hook_priorities( 'add_custom_template_tags' ) );
		add_action( $hook_name, array( $this, 'setup_single_filter_hooks' ), EEB()->settings->get_hook_priorities( 'setup_single_filter_hooks' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'load_frontend_header_styling' ), EEB()->settings->get_hook_priorities( 'load_frontend_header_styling' ) );
		add_filter( 'dynamic_sidebar_params', array( $this, 'eeb_dynamic_sidebar_params' ), EEB()->settings->get_hook_priorities( 'eeb_dynamic_sidebar_params' ) );

		//Add shortcodes
		add_shortcode( 'eeb_protect_emails', array( $this, 'protect_content_shortcode' ) );
		add_shortcode( 'eeb_protect_content', array( $this, 'shortcode_eeb_content' ) );
		add_shortcode( 'eeb_mailto', array( $this, 'shortcode_eeb_email' ) );
		add_shortcode( 'eeb_form', array( $this, 'shortcode_email_encoder_form' ) );

		//BAckwards compatibility
		add_shortcode( 'eeb_content', array( $this, 'shortcode_eeb_content' ) );
		add_shortcode( 'eeb_email', array( $this, 'shortcode_eeb_email' ) );

		do_action('eeb_ready', array($this, 'eeb_ready_callback_filter'), $this);

	}

	/**
	 * ######################
	 * ###
	 * #### CALLBACK FILTERS
	 * ###
	 * ######################
	 */

	 /**
     * WP filter callback
     * @param string $content
     * @return string
     */
    public function eeb_ready_callback_filter( $content ) {

		if( EEB()->validate->is_post_excluded() ){
			return $content;
		}

        $protect_using = (string) EEB()->settings->get_setting( 'protect_using', true );
        
        return EEB()->validate->filter_content( $content, $protect_using );
    }

	/**
	 * ######################
	 * ###
	 * #### PAGE BUFFERING & WIDGET FILTER
	 * ###
	 * ######################
	 */

	 /**
	  * Buffer the final output on the init hook
	  *
	  * @return void
	  */
	public function buffer_final_output(){
        if ( ! defined( 'WP_CLI' ) ) {
			ob_start( array( $this, 'apply_content_filter' ) );
		}
    }

	 /**
     * Apply the callabla function for ob_start()
	 * 
     * @param string $content
     * @return string - the filtered content
     */
    public function apply_content_filter( $content ){
        $filteredContent = apply_filters( $this->final_outout_buffer_hook, $content );

        // remove filters after applying to prevent multiple applies
        remove_all_filters( $this->final_outout_buffer_hook );

        return $filteredContent;
	}
	
	/**
     * Filter for "dynamic_sidebar_params" hook
	 * 
     * @global array $wp_registered_widgets
     * @param  array $params
     * @return array
     */
    public function eeb_dynamic_sidebar_params( $params){
         global $wp_registered_widgets;

        if ( is_admin() ) {
            return $params;
        }

        $widget_id = $params[0]['widget_id'];

        // prevent overwriting when already set by another version of the widget output class
        if ( isset( $wp_registered_widgets[ $widget_id ]['_wo_original_callback'] ) ) {
            return $params;
        }

        $wp_registered_widgets[ $widget_id ]['_wo_original_callback'] = $wp_registered_widgets[ $widget_id ]['callback'];
        $wp_registered_widgets[ $widget_id ]['callback'] = array( $this, 'call_widget_callback' );

        return $params;
	}
	
	/**
     * The Widget Callback
     * @global array $wp_registered_widgets
     */
    public function call_widget_callback(){
        global $wp_registered_widgets;

		$original_callback_params = func_get_args();
		$original_callback = null;
		
		$widget_id = $original_callback_params[0]['widget_id'];

		$original_callback = $wp_registered_widgets[ $widget_id ]['_wo_original_callback'];
		$wp_registered_widgets[ $widget_id ]['callback'] = $original_callback;

		$widget_id_base = $wp_registered_widgets[ $widget_id ]['callback'][0]->id_base;

        if ( is_callable( $original_callback ) ) {
            ob_start();
            call_user_func_array( $original_callback, $original_callback_params );
            $widget_output = ob_get_clean();

            echo apply_filters( $this->widget_callback_hook, $widget_output, $widget_id_base, $widget_id );

            // remove filters after applying to prevent multiple applies
            remove_all_filters( $this->widget_callback_hook );
        }
    }

	/**
	 * ######################
	 * ###
	 * #### SCRIPT ENQUEUEMENTS
	 * ###
	 * ######################
	 */

	public function load_frontend_header_styling(){

		$js_version  = date( "ymd-Gis", filemtime( EEB_PLUGIN_DIR . 'core/includes/assets/js/custom.js' ));
		$css_version = date( "ymd-Gis", filemtime( EEB_PLUGIN_DIR . 'core/includes/assets/css/style.css' ));
		$protection_activated = (int) EEB()->settings->get_setting( 'protect', true );
		$without_javascript = (string) EEB()->settings->get_setting( 'protect_using', true );
		$footer_scripts = (bool) EEB()->settings->get_setting( 'footer_scripts', true );
		 
		if( $without_javascript !== 'without_javascript' ){
			wp_enqueue_script( 'eeb-js-frontend', EEB_PLUGIN_URL . 'core/includes/assets/js/custom.js', array( 'jquery' ), $js_version, $footer_scripts );
		}
		
		wp_register_style( 'eeb-css-frontend',    EEB_PLUGIN_URL . 'core/includes/assets/css/style.css', false,   $css_version );
		wp_enqueue_style ( 'eeb-css-frontend' );

		if( (string) EEB()->settings->get_setting( 'show_encoded_check', true ) === '1' ){
			wp_enqueue_style('dashicons');
		}

	}

	/**
	 * ######################
	 * ###
	 * #### CORE LOGIC
	 * ###
	 * ######################
	 */

	 /**
	  * Register all single filters to protect your content
	  *
	  * @return void
	  */
    public function setup_single_filter_hooks(){

		if( EEB()->validate->is_post_excluded() ){
			return;
		}

		$protection_method = (int) EEB()->settings->get_setting( 'protect', true );
		$filter_rss = (int) EEB()->settings->get_setting( 'filter_rss', true, 'filter_body' );
		$remove_shortcodes_rss = (int) EEB()->settings->get_setting( 'remove_shortcodes_rss', true, 'filter_body' );
		$protect_shortcode_tags = (bool) EEB()->settings->get_setting( 'protect_shortcode_tags', true, 'filter_body' );
		$protect_shortcode_tags_valid = false;

		if ( is_feed() ) {
			
			if( $filter_rss === 1 ){
				add_filter( $this->final_outout_buffer_hook, array( $this, 'filter_rss' ), EEB()->settings->get_hook_priorities( 'filter_rss' ) );
			}
	   
			if ( $remove_shortcodes_rss ) {
				add_filter( $this->final_outout_buffer_hook, array( $this, 'callback_rss_remove_shortcodes' ), EEB()->settings->get_hook_priorities( 'callback_rss_remove_shortcodes' ) );
			}
			
        }

		if ( $protection_method === 2 ) {
			$protect_shortcode_tags_valid = true;

			$filter_hooks = array(
				'the_title', 
				'the_content', 
				'the_excerpt', 
				'get_the_excerpt',

				//Comment related
				'comment_text', 
				'comment_excerpt', 
				'comment_url',
				'get_comment_author_url',
				'get_comment_author_url_link',

				//Widgets
				'widget_title',
				'widget_text',
				'widget_content',
				'widget_output',
			);

			$filter_hooks = apply_filters( 'eeb/frontend/wordpress_filters', $filter_hooks );

			foreach ( $filter_hooks as $hook ) {
			   add_filter( $hook, array( $this, 'filter_content' ), EEB()->settings->get_hook_priorities( 'filter_content' ) );
			}
		} elseif( $protection_method === 1 ){
			$protect_shortcode_tags_valid = true;

			add_filter( $this->final_outout_buffer_hook, array( $this, 'filter_page' ), EEB()->settings->get_hook_priorities( 'filter_page' ) );
		}

		if( $protect_shortcode_tags_valid ){
			if( $protect_shortcode_tags ){
				add_filter( 'do_shortcode_tag', array( $this, 'filter_content' ), EEB()->settings->get_hook_priorities( 'do_shortcode_tag' ) );
			}
		}
		
	}
	
	/**
	 * Filter the page itself
	 * 
     * @param string $content
     * @return string
     */
    public function filter_page( $content ){
		$protect_using = (string) EEB()->settings->get_setting( 'protect_using', true );

        return EEB()->validate->filter_page( $content, $protect_using );
    }

    /**
	 * Filter the whole content
	 * 
     * @param string $content
     * @return string
     */
    public function filter_content( $content ){
		$protect_using = (string) EEB()->settings->get_setting( 'protect_using', true );
        return EEB()->validate->filter_content( $content, $protect_using );
    }

    /**
	 * Filter the rss content
	 * 
     * @param string $content
     * @return string
     */
    public function filter_rss( $content ){
		$protection_type = (string) EEB()->settings->get_setting( 'protect_using', true );
        return EEB()->validate->filter_rss( $content, $protection_type );
	}

	/**
     * RSS Callback Remove shortcodes
     * @param string $content
     * @return string
     */
    public function callback_rss_remove_shortcodes( $content ) {
        // strip shortcodes like [eeb_content], [eeb_form]
        $content = strip_shortcodes($content);

        return $content;
    }
	
	/**
	 * ######################
	 * ###
	 * #### SHORTCODES
	 * ###
	 * ######################
	 */

	 /**
     * Handle content filter shortcode
     * @param array   $atts
     * @param string  $content
     */
    public function protect_content_shortcode( $atts, $content = null ){
		$protect = (int) EEB()->settings->get_setting( 'protect', true );
		$protect_using = (string) EEB()->settings->get_setting( 'protect_using', true );
		$protection_activated = ( $protect === 1 || $protect === 2 ) ? true : false;

        if ( ! $protection_activated ) {
			return $content;
		}
		
		if( isset( $atts['protect_using'] ) ){
			$protect_using = $atts['protect_using'];
		}

        $content = EEB()->validate->filter_content( $content, $protect_using );

        return $content;
	}

	 /**
     * Return the email encoder form
     * @param array   $atts
     * @param string  $content
     */
    public function shortcode_email_encoder_form( $atts = array(), $content = null ){
		$display_encoder_form = (bool) EEB()->settings->get_setting( 'display_encoder_form', true, 'encoder_form' );

		if( $display_encoder_form ){
			return EEB()->validate->get_encoder_form();
		}

        return '';
	}

	 /**
     * Return the encoded content
     * @param array   $atts
     * @param string  $content
     */
    public function shortcode_eeb_content( $atts = array(), $content = null ){

		$original_content = $content;
		$show_encoded_check = (string) EEB()->settings->get_setting( 'show_encoded_check', true );

		if( ! isset( $atts['protection_text'] ) ){
			$protection_text = __( EEB()->settings->get_setting( 'protection_text', true ), 'email-protection-text-eeb-content' );
		} else {
			$protection_text = wp_kses_post( $atts['protection_text'] );
		}

		if( isset( $atts['method'] ) ){
			$method = sanitize_title( $atts['method'] );
		} else {
			$method = 'rot13';
		}

		if( isset( $atts['do_shortcode'] ) && $atts['do_shortcode'] === 'yes' ){
			$content = do_shortcode( $content );
		}

        switch( $method ){
			case 'enc_ascii':
			case 'rot13':
				$content = EEB()->validate->encode_ascii( $content, $protection_text );
				break;
			case 'enc_escape':
			case 'escape':
				$content = EEB()->validate->encode_escape( $content, $protection_text );
				break;
			case 'enc_html':
			case 'encode':
			default:
				$content = antispambot( $content );
				break;
		}

		 // mark link as successfullly encoded (for admin users)
		 if ( current_user_can( EEB()->settings->get_admin_cap( 'frontend-display-security-check' ) ) && $show_encoded_check ) {
            $content .= '<i class="eeb-encoded dashicons-before dashicons-lock" title="' . __( 'Email encoded successfully!', 'email-encoder-bundle' ) . '"></i>';
        }

		return apply_filters( 'eeb/frontend/shortcode/eeb_protect_content', $content, $atts, $original_content );
	}

	 /**
     * Return the encoded email
     * @param array   $atts
     * @param string  $content
     */
    public function shortcode_eeb_email( $atts = array(), $content = null ){

		$show_encoded_check = (bool) EEB()->settings->get_setting( 'show_encoded_check', true );
		$protection_text = __( EEB()->settings->get_setting( 'protection_text', true ), 'email-encoder-bundle' );

		if( empty( $atts['email'] ) ){
			return '';
		} else {
			$email = $atts['email'];
		}

		if( empty( $atts['extra_attrs'] ) ){
			$extra_attrs = '';
		} else {
			$extra_attrs = $atts['extra_attrs'];
		}

		if( empty( $atts['method'] ) ){
			$method = 'rot13';
		} else {
			$method = sanitize_title( $atts['method'] );
		}

		$custom_class = (string) EEB()->settings->get_setting( 'class_name', true );
		
		if( empty( $atts['display'] ) ) {
			$display = $email;
		} else {
			$display = html_entity_decode( $atts['display'] );
		}
		
		if( empty( $atts['noscript'] ) ) {
			$noscript = $protection_text;
		} else {
			$noscript = html_entity_decode( $atts['noscript'] );
		}
		
		$class_name = ' ' . trim( $extra_attrs );
		$class_name .= ' class="' . esc_attr( $custom_class ) . '"';
		$mailto = '<a href="mailto:' . $email . '"'. $class_name . '>' . $display . '</a>';
		
		switch( $method ){
			case 'enc_ascii':
			case 'rot13':
				$mailto = EEB()->validate->encode_ascii( $mailto, $noscript );
				break;
			case 'enc_escape':
			case 'escape':
				$mailto = EEB()->validate->encode_escape( $mailto, $noscript );
				break;
			case 'enc_html':
			case 'encode':
			default:
				$mailto = '<a href="mailto:' . antispambot( $email ) . '"'. $class_name . '>' . antispambot( $display ) . '</a>';
				break;
		}

		// mark link as successfullly encoded (for admin users)
		if ( current_user_can( EEB()->settings->get_admin_cap( 'frontend-display-security-check' ) ) && $show_encoded_check ) {
            $mailto .= '<i class="eeb-encoded dashicons-before dashicons-lock" title="' . __( 'Email encoded successfully!', 'email-encoder-bundle' ) . '"></i>';
        }

		return apply_filters( 'eeb/frontend/shortcode/eeb_mailto', $mailto );
	}
	
	/**
	 * ######################
	 * ###
	 * #### EMAIL IMAGE
	 * ###
	 * ######################
	 */

	 public function display_email_image(){

		if( ! isset( $_GET['eeb_mail'] ) ){
			return;
		}

		$email = sanitize_email( base64_decode( $_GET['eeb_mail'] ) );
		 
		if( ! is_email( $email ) || ! isset( $_GET['eeb_hash'] ) ){
			return;
		}

		$hash = (string) $_GET['eeb_hash'];
		$secret = EEB()->settings->get_email_image_secret();

		if( EEB()->validate->generate_email_signature( $email, $secret ) !== $hash ){
			wp_die( __('Your signture is invalid.', 'email-encoder-bundle') );
		}

		$image = EEB()->validate->email_to_image( $email );

		if( empty( $image ) ){
			wp_die( __('Your email could not be converted.', 'email-encoder-bundle') );
		}

		header('Content-type: image/png');
		echo $image;
		die();

	 }
	
	/**
	 * ######################
	 * ###
	 * #### TEMPLATE TAGS
	 * ###
	 * ######################
	 */

	 public function add_custom_template_tags(){
		$template_tags = EEB()->settings->get_template_tags();

		foreach( $template_tags as $hook => $callback ){

			//Make sure we only call our own custom template tags
			if( is_callable( array( $this, $callback ) ) ){
				apply_filters( $hook, array( $this, $callback ), 10 );
			}

		}
	 }

	 /**
	  * Filter for the eeb_filter template tag
	  *	
	  * This function is called dynamically by add_custom_template_tags 
	  * using the EEB()->settings->get_template_tags() callback.
	  * 
	  * @param string $content - the default content
	  * @return string - the filtered content
	  */
	 public function template_tag_eeb_filter( $content ){
		$protect_using = (string) EEB()->settings->get_setting( 'protect_using', true );
        return EEB()->validate->filter_content( $content, $protect_using );
	 }

	 /**
	  * Filter for the eeb_filter template tag
	  *	
	  * This function is called dynamically by add_custom_template_tags 
	  * using the EEB()->settings->get_template_tags() callback.
	  * 
	  * @param string $content - the default content
	  * @return string - the filtered content
	  */
	 public function template_tag_eeb_mailto( $email, $display = null, $atts = array() ){
        if ( is_array( $display ) ) {
            // backwards compatibility (old params: $display, $attrs = array())
            $atts   = $display;
            $display = $email;
        } else {
            $atts['href'] = 'mailto:'.$email;
        }

        return EEB()->validate->create_protected_mailto( $display, $atts );
	 }

}
