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
        $disable_marketing = (bool) EEB()->settings->get_setting( 'disable_marketing', true );

        $marketing = array(
            'mailoptin' => 'mailoptin.php',
        );

        $plugins = array(
            'maintenance' => 'maintenance.php',
            'divi_theme' => 'divi_theme.php',
        );
        
        if( $disable_marketing ){
            $marketing = array();
        }

        $integrations = array_merge( $marketing, $plugins );
        
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