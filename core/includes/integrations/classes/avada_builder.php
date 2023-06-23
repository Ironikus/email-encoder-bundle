<?php

// Exit if accessed directly.
if ( !defined( 'ABSPATH' ) ) exit;

if( ! class_exists( 'Email_Encoder_Integration_Avada' ) ){

    /**
     * Class Email_Encoder_Integration_Avada
     *
     * This class integrates support for the Avada page builder https://avada.com/
     *
     * @since 2.1.6
     * @package EEB
     * @author Ironikus <info@ironikus.com>
     */

    class Email_Encoder_Integration_Avada{

        /**
         * Our Email_Encoder_Run constructor.
         */
        function __construct(){
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
          * Verify if Avada builder is active 
          * in the first place
          *
          * @return array
          */
        public function is_avada_active(){
            return defined( 'FUSION_BUILDER_VERSION' );
        }
        
        public function deactivate_logic( $fields ){

            if( $this->is_avada_active() ){

                if( isset( $_GET['fb-edit'] ) ){
                    if( is_array( $fields ) ){
                        if( isset( $fields[ 'protect' ] ) ){
                            if( isset( $fields[ 'protect' ]['value'] ) ){
                                $fields[ 'protect' ]['value'] = 3; //3 equals "Do Nothing"
                            }
                        }
                    }
                }

            }

            return $fields;
            
        }
        

    }

    new Email_Encoder_Integration_Avada();
}
