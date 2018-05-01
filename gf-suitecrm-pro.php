<?php
/*
Plugin Name: Gravity Forms - Suite CRM
Description: This plugin can integrate Contacts, Cases and Leads between your WordPress Gravity Forms and Suite CRM. Easily add automatically Contacts, Cases and Leads into Suite CRM when people submit a Gravity Forms form on your site.
Version:     1.0.0
Author:      Obtain Code
Author URI:  https://obtaincode.com/
License:     GPL2
*/

if ( ! defined( 'ABSPATH' ) ) {
    exit( 'restricted access' );
}

/*
 * This is a class file for Suite CRM API
 */
include_once( 'includes/class-suitecrm.php' );

/*
 * This is a core functions file
 */
include_once( 'includes/functions.php' );

/*
 * This is a integration file
 */
include_once( 'admin/integration.php' );

/*
 * This is a configuration file
 */
include_once( 'admin/configuration.php' );

/*
 * This is a function that run during active plugin
 */
if ( ! function_exists( 'gf_sc_activation' ) ) {
    register_activation_hook( __FILE__, 'gf_sc_activation' );
    function gf_sc_activation() {
        
        update_option( 'gf_sc_modules', 'a:3:{s:8:"Contacts";s:8:"Contacts";s:5:"Cases";s:5:"Cases";s:5:"Leads";s:5:"Leads";}' );
    }
}

/*
 * This is a function that integrate form
 * $gf variable return form data
 */
if ( ! function_exists( 'gf_sc_integration' ) ) {
    add_action( 'gform_pre_submission', 'gf_sc_integration', 20, 1 );
    function gf_sc_integration( $gf ) {
        
        $form_id = 0;
        if ( isset( $gf['id'] ) ) {
            $form_id = intval( $gf['id'] );
        }
        
        if ( $form_id ) {
            $gf_sc = get_option( 'gf_sc_'.$form_id );
            if ( $gf_sc ) {
                $gf_sc_fields = get_option( 'gf_sc_fields_'.$form_id );
                if ( $gf_sc_fields != null ) {                    
                    $gf_sc_data = array();
                    foreach ( $gf_sc_fields as $gf_field_key => $sf_field ) {
                        if ( isset( $sf_field['key'] ) && $sf_field['key'] ) {
                            $gf_sc_data[$sf_field['key']] = $_REQUEST['input_'.$gf_field_key];
                        }
                    }
                    
                    if ( $gf_sc_data != null ) {
                        $sc_url = get_option( 'gf_sc_url' );
                        $username = get_option( 'gf_sc_username' );
                        $password = gf_sc_crypt( get_option( 'gf_sc_password' ), 'd', $username );                        
                        $gf_sc = new SuiteCRM_REST_API( $sc_url, $username, $password );
                        $authentication = $gf_sc->authentication();
                        if ( ! isset( $authentication->error ) ) { 
                            $gf_sc_module = get_option( 'gf_sc_module_'.$form_id );
                            $gf_sc->addRecord( $authentication->id, $gf_sc_module, $gf_sc_data );
                        }
                    }
                }
            }
        }        
    }
}