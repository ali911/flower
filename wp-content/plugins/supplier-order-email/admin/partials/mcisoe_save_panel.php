<?php
if ( !defined( 'ABSPATH' ) ) {
    exit;
}

class McisoeSavePanel
{
    private $options;

    public function __construct( $options )
    {
        $this->options = $options;
    }

    public function save_panel_data()
    {
        if ( isset( $_POST['submit'] ) ) {

            // Check nonce field
            if ( isset( $_POST['mcisoe_nonce_field'] ) && wp_verify_nonce( $_POST['mcisoe_nonce_field'], 'mcisoe_nonce_field' ) ) {

                // Get the post data, validate, sanitize and save it
                // Subject
                if ( isset( $_POST['subject'] ) && !empty( $_POST['subject'] ) ) {
                    update_option( 'mcisoe_email_subject', sanitize_text_field( $_POST['subject'] ) );
                }
                // Intro
                if ( isset( $_POST['email_intro'] ) && !empty( $_POST['email_intro'] ) ) {
                    update_option( 'mcisoe_email_intro', sanitize_textarea_field( $_POST['email_intro'] ) );
                }
                // Select email admin
                if ( isset( $_POST['select_email_admin'] ) ) {
                    update_option( 'mcisoe_select_email_admin', '1' );
                } else {
                    update_option( 'mcisoe_select_email_admin', '0' );
                }
                // Replace address
                if ( isset( $_POST['replace_address'] ) ) {
                    update_option( 'mcisoe_replace_address', '1' );
                } else {
                    update_option( 'mcisoe_replace_address', '0' );
                }
                // Delete all data
                if ( isset( $_POST['delete_all_data'] ) ) {
                    update_option( 'mcisoe_delete_all_data', '1' );
                } else {
                    update_option( 'mcisoe_delete_all_data', '0' );
                }

                // PREMIUM OPTIONS ///////////////////////////////////////////////////////////////////

                if ( $this->options->auth_premium ) {
                    // Header color
                    if ( isset( $_POST['header_color'] ) && !empty( $_POST['header_color'] ) ) {
                        update_option( 'mcisoe_header_color', sanitize_hex_color( $_POST['header_color'] ) );
                    }
                    // Store logo
                    if ( isset( $_POST['store_logo'] ) ) {
                        update_option( 'mcisoe_store_logo', '1' );
                    } else {
                        update_option( 'mcisoe_store_logo', '0' );
                    }
                    // Hide customer
                    if ( isset( $_POST['hide_customer'] ) ) {
                        update_option( 'mcisoe_hide_customer', '1' );
                    } else {
                        update_option( 'mcisoe_hide_customer', '0' );
                    }
                    // Show customer email
                    if ( isset( $_POST['show_customer_email'] ) ) {
                        update_option( 'mcisoe_show_customer_email', '1' );
                    } else {
                        update_option( 'mcisoe_show_customer_email', '0' );
                    }
                    // Show customer phone
                    if ( isset( $_POST['show_customer_phone'] ) ) {
                        update_option( 'mcisoe_show_customer_phone', '1' );
                    } else {
                        update_option( 'mcisoe_show_customer_phone', '0' );
                    }
                    // Show shortdesc
                    if ( isset( $_POST['show_shortdesc'] ) ) {
                        update_option( 'mcisoe_show_shortdesc', '1' );
                    } else {
                        update_option( 'mcisoe_show_shortdesc', '0' );
                    }
                    // Show EAN
                    if ( isset( $_POST['show_ean'] ) ) {
                        update_option( 'mcisoe_show_ean', '1' );
                    } else {
                        update_option( 'mcisoe_show_ean', '0' );
                    }
                    // Show notes
                    if ( isset( $_POST['show_notes'] ) ) {
                        update_option( 'mcisoe_show_notes', '1' );
                    } else {
                        update_option( 'mcisoe_show_notes', '0' );
                    }
                    // Show order total
                    if ( isset( $_POST['show_order_total'] ) ) {
                        update_option( 'mcisoe_show_order_total', '1' );
                    } else {
                        update_option( 'mcisoe_show_order_total', '0' );
                    }
                    // Show payment method
                    if ( isset( $_POST['show_payment_method'] ) ) {
                        update_option( 'mcisoe_show_payment_method', '1' );
                    } else {
                        update_option( 'mcisoe_show_payment_method', '0' );
                    }
                    // Show shipping method
                    if ( isset( $_POST['show_shipping_method'] ) ) {
                        update_option( 'mcisoe_show_shipping_method', '1' );
                    } else {
                        update_option( 'mcisoe_show_shipping_method', '0' );
                    }
                    // Show price items
                    if ( isset( $_POST['show_price_items'] ) ) {
                        update_option( 'mcisoe_show_price_items', '1' );
                    } else {
                        update_option( 'mcisoe_show_price_items', '0' );
                    }
                    // Show order number
                    if ( isset( $_POST['show_order_number'] ) ) {
                        update_option( 'mcisoe_show_order_number', '1' );
                    } else {
                        update_option( 'mcisoe_show_order_number', '0' );
                    }
                    // Show product attributes
                    if ( isset( $_POST['show_product_attributes'] ) ) {
                        update_option( 'mcisoe_show_product_attributes', '1' );
                    } else {
                        update_option( 'mcisoe_show_product_attributes', '0' );
                    }
                    // Show product meta
                    if ( isset( $_POST['show_product_meta'] ) ) {
                        update_option( 'mcisoe_show_product_meta', '1' );
                    } else {
                        update_option( 'mcisoe_show_product_meta', '0' );
                    }
                    // Show cost prices
                    if ( is_plugin_active( 'woocommerce-cost-of-goods/woocommerce-cost-of-goods.php' ) ) {
                        if ( isset( $_POST['show_cost_prices'] ) ) {
                            update_option( 'mcisoe_show_cost_prices', '1' );
                        } else {
                            update_option( 'mcisoe_show_cost_prices', '0' );
                        }
                        if ( isset( $_POST['show_cost_total'] ) ) {
                            update_option( 'mcisoe_show_cost_total', '1' );
                        } else {
                            update_option( 'mcisoe_show_cost_total', '0' );
                        }

                    }
                } // End if premium

                // END PREMIUM OPTIONS /////////////////////////////////////////////////////////////////
            } //End nonce
        } //End submit
    }

    public function deactivate_premium()
    {
        // Deactivate premium license if press button deactivate_license
        if (  ( isset( $_POST['mcisoe_nonce_field'] ) && wp_verify_nonce( $_POST['mcisoe_nonce_field'], 'mcisoe_nonce_field' ) ) &&
            $this->options->auth_premium && isset( $_POST['mcisoe_deactivate'] ) ) {

            require_once MCISOE_PLUGIN_DIR . 'includes/check_premium/mcisoe_check_lemon.php';

            $license_lemon = new CheckLicenseLemonMci();
            $license_lemon->deactivate();
            update_option( 'mcisoe_auth_lemon', '0' );
        }
    }

    public function init()
    {
        $this->save_panel_data();
        $this->deactivate_premium();
    }

} // End class