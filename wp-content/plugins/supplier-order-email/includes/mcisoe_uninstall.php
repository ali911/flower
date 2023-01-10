<?php
if ( !defined( 'ABSPATH' ) ) {
    exit;
}

class McisoeUninstall
{
    public function delete_options()
    {
        delete_option( 'mcisoe_version' );
        delete_option( 'mcisoe_email_intro' );
        delete_option( 'mcisoe_email_subject' );
        delete_option( 'mcisoe_header_color' );
        delete_option( 'mcisoe_store_logo' );
        delete_option( 'mcisoe_hide_customer' );
        delete_option( 'mcisoe_replace_address' );
        delete_option( 'mcisoe_select_email_admin' );
        delete_option( 'mcisoe_show_customer_email' );
        delete_option( 'mcisoe_show_customer_phone' );
        delete_option( 'mcisoe_show_ean' );
        delete_option( 'mcisoe_show_notes' );
        delete_option( 'mcisoe_show_order_number' );
        delete_option( 'mcisoe_show_order_total' );
        delete_option( 'mcisoe_show_price_items' );
        delete_option( 'mcisoe_show_product_attributes' );
        delete_option( 'mcisoe_show_product_meta' );
        delete_option( 'mcisoe_version' );
        delete_option( 'mci_api_key' );
        delete_option( 'mcisoe_manual_auth' );

        delete_option( 'mcisoe_auth_premium' );
        delete_option( 'mcisoe_auth_lemon' );
        delete_option( 'mcisoe_auth_mciapi' );
        delete_option( 'mcisoe_new_structure_2_0' );
        delete_option( 'mcisoe_delete_all_data' );
        delete_option( 'mcisoe_show_cost_prices' );
        delete_option( 'mcisoe_show_cost_total' );
    }

    public function delete_supplier_termmeta()
    {

        //Delete termmeta with meta_key 'mcisoe_supplier_email'
        global $wpdb;
        $termmeta_table = $wpdb->prefix . 'termmeta';
        $wpdb->query( "DELETE FROM $termmeta_table WHERE meta_key = 'mcisoe_supplier_email'" );

    }

    public function delete_supplier_terms()
    {
        //Get 'term_id' list of 'term_taxonomy' table on 'taxonomy' = 'supplier'
        global $wpdb;
        $term_taxonomy_table = $wpdb->prefix . 'term_taxonomy';
        $term_id_list        = $wpdb->get_col( "SELECT term_id FROM $term_taxonomy_table WHERE taxonomy = 'supplier'" );

        //Delete items of table 'terms' on 'term_id' in 'term_id_list' and 'taxonomy' = 'supplier'
        $terms_table = $wpdb->prefix . 'terms';
        $wpdb->query( "DELETE FROM $terms_table WHERE term_id IN (" . implode( ',', $term_id_list ) . ")" );

    }

    public function delete_term_taxonomy()
    {
        global $wpdb;
        $term_taxonomy_table = $wpdb->prefix . 'term_taxonomy';
        $wpdb->query( "DELETE FROM $term_taxonomy_table WHERE taxonomy = 'supplier'" );
    }

    public function init()
    {
        if ( is_admin() && current_user_can( 'activate_plugins' ) ) {

            if ( get_option( 'mcisoe_delete_all_data' ) ) {
                $this->delete_options();
                $this->delete_supplier_termmeta();
                $this->delete_supplier_terms();
                $this->delete_term_taxonomy();
            }
        }
    }

} //end class