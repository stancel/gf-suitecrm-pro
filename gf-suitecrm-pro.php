<?php
/*
Plugin Name: Gravity Forms - Suite CRM
Description: This plugin can integrate Contacts, Cases and Leads between your WordPress Gravity Forms and Suite CRM. Easily add automatically Contacts, Cases and Leads into Suite CRM when people submit a Gravity Forms form on your site.
Version:     1.3.0
Author:      Obtain Code
Author URI:  http://obtaincode.com/
License:     GPL2
*/

if ( ! defined( 'ABSPATH' ) ) {
    exit( 'restricted access' );
}

define( 'GF_SC_PLUGIN_PATH', plugin_dir_path( __FILE__ ) );

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
    add_action( 'gform_after_submission', 'gf_sc_integration', 20, 2 );
    function gf_sc_integration( $entry, $gf ) {
        
        $licence = get_site_option( 'gf_sc_licence' );
        if ( $licence ) {
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
                        foreach ( $gf_sc_fields as $gf_field_key => $sc_field ) {
                            if ( isset( $sc_field['key'] ) && $sc_field['key'] ) {
                                if ( isset( $sc_field['field_type'] ) && $sc_field['field_type'] == 'name' ) {
                                    if ( $sc_field['key'] == 'last_name' ) {
                                        $gf_sc_data['first_name'] = strip_tags( $entry[$gf_field_key.'.3'] );
                                        $entry[$gf_field_key] = $entry[$gf_field_key.'.6'];
                                    } else {
                                        $entry[$gf_field_key] = $entry[$gf_field_key.'.3'].' '.$entry[$gf_field_key.'.6'];
                                    }
                                } else if ( isset( $sc_field['field_type'] ) && $sc_field['field_type'] == 'checkbox' ) {
                                    for( $i = 1; $i <= 20; $i++ ) {
                                        if ( isset( $entry[$gf_field_key.'.'.$i] ) ) {
                                            if ( $entry[$gf_field_key.'.'.$i] ) {
                                                $entry[$gf_field_key][] = $entry[$gf_field_key.'.'.$i];
                                            }
                                        }
                                    }
                                } else if ( isset( $sc_field['field_type'] ) && $sc_field['field_type'] == 'address' ) {
                                    if ( $sc_field['key'] == 'primary_address_street' ) {
                                        $entry[$gf_field_key] = strip_tags( $entry[$gf_field_key.'.1'] );
                                        $gf_sc_data['primary_address_street_2'] = strip_tags ( $entry[$gf_field_key.'.2'] );
                                        $gf_sc_data['primary_address_city'] = strip_tags ( $entry[$gf_field_key.'.3'] );
                                        $gf_sc_data['primary_address_state'] = strip_tags( $entry[$gf_field_key.'.4'] );
                                        $gf_sc_data['primary_address_postalcode'] = strip_tags( $entry[$gf_field_key.'.5'] );
                                        $gf_sc_data['primary_address_country'] = strip_tags( $entry[$gf_field_key.'.6'] );
                                    } else if ( $sc_field['key'] == 'alt_address_street' ) {
                                        $entry[$gf_field_key] = strip_tags( $entry[$gf_field_key.'.1'] );
                                        $gf_sc_data['alt_address_street_2'] = strip_tags ( $entry[$gf_field_key.'.2'] );
                                        $gf_sc_data['alt_address_city'] = strip_tags ( $entry[$gf_field_key.'.3'] );
                                        $gf_sc_data['alt_address_state'] = strip_tags( $entry[$gf_field_key.'.4'] );
                                        $gf_sc_data['alt_address_postalcode'] = strip_tags( $entry[$gf_field_key.'.5'] );
                                        $gf_sc_data['alt_address_country'] = strip_tags( $entry[$gf_field_key.'.6'] );
                                    } else {
                                        $entry[$gf_field_key] = $entry[$gf_field_key.'.1'].', '.$entry[$gf_field_key.'.2'].', '.$entry[$gf_field_key.'.3'].', '.$entry[$gf_field_key.'.4'].', '.$entry[$gf_field_key.'.5'].', '.$entry[$gf_field_key.'.6'];
                                    }
                                } else if ( isset( $sc_field['field_type'] ) && $sc_field['field_type'] == 'multiselect' ) {
                                    if ( $entry[$gf_field_key] ) {
                                        $entry[$gf_field_key] = json_decode( $entry[$gf_field_key] );
                                    }
                                }

                                if ( is_array( $entry[$gf_field_key] ) ) {
                                    $entry[$gf_field_key] = implode( ',', $entry[$gf_field_key] );
                                }

                                $gf_sc_data[$sc_field['key']] = strip_tags( $entry[$gf_field_key] );
                            }
                        }

                        if ( $gf_sc_data != null ) {
                            $sc_url = get_option( 'gf_sc_url' );
                            $username = get_option( 'gf_sc_username' );
                            $password = gf_sc_crypt( get_option( 'gf_sc_password' ), 'd', $username );                        
                            $gf_sc = new SuiteCRM_REST_API( $sc_url, $username, $password );
                            $authentication = $gf_sc->authentication();
                            if ( ! isset( $authentication->error ) && isset( $authentication->id ) ) {
                                $gf_sc_module = get_option( 'gf_sc_module_'.$form_id );
                                $gf_sc->addRecord( $authentication->id, $gf_sc_module, $gf_sc_data, $form_id );
                            }
                        }
                    }
                }
            }
        }
    }
}