<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit( 'restricted access' );
}

if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
    die;
}

/*
 * Delete options when plugin uninstall
 */
delete_option( 'gf_sc_url' );
delete_option( 'gf_sc_username' );
delete_option( 'gf_sc_password' );
delete_option( 'gf_sc_modules' );
delete_option( 'gf_sc_modules_fields' );