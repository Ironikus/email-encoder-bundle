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
            'mailoptin' => 'mailoptin.php',
        );
        $disable_marketing = (bool) EEB()->settings->get_setting( 'disable_marketing', true );

        $filter_integrations = true;
        if( $disable_marketing ){
            $filter_integrations = false;
        }
        
        foreach ( $plugins as $plugin_id => $plugin_file ) :
        
            $plugin_file = 'classes/' . $plugin_file;
            $full_path = EEB_PLUGIN_DIR . 'core/includes/integrations/' . $plugin_file;
        
            if ( TRUE === apply_filters( 'eeb/integrations/' . $plugin_id, $filter_integrations ) ){
                if( file_exists( $full_path ) ){
                    include( $plugin_file );
                }
            }
        
        endforeach;

    }

}