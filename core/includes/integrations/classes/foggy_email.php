<?php

// Exit if accessed directly.
if ( !defined( 'ABSPATH' ) ) exit;

if( ! class_exists( 'Email_Encoder_Integration_FoggyEmail' ) ){

    /**
     * Class Email_Encoder_Integration_FoggyEmail
     *
     * This class integrates support for the oxygen page builder https://oxygenbuilder.com/
     *
     * @since 2.0.6
     * @package EEB
     * @author Ironikus <info@ironikus.com>
     */

    class Email_Encoder_Integration_FoggyEmail{

        /**
         * The main page name for our admin page
         *
         * @var string
         * @since 2.0.6
         */
        private $page_name;

        /**
         * The main page title for our admin page
         *
         * @var string
         * @since 2.0.6
         */
        private $page_title;

        /**
         * Our Email_Encoder_Run constructor.
         */
        function __construct(){
            $this->page_name    = EEB()->settings->get_page_name();
            $this->page_title   = EEB()->settings->get_page_title();
            $this->foggy_key    = 'foggy_email_emails';
            $this->foggy_emails = $this->load_foggy_emails();
            $this->api_endpoint = 'https://foggy.email/api/';
            $this->add_hooks();
        }

        /**
         * ######################
         * ###
         * #### HELPERS
         * ###
         * ######################
         */

         public function is_active(){
            return false; //Got discontinued
         }

        /**
         * ######################
         * ###
         * #### FOGGY EMAILS
         * ###
         * ######################
         */

         public function load_foggy_emails(){
            
            $return = array();
            $emails = get_option( $this->foggy_key );
            if( ! empty( $emails ) && is_array( $emails ) ){
                $return = $emails;
            } else {
                if( ! is_array( $emails ) ){
                    $emails = array();
                }
            }

            return $return;
         }

        /**
         * Define all of our necessary hooks
         */
        private function add_hooks(){
            add_filter( 'eeb/settings/pre_filter_fields', array( $this, 'load_foggy_email_settings' ), 10 );
            add_filter( 'eeb/validate/filter_page_content', array( $this, 'disguise_emails' ), 10, 2 );
            add_filter( 'eeb/validate/filter_content_content', array( $this, 'disguise_emails' ), 10, 2 );
            add_action( 'init', array( $this, 'maybe_deactivate_foggy_email' ), 5 );
            #add_action( 'init', array( $this, 'reload_settings' ), 5 );
        }

        /**
         * ######################
         * ###
         * #### CORE LOGIC
         * ###
         * ######################
         */

         public function maybe_deactivate_foggy_email(){
            $foggy_email_api_key = (string) EEB()->settings->get_setting( 'foggy_email_api_key', true );
            $emails = get_option( $this->foggy_key );

            //Make sure after deactivating the logic, the emails get removed as well
            if( empty( $foggy_email_api_key ) && ! empty( $emails ) ){

                //Allow a hard reset of the added emails
                if( isset( $_GET['eeb_fggy_email_clear_entries'] ) && current_user_can( 'manage_options' ) ){
                    $emails = null;
                }

                if( is_array( $emails ) ){
                    foreach( $emails as $key => $mail ){
                        if( isset( $mail['alias'] ) && isset( $mail['api_key'] ) ){
                            $alias = $mail['alias'];
                            $api_key = base64_decode( $mail['api_key'] );
    
                            if( ! empty( $alias ) && ! empty( $api_key ) ){
                                $check = $this->delete_foggy_email( $api_key, $alias );
                                if( $check ){
                                    unset( $emails[ $key ] );
                                }
                            }
                        }
                    }
                }

                if( empty( $emails ) ){
                    delete_option( $this->foggy_key );
                } else {
                    update_option( $this->foggy_key, $emails );
                }
            }
         }

        public function disguise_emails( $content, $protect_using ){
            $foggy_email_api_key = (string) EEB()->settings->get_setting( 'foggy_email_api_key', true );

            //Shorten circuit if nothing is set
            if( empty( $foggy_email_api_key ) ){
                return $content;
            }

            $self = $this;
            $content = EEB()->validate->filter_plain_emails( $content, function ( $match ) use ( $self ) {
                return $self->get_disguised_email( $match[0] );
            }, 'no_encoding', false);

            return $content;
        }

        public function get_disguised_email( $email ){

            if( ! is_email( $email ) || strpos( $email, 'neat.email' ) !== FALSE ){
                return $email;
            }

            $email_key = base64_encode( $email );
            if( isset( $this->foggy_emails[ $email_key ] ) ){
                $alias_email = $this->foggy_emails[ $email_key ]['alias_email'];

                if( ! empty( $alias_email ) ){
                    if( is_email( $alias_email ) ){
                        $email = $alias_email;
                    } 
                }
            } else {
                $email = $this->create_foggy_email( $email );
            }

            return $email;
        }

        public function create_foggy_email( $email ){
            $foggy_email_api_key = (string) EEB()->settings->get_setting( 'foggy_email_api_key', true );

            $return = $email;
            $args = array(
                'foggyemail_api' => 'create_alias'
            );
            $endpoint = EEB()->helpers->built_url( $this->api_endpoint, $args );

            $http_args = array(
                'method'      => 'POST',
                'timeout'     => 45,
                'blocking'    => true,
                'headers'     => array(
                    'Content-Type' => 'application/json'
                ),
                'body'        => json_encode( array(
                    'foggyemail_ussauth' => $foggy_email_api_key,
                    'email' => $email,
                ) ),
                'cookies'     => array()
            );
            $http_args = apply_filters( 'eeb/integrations/foggy_email/http_args', $http_args, $email );

            $response = wp_remote_post( $endpoint, $http_args );

            if ( ! is_wp_error( $response ) && isset( $response['body'] ) ) {
                $response_data = json_decode( $response['body'], true );
                if( ! empty( $response_data ) ){
                    if( isset( $response_data['success'] ) && $response_data['success'] == 'true' ){
                        if( isset( $response_data['data'] ) && isset( $response_data['data']['alias'] ) && isset( $response_data['data']['domain'] ) ){
                            $email_key = base64_encode( $email );
                            $this->foggy_emails[ $email_key ] = array(
                                'email' => $email,
                                'alias' => $response_data['data']['alias'],
                                'alias_email' => $response_data['data']['alias_email'],
                                'domain' => $response_data['data']['domain'],
                                'date' => date( "Y-m-d H:i:s" ),
                                'api_key' => base64_encode( $foggy_email_api_key )
                            );

                            update_option( $this->foggy_key, $this->foggy_emails );
                            $return = $response_data['data']['alias_email'];
                        }
                    }
                }
            }

            return $return;
        }

        public function delete_foggy_email( $foggy_email_api_key, $alias ){

            $return = false;
            $args = array(
                'foggyemail_api' => 'delete_alias'
            );
            $endpoint = EEB()->helpers->built_url( $this->api_endpoint, $args );

            $http_args = array(
                'method'      => 'POST',
                'timeout'     => 45,
                'blocking'    => true,
                'headers'     => array(
                    'Content-Type' => 'application/json'
                ),
                'body'        => json_encode( array(
                    'foggyemail_ussauth' => $foggy_email_api_key,
                    'alias' => $alias,
                ) ),
                'cookies'     => array()
            );
            $response = wp_remote_post( $endpoint, $http_args );

            if ( ! is_wp_error( $response ) && isset( $response['body'] ) ) {
                $response_data = json_decode( $response['body'], true );
                if( ! empty( $response_data ) ){
                    if( isset( $response_data['success'] ) && $response_data['success'] == 'true' ){
                        $return = true;
                    }
                }
            }

            return $return;
        }

        /**
         * ######################
         * ###
         * #### SCRIPTS & STYLES
         * ###
         * ######################
         */

         public function reload_settings(){
            EEB()->settings->reload_settings();
         }
        
        public function load_foggy_email_settings( $fields ){
            
            $slug = 'foggy_email_api_key';
            $new_field = array(
				'fieldset'    => array( 'slug' => 'main', 'label' => 'Label' ),
				'id'          => $slug,
				'type'        => 'text',
				'advanced' 	  => false,
				'title'       => __('Foggy Email API Key', 'email-encoder-bundle'),
				'placeholder' => '',
				'required'    => false,
				'description' => __('Create anonymous emails using <a title="Visit Foggy Email" target="_blank" href="https://foggy.email">https://foggy.email</a> - This will turn every of your emails into a disguised email. For example: <strong>you@example.com</strong> turns into <strong>luhhsd@neat.email</strong>, which will forward every email to <strong>you@example.com</strong>', 'email-encoder-bundle')
			);

            if( is_array( $fields ) ){
                if( ! isset( $fields[ $slug ] ) ){
                    $fields[ $slug ] = $new_field;
                }
            }

            return $fields;
            
        }
        

    }

    new Email_Encoder_Integration_FoggyEmail();
}
