<?php
if ( !defined( 'ABSPATH' ) ) {
    exit;
}

class McisoeCheckPremium
{

    public function check_master()
    {
        // Includes check premium
        require_once MCISOE_PLUGIN_DIR . 'includes/check_premium/mcisoe_check_api.php';
        //require_once MCISOE_PLUGIN_DIR . 'includes/check_premium/mcisoe_old_auth.php';
        require_once MCISOE_PLUGIN_DIR . 'includes/check_premium/mcisoe_check_lemon.php';

        if ( !defined( 'MCI_API_KEY' ) ) {
            define( 'MCI_API_KEY', 'oxAuRZ1WUqhGIYiQLjfTkSCgPlte43ND' );
        }

        $check_mciapi = new MciCheckApi;
        //$mci_old_auth  = new MciOldAuth;
        $check_license_lemon = new CheckLicenseLemonMci;

        //Execute always if pages are admin.php?page=plugin_slug or plugins.php to activate/deactivate $mcisoe_auth_premium authorization
        global $pagenow;

        if (
            ( isset( $_GET['page'] ) && $_GET['page'] == MCISOE_SLUG && $pagenow == 'admin.php' ) ||
            $pagenow == 'plugins.php'
        ) {
            //Execute convert_pay_to_users if button is pressed
            if (
                isset( $_POST['submit_mcisoe_activate'] ) && isset( $_POST['mci_code_key'] ) && strlen( $_POST['mci_code_key'] ) > 18 &&
                !empty( $_POST['mci_code_key'] )
            ) {
                // Activate in MCI API
                $check_mciapi->convert_pay_to_user();

                // Activate in Lemon
                if ( $check_license_lemon->instance_exists() !== true ) {
                    $check_license_lemon->activate( sanitize_text_field( $_POST['mci_code_key'] ) );
                }

                if ( $check_mciapi->mci_verify() || $check_license_lemon->validate() ) {
                    update_option( 'mcisoe_auth_premium', '1' );
                    printf( '<div class="notice notice-success is-dismissible mcisoe_notice"><p>' .
                        __( 'The Premium plugin is activated. ', 'supplier-order-email' ) .
                        '</p></div>' );
                } else {
                    update_option( 'mcisoe_auth_premium', '0' );
                    printf( '<div class="notice notice-error is-dismissible mcisoe_notice"><p>' .
                        __( 'The Premium plugin is disabled. ', 'supplier-order-email' ) .
                        '</p></div>' );
                }
            }

            $check_mciapi        = $check_mciapi->mci_verify();
            $check_license_lemon = $check_license_lemon->validate();

            // Check MciAPI & LemonAPI and deactivate 'mcisoe_auth_premium' if none are valid
            if ( $check_mciapi == false
                && $check_license_lemon == false
                && $check_license_lemon != 'server_error'
            ) {
                update_option( 'mcisoe_auth_premium', '0' );
            }

            // Check MciAPI and update option 'mcisoe_auth_mciapi' for hide email activation fields
            if ( $check_mciapi ) {
                update_option( 'mcisoe_auth_mciapi', '1' );
            } else {
                update_option( 'mcisoe_auth_mciapi', '0' );
            }

            // Check LemonAPI and update option 'mcisoe_auth_lemon' for hide email activation fields
            if ( $check_license_lemon ) {
                update_option( 'mcisoe_auth_lemon', '1' );
            } else {
                if ( $check_license_lemon != 'server_error' ) {
                    update_option( 'mcisoe_auth_lemon', '0' );
                }
            }

            // This method was for old users who are already up to date. That's why it was removed. // Este metodo era para usuarios antiguos que ya están actualizados. Por eso ya se eliminó.
            //$mci_old_auth->create_user_from_old();
        }
    }

    // End check_master function

    public function init()
    {
        add_action( 'admin_init', array( $this, 'check_master' ) );
    }
}