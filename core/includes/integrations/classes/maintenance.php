<?php

// Exit if accessed directly.
if ( !defined( 'ABSPATH' ) ) exit;

if( ! class_exists( 'Email_Encoder_Integration_Maintenance' ) ){

    /**
     * Class Email_Encoder_Integration_Maintenance
     *
     * This class integrates support for the maintenance plugin: https://de.wordpress.org/plugins/maintenance/
     *
     * @since 2.0.0
     * @package EEB
     * @author Ironikus <info@ironikus.com>
     */

    class Email_Encoder_Integration_Maintenance{

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
            $this->page_name    = EEB()->settings->get_page_name();
            $this->page_title   = EEB()->settings->get_page_title();
            $this->add_hooks();
        }

        /**
         * Define all of our necessary hooks
         */
        private function add_hooks(){
            add_action( 'load_custom_style', array( $this, 'eeb_integrations_maintenance_load_custom_styles' ), 100 );
            add_action( 'load_custom_scripts',    array( $this, 'eeb_integrations_maintenance_load_custom_scripts' ), 100 );
        }

        /**
         * ######################
         * ###
         * #### HELPERS
         * ###
         * ######################
         */

        public function is_maintenance_active(){
            return class_exists( 'MTNC' );
        }

        /**
         * ######################
         * ###
         * #### SCRIPTS & STYLES
         * ###
         * ######################
         */
        
        public function eeb_integrations_maintenance_load_custom_styles(){

            if( ! $this->is_maintenance_active() ){
                return;
            }

            $protection_activated = (int) EEB()->settings->get_setting( 'protect', true );
            
            if( $protection_activated === 2 || $protection_activated === 1 ){
                
                echo '<link rel="stylesheet" id="eeb-css-frontend"  href="' . EEB_PLUGIN_URL . 'core/includes/assets/css/style.css' . '" type="text/css" media="all" />';
            
            }
        }
        
        public function eeb_integrations_maintenance_load_custom_scripts(){
            if( ! $this->is_maintenance_active() ){
                return;
            }

            $protection_activated = (int) EEB()->settings->get_setting( 'protect', true );
            $without_javascript = (string) EEB()->settings->get_setting( 'protect_using', true );
            
            if( $protection_activated === 2 || $protection_activated === 1 ){

                if( $without_javascript !== 'without_javascript' ){
                    echo '<script type="text/javascript" src="' . EEB_PLUGIN_URL . 'core/includes/assets/js/custom.js' . '"></script>';
                }
            
            }
        }
        

    }

    new Email_Encoder_Integration_Maintenance();
}
