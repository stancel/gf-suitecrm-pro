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
        ?>
            <div class="wrap">                
                <h1><?php _e( 'Gravity Forms Suite CRM Configuration' ); ?></h1>
                <hr>
                <?php
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
            </div>
        <?php
    }
}