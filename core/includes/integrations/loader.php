<?php
/**
 *
 * Load our custom marketing integrations
 *
 */

 // Exit if accessed directly.
if ( !defined( 'ABSPATH' ) ) exit;

class EEB_Integrations_Loader{

    function __construct(){
        $this->load_integrations();
    }

    public function load_integrations(){

        $plugins = array(
            'maintenance' => 'maintenance.php',
            'divi_theme' => 'divi_theme.php',
            'google_site_kit' => 'google_site_kit.php',
            'oxygen_builder' => 'oxygen_builder.php',
            'the_events_calendar' => 'the_events_calendar.php',
        );

        $services = array(
            'foggy_email' => 'foggy_email.php'
        );

        $integrations = array_merge( $plugins, $services );
        
        foreach ( $integrations as $plugin_id => $plugin_file ) :
        
            $plugin_file = 'classes/' . $plugin_file;
            $full_path = EEB_PLUGIN_DIR . 'core/includes/integrations/' . $plugin_file;
        
            if ( TRUE === apply_filters( 'eeb/integrations/' . $plugin_id, true ) ){
                if( file_exists( $full_path ) ){
                    include( $plugin_file );
                }
            }
        
        endforeach;

    }

}