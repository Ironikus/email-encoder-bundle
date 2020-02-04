<?php

// Exit if accessed directly.
if ( !defined( 'ABSPATH' ) ) exit;

if( ! class_exists( 'Email_Encoder_Integration_The_Events_Calendar' ) ){

    /**
     * Class Email_Encoder_Integration_The_Events_Calendar
     *
     * This class integrates support for The Events Calendar https://de.wordpress.org/plugins/the-events-calendar/
     *
     * @since 2.0.7
     * @package EEB
     * @author Ironikus <info@ironikus.com>
     */

    class Email_Encoder_Integration_The_Events_Calendar{

        /**
         * Our Email_Encoder_Run constructor.
         */
        function __construct(){
            add_filter( 'tribe_get_organizer_email', array( $this, 'deactivate_tribe_email_filter' ), 100, 2 );
        }

        /**
         * ######################
         * ###
         * #### CORE LOGIC
         * ###
         * ######################
         */

        public function deactivate_tribe_email_filter( $filtered_email, $unfiltered_email ){
            return $unfiltered_email;
        }
        

    }

    new Email_Encoder_Integration_The_Events_Calendar();
}
