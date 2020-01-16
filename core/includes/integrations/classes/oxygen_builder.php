<?php

// Exit if accessed directly.
if ( !defined( 'ABSPATH' ) ) exit;

if( ! class_exists( 'Email_Encoder_Integration_Oxygen' ) ){

    /**
     * Class Email_Encoder_Integration_Oxygen
     *
     * This class integrates support for the oxygen page builder https://oxygenbuilder.com/
     *
     * @since 2.0.6
     * @package EEB
     * @author Ironikus <info@ironikus.com>
     */

    class Email_Encoder_Integration_Oxygen{

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
            add_action( 'init', array( $this, 'reload_settings_before_oxygen_builder' ), 5 );
        }

        /**
         * ######################
         * ###
         * #### HELPERS
         * ###
         * ######################
         */

        public function is_oxygen_active(){
            return defined( 'CT_VERSION' );
        }

        /**
         * ######################
         * ###
         * #### SCRIPTS & STYLES
         * ###
         * ######################
         */

         public function reload_settings_before_oxygen_builder(){
            EEB()->settings->reload_settings();
         }
        
        public function deactivate_logic( $fields ){

            if( $this->is_oxygen_active() ){
                if( isset( $_GET['ct_builder'] ) && $_GET['ct_builder'] === 'true' ){
                    if( is_array( $fields ) ){
                        if( isset( $fields[ 'protect' ] ) ){
                            if( isset( $fields[ 'protect' ]['value'] ) ){
                                $fields[ 'protect' ]['value'] = 3;
                            }
                        }
                    }
                }
            }

            return $fields;
            
        }
        

    }

    new Email_Encoder_Integration_Oxygen();
}
