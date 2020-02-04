<?php

// Exit if accessed directly.
if ( !defined( 'ABSPATH' ) ) exit;

if( ! class_exists( 'Email_Encoder_Integration_Google_Site_Kit' ) ){

    /**
     * Class Email_Encoder_Integration_Google_Site_Kit
     *
     * This class integrates support for Google Site Kit plugin https://wordpress.org/plugins/google-site-kit/
     *
     * @since 2.0.7
     * @package EEB
     * @author Ironikus <info@ironikus.com>
     */

    class Email_Encoder_Integration_Google_Site_Kit{

        /**
         * Our Email_Encoder_Run constructor.
         */
        function __construct(){
            add_filter( 'googlesitekit_admin_data', array( $this, 'soft_encode_googlesitekit_admin_data' ), 100, 1 );
        }

        /**
         * ######################
         * ###
         * #### CORE LOGIC
         * ###
         * ######################
         */

        public function soft_encode_googlesitekit_admin_data( $admin_data ){

            $soft_encode = apply_filters( 'eeb/integrations/google_site_kit/soft_encode', true );

            if( isset( $admin_data['userData'] ) && isset( $admin_data['userData']['email'] ) ){

                if( $soft_encode ){
                    $admin_data['userData']['email'] = antispambot( $admin_data['userData']['email'] );
                } else {
                    $admin_data['userData']['email'] = EEB()->validate->temp_encode_at_symbol( $admin_data['userData']['email'] );
                }

            }
            
            return $admin_data;
        }
        

    }

    new Email_Encoder_Integration_Google_Site_Kit();
}
