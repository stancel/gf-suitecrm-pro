<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit( 'restricted access' );
}

/*
 * This is a function that create menu
 */
if ( ! function_exists( 'gf_sc_add_configuration_sub_menu' ) ) {
    add_action('admin_menu', 'gf_sc_add_configuration_sub_menu');
    function gf_sc_add_configuration_sub_menu() {
        
        add_submenu_page( 'gf_sc-integration', 'Gravity Forms Suite CRM Configuration', 'Configuration', 'manage_options', 'gf_sc-configuration', 'gf_sc_configuration_sub_menu_callback' );
        add_submenu_page( 'gf_sc-integration', 'Gravity Forms Suite CRM Licence Verification', 'Licence Verification', 'manage_options', 'gf_sc_licence_verification', 'gf_sc_licence_verification' );
    }
}

/*
 * This is a function for configuration
 */
if ( ! function_exists( 'gf_sc_configuration_sub_menu_callback' ) ) {
    function gf_sc_configuration_sub_menu_callback() {
        
        if ( isset( $_REQUEST['submit'] ) ) {
            $request = $_REQUEST;
            unset( $request['submit'] );
            if ( $request != null ) {
                foreach ( $request as $key => $value ) {
                    if ( $key == 'gf_sc_password' ) {
                        update_option( $key, gf_sc_crypt( $value, 'e', $request['gf_sc_username'] ) );
                    } else {
                        update_option( $key, $value );
                    }
                }
            }
        }
        
        $sc_url = get_option( 'gf_sc_url' );
        $username = get_option( 'gf_sc_username' );
        $password = gf_sc_crypt( get_option( 'gf_sc_password' ), 'd', $username );
        
        $gf_sc_licence = get_site_option( 'gf_sc_licence' );
        ?>
            <div class="wrap">                
                <h1><?php _e( 'Gravity Forms Suite CRM Configuration' ); ?></h1>
                <hr>
                <?php
                if ( $gf_sc_licence ) {
                    if ( isset( $_REQUEST['submit'] ) ) {
                        $gf_sc = new SuiteCRM_REST_API( $sc_url, $username, $password );
                        $authentication = $gf_sc->authentication();
                        if ( ! isset( $authentication->id ) ) {                            
                            ?>
                                <div class="notice notice-error is-dismissible">
                                    <p><?php _e( 'Configuration failure.' ); ?></p>
                                </div>
                            <?php
                        } else {
                            $modules = unserialize( get_option( 'gf_sc_modules' ) );                            
                            $gf_sc_modules_fields = array();
                            if ( $modules != null ) {
                                foreach( $modules as $key => $value ) {
                                    $gf_sc_modules_fields[$key] = $gf_sc->getModuleFields( $authentication->id, $key );
                                }
                            }
                            update_option( 'gf_sc_modules_fields', $gf_sc_modules_fields );
                            ?>
                                <div class="notice notice-success is-dismissible">
                                    <p><?php _e( 'Configuration successful.' ); ?></p>
                                </div>
                            <?php
                        }                        
                    }
                    ?>
                    <form method="post">
                        <table class="form-table">
                            <tbody>
                                <tr>
                                    <th scope="row"><label><?php _e( 'URL' ); ?> <span class="description">(required)</span></label></th>
                                    <td>
                                        <input class="regular-text" type="text" name="gf_sc_url" value="<?php echo $sc_url; ?>" required />
                                        <p class="description"><?php _e( 'Enter instance URL. Like' ); ?> http://example.com</p>
                                    </td>
                                </tr>                            
                                <tr>
                                    <th scope="row"><label><?php _e( 'Username' ); ?> <span class="description">(required)</span></label></th>
                                    <td>
                                        <input class="regular-text" type="text" name="gf_sc_username" value="<?php echo $username; ?>" required />
                                    </td>
                                </tr>
                                <tr>
                                    <th scope="row"><label><?php _e( 'Password' ); ?> <span class="description">(required)</span></label></th>
                                    <td>
                                        <input class="regular-text" type="password" name="gf_sc_password" value="" required />
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                        <p><input type='submit' class='button-primary' name="submit" value="<?php _e( 'Save' ); ?>" /></p>
                    </form>
                    <?php
                } else {
                    ?>
                        <div class="notice notice-error is-dismissible">
                            <p><?php _e( 'Please verify purchase code.' ); ?></p>
                        </div>
                    <?php
                }
                ?>
            </div>
        <?php
    }
}

/*
 * This is a function that verify product licence.
 */
if ( ! function_exists( 'gf_sc_licence_verification' ) ) {
    function gf_sc_licence_verification() {
        
        if ( isset( $_REQUEST['verify'] ) ) {
            if ( isset( $_REQUEST['gf_sc_purchase_code'] ) ) {
                update_site_option( 'gf_sc_purchase_code', $_REQUEST['gf_sc_purchase_code'] );
                
                $data = array(
                    'sku'           => '20174494',
                    'purchase_code' => $_REQUEST['gf_sc_purchase_code'],
                    'domain'        => site_url(),
                    'status'        => 'verify',
                    
                );

                $ch = curl_init();
                curl_setopt( $ch, CURLOPT_URL, 'https://obtaincode.net/extension/' );
                curl_setopt( $ch, CURLOPT_POST, 1 );
                curl_setopt( $ch, CURLOPT_POSTFIELDS, http_build_query( $data ) );
                curl_setopt( $ch, CURLOPT_RETURNTRANSFER, 1 );
                curl_setopt( $ch, CURLOPT_SSL_VERIFYPEER, 0 );
                $json_response = curl_exec( $ch );
                curl_close ($ch);
                
                $response = json_decode( $json_response );
                $response = json_decode( $json_response );
                if ( isset( $response->success ) ) {
                    if ( $response->success ) {
                        update_site_option( 'gf_sc_licence', 1 );
                    }
                }
            }
        } else if ( isset( $_REQUEST['unverify'] ) ) {
            if ( isset( $_REQUEST['gf_sc_purchase_code'] ) ) {
                $data = array(
                    'sku'           => '20174494',
                    'purchase_code' => $_REQUEST['gf_sc_purchase_code'],
                    'domain'        => site_url(),
                    'status'        => 'unverify',
                    
                );

                $ch = curl_init();
                curl_setopt( $ch, CURLOPT_URL, 'https://obtaincode.net/extension/' );
                curl_setopt( $ch, CURLOPT_POST, 1 );
                curl_setopt( $ch, CURLOPT_POSTFIELDS, http_build_query( $data ) );
                curl_setopt( $ch, CURLOPT_RETURNTRANSFER, 1 );
                curl_setopt( $ch, CURLOPT_SSL_VERIFYPEER, 0 );
                $json_response = curl_exec( $ch );
                curl_close ($ch);

                $response = json_decode( $json_response );
                if ( isset( $response->success ) ) {
                    if ( $response->success ) {
                        update_site_option( 'gf_sc_purchase_code', '' );
                        update_site_option( 'gf_sc_licence', 0 );
                    }
                }
            }
        }    
        
        $gf_sc_purchase_code = get_site_option( 'gf_sc_purchase_code' );
        ?>
            <div class="wrap">      
                <h2><?php _e( 'Licence Verification' ); ?></h2>
                <?php
                    if ( isset( $response->success ) ) {
                        if ( $response->success ) {                            
                             ?>
                                <div class="notice notice-success is-dismissible">
                                    <p><?php echo $response->message; ?></p>
                                </div>
                            <?php
                        } else {
                            update_site_option( 'gf_sc_licence', 0 );
                            ?>
                                <div class="notice notice-error is-dismissible">
                                    <p><?php echo $response->message; ?></p>
                                </div>
                            <?php
                        }
                    }
                ?>
                <form method="post">
                    <table class="form-table">                    
                        <tbody>
                            <tr>
                                <th scope="row"><?php _e( 'Purchase Code' ); ?></th>
                                <td>
                                    <input name="gf_sc_purchase_code" type="text" class="regular-text" value="<?php echo $gf_sc_purchase_code; ?>" />
                                </td>
                            </tr>
                        </tbody>
                    </table>
                    <p>
                        <input type='submit' class='button-primary' name="verify" value="<?php _e( 'Verify' ); ?>" />
                        <input type='submit' class='button-primary' name="unverify" value="<?php _e( 'Unverify' ); ?>" />
                    </p>
                </form>   
            </div>
        <?php
    }
}