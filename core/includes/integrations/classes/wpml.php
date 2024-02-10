<?php

// Exit if accessed directly.
if ( !defined( 'ABSPATH' ) ) exit;

if( ! class_exists( 'Email_Encoder_Integration_WPML' ) ){

    /**
     * Class Email_Encoder_Integration_WPML
     *
     * This class integrates support for the WPML translation plugin https://wpml.org/
     *
     * @since 2.1.6
     * @package EEB
     * @author Ironikus <info@ironikus.com>
     */

    class Email_Encoder_Integration_WPML{

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
            $this->add_hooks();
        }

        /**
         * Define all of our necessary hooks
         */
        private function add_hooks(){
            add_filter( 'eeb/settings/fields', array( $this, 'deactivate_logic' ), 10 );
        }

        /**
         * ######################
         * ###
         * #### HELPERS
         * ###
         * ######################
         */

         /**
          * Verify if WPML is active 
          * in the first place
          *
          * @return array
          */
        public function is_wpml_active(){
            return defined( 'ICL_SITEPRESS_VERSION' );
        }
        
        public function deactivate_logic( $fields ){

            if( $this->is_wpml_active() ){

                if( is_user_logged_in() && isset( $_GET['wpml-app'] ) && ! empty( $_GET['wpml-app'] ) ){
                    if( is_array( $fields ) ){
                        if( isset( $fields[ 'protect' ] ) ){
                            if( isset( $fields[ 'protect' ]['value'] ) ){
                                $fields[ 'protect' ]['value'] = 2;
                            }
                        }
                    }
                }

            }

            return $fields;
            
        }
        

    }

    new Email_Encoder_Integration_WPML();
}
