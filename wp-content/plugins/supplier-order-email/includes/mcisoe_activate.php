<?php
if ( !defined( 'ABSPATH' ) ) {exit;}

class McisoeActivate
{

    public function activate()
    {
        // If no flag & if plugin is already installed, change database to new structure
        if ( get_option( 'mcisoe_new_structure_2_0' ) != '1' && get_option( 'mcisoe_version' ) != false ) {

            require_once MCISOE_PLUGIN_DIR . 'includes/mcisoe_convert_old_db.php';
            $this->mcisoe_convert_old_db = new McisoeConvertOldDb;
            $this->mcisoe_convert_old_db->convert_old_db_suppliers();
            $this->mcisoe_convert_old_db->convert_old_db_options();

            // Set flag to indicates version >= 2.0
            update_option( 'mcisoe_new_structure_2_0', '1' );
        }

        // Create default option values if not set
        $this->create_default_options();

        update_option( 'mcisoe_version', MCISOE_VERSION );
        flush_rewrite_rules();
    }

    public function default_options()
    {
        $options = [
            'mcisoe_email_intro'             => 'Hello, we need you to send this order directly to the end customer without valued documents. Thank you.',
            'mcisoe_email_subject'           => 'New order to send',
            'mcisoe_header_color'            => MCISOE_HEADER_COLOR,
            'mcisoe_store_logo'              => '0',
            'mcisoe_hide_customer'           => '0',
            'mcisoe_replace_address'         => '0',
            'mcisoe_select_email_admin'      => '1',
            'mcisoe_show_customer_email'     => '0',
            'mcisoe_show_customer_phone'     => '0',
            'mcisoe_show_ean'                => '0',
            'mcisoe_show_notes'              => '0',
            'mcisoe_show_order_number'       => '0',
            'mcisoe_show_order_total'        => '0',
            'mcisoe_show_payment_method'     => '0',
            'mcisoe_show_shipping_method'    => '0',
            'mcisoe_show_price_items'        => '0',
            'mcisoe_show_product_attributes' => '1',
            'mcisoe_show_product_meta'       => '1',
            'mcisoe_delete_all_data'         => '0',
            'mcisoe_show_cost_prices'        => '0',
            'mcisoe_show_cost_total'         => '0',
            'mcisoe_show_shortdesc'          => '0',
            'mcisoe_new_structure_2_0'       => '1', //Create flag to avoid convertion of old database in news installations
        ];
        return $options;
    }

    public function create_default_options()
    {
        $options = $this->default_options();

        foreach ( $options as $option => $value ) {

            $option_exists = get_option( $option );
            if ( !$option_exists ) {
                add_option( $option, $value );
            }
        }
    }

}