<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit( 'restricted access' );
}

/*
 * This is a class for Suite CRM API
 */
if ( ! class_exists( 'SuiteCRM_REST_API' ) ) {
    class SuiteCRM_REST_API {

        var $url;
        var $username;
        var $password;
        
        function __construct( $url, $username, $password ) {          
                        
            $this->url      = $url.'/service/v4_1/rest.php';
            $this->username = $username;
            $this->password = $password;
        }
        
        function authentication() {
            
            $data = array(
                'method'        => 'login',
                'input_type'    => 'JSON',
                'response_type' => 'JSON',
                'rest_data'     => json_encode( array(
                        'user_auth' => array(
                            'user_name' => $this->username,
                            'password'  => md5( $this->password ),
                        ),
                    )),                
            );
            
            $ch = curl_init( $this->url );
            curl_setopt( $ch, CURLOPT_HEADER, false );
            curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
            curl_setopt( $ch, CURLOPT_SSL_VERIFYPEER, false );        
            curl_setopt( $ch, CURLOPT_POST, true );
            curl_setopt( $ch, CURLOPT_POSTFIELDS, $data );
            $json_response = curl_exec( $ch );
            curl_close( $ch );
            
            $response = json_decode( $json_response );
            
            return $response;
        }
        
        function addRecord( $session_id, $module, $data ) {            
            
            $filter_data = array();
            if ( $data != null ) {
                foreach ( $data as $key => $value ) {
                    $filter_data[] = array(
                        'name'  => $key,
                        'value' => $value,
                    );
                }
            }
            
            $data = array(
                'method'        => 'set_entry',
                'input_type'    => 'JSON',
                'response_type' => 'JSON',
                'rest_data'     => json_encode( array(
                        'session'           => $session_id,
                        'module_name'       => $module,
                        'name_value_list'   => $filter_data,
                    )),                
            );
            
            $ch = curl_init( $this->url );
            curl_setopt( $ch, CURLOPT_HEADER, false );
            curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
            curl_setopt( $ch, CURLOPT_SSL_VERIFYPEER, false );        
            curl_setopt( $ch, CURLOPT_POST, true );
            curl_setopt( $ch, CURLOPT_POSTFIELDS, $data );
            $json_response = curl_exec( $ch );
            curl_close( $ch );
            
            $response = json_decode( $json_response );
            
            return $response;
        }
        
        function getModuleFields( $session_id, $module ) {
            
            $data = array(
                'method'        => 'get_module_fields',
                'input_type'    => 'JSON',
                'response_type' => 'JSON',
                'rest_data'     => json_encode( array(
                        'session'           => $session_id,
                        'module_name'       => $module,
                        'fields'            => array(),
                    )),                
            );
            
            $ch = curl_init( $this->url );
            curl_setopt( $ch, CURLOPT_HEADER, false );
            curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
            curl_setopt( $ch, CURLOPT_SSL_VERIFYPEER, false );        
            curl_setopt( $ch, CURLOPT_POST, true );
            curl_setopt( $ch, CURLOPT_POSTFIELDS, $data );
            $json_response = curl_exec( $ch );
            curl_close( $ch );
            
            $response = json_decode( $json_response );
            
            $filter_fields = array();
            if ( isset( $response->module_fields ) && $response->module_fields != null ) {
                foreach ( $response->module_fields as $field ) {
                    if ( $field->related_module == null && $field->type != 'id' && $field->type != 'assigned_user_name' && $field->type != 'relate' ) {
                        $filter_fields[$field->name] = array(
                            'label'     => $field->label,
                            'type'      => $field->type,  
                            'required'  => $field->required,
                        );
                    }
                }
            }
            
            return $filter_fields;
        }
    }
}