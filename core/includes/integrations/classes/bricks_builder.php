<?php

// Exit if accessed directly.
if ( !defined( 'ABSPATH' ) ) exit;

if( ! class_exists( 'Email_Encoder_Integration_Bricks' ) ){

    /**
     * Class Email_Encoder_Integration_Bricks
     *
     * This class integrates support for the Bricks page builder https://bricksbuilder.io/
     *
     * @since 2.1.6
     * @package EEB
     * @author Ironikus <info@ironikus.com>
     */

    class Email_Encoder_Integration_Bricks{

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
          * Verify if Bricks builder is active 
          * in the first place
          *
          * @return array
          */
        public function is_bricks_active(){
            return function_exists( 'bricks_is_builder' );
        }
        
        public function deactivate_logic( $fields ){

            if( $this->is_bricks_active() ){

                if( function_exists( 'bricks_is_builder' ) && bricks_is_builder() ){
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

    new Email_Encoder_Integration_Bricks();
}
