<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit( 'restricted access' );
}

/*
 * This is a function that crypt data
 * $string variable return original data
 * $action variable return crypt type
 * $secret variable return secret data
 */
if ( ! function_exists( 'gf_sc_crypt' ) ) {
    function gf_sc_crypt( $string, $action = 'e', $secret ) {
       
        if ( extension_loaded( 'openssl' ) ) {
            $secret_key = $secret.'gf_sc_key';
            $secret_iv = $secret.'gf_sc_iv';

            $output = false;
            $encrypt_method = 'AES-256-CBC';
            $key = hash( 'sha256', $secret_key );
            $iv = substr( hash( 'sha256', $secret_iv ), 0, 16 );

            if( $action == 'e' ) {
                $output = base64_encode( openssl_encrypt( $string, $encrypt_method, $key, 0, $iv ) );
            }
            else if( $action == 'd' ){
                $output = openssl_decrypt( base64_decode( $string ), $encrypt_method, $key, 0, $iv );
            }

            return $output;
        } else {
            return $string;
        }
    }
}