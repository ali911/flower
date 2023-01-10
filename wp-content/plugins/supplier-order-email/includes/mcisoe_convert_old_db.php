<?php
if ( !defined( 'ABSPATH' ) ) {exit;}

class McisoeConvertOldDb
{

    public function convert_old_db_options()
    {
        if ( $this->get_old_options() != null ) {

            // Get the option name and value
            $old_lines = $this->get_old_options();

            foreach ( $old_lines as $old_line ) {
                $option_name  = sanitize_text_field( $old_line->option_mci );
                $option_value = sanitize_text_field( $old_line->value_mci );

                // Update the option to the new DB system
                update_option( 'mcisoe_' . $option_name, $option_value );
            }

            // Add notice to admin
            add_action( 'admin_notices', function () {
                echo '<div class="success notice notice-success is-dismissible"><p>' . __( '"Supplier Order Email" plugin options have been successfully converted to the latest database version 2.0', 'supplier-order-email' ) . '</p></div>';
            } );

        } else {
            // Add notice to admin
            add_action( 'admin_notices', function () {
                echo '<div class="notice notice-warning is-dismissible"><p>' . __( 'The old options from the "Supplier Order Email" plugin could not be imported into the new database format because the mcisoe_options table does not exist or is empty. Default options have been activated. Please check the plugin configuration options.', 'supplier-order-email' ) . '</p></div>';
            } );
        }

        // Delete old options table
        global $wpdb;
        $tabla_mcisoe_options = sanitize_text_field( $wpdb->prefix ) . 'mcisoe_options';
        $wpdb->query( "DROP TABLE IF EXISTS " . esc_sql( $tabla_mcisoe_options ) );
    }

    public function convert_old_db_suppliers()
    {
        global $wpdb;
        //Get table old suppliers of database
        $old_lines = $this->get_old_suppliers();

        if ( isset( $old_lines ) ) {

            //Get table 'terms' of database
            $tabla_terms = sanitize_text_field( $wpdb->prefix ) . 'terms';
            $terms       = $wpdb->get_results( "SELECT * FROM " . esc_sql( $tabla_terms ) );

            foreach ( $old_lines as $old_line ) {

                if ( $old_line->supplier_key == 'select_email_admin' ) {
                    update_option( 'mcisoe_select_email_admin', $old_line->supplier_value );
                }
                if ( $old_line->supplier_key == 'email_subject' ) {
                    update_option( 'mcisoe_email_subject', $old_line->supplier_value );
                }
                if ( $old_line->supplier_key == 'email_intro' ) {
                    update_option( 'mcisoe_email_intro', $old_line->supplier_value );
                }
                if ( $old_line->supplier_key == 'replace_address' ) {
                    update_option( 'mcisoe_replace_address', $old_line->supplier_value );
                }

                $term_id = $this->search_term_by_supplier_name( $terms, $old_line->supplier_name );

                // Detect old_lines of type supplier in old table
                if ( $old_line->supplier_key == null && $old_line->supplier_name != null && $old_line->supplier_email != null && $old_line->supplier_value == null ) {
                    // Create new suppliers

                    // Insert new term in new database
                    add_action( 'init', function () use ( $old_line, $term_id ) {
                        $this->insert_term( $old_line->supplier_name, $old_line->supplier_email, $term_id );
                    } );

                }
            }

            // Add notice to admin
            add_action( 'admin_notices', function () {
                echo '<div class="success notice notice-success is-dismissible"><p>' . __( 'Suppliers and emails of "Supplier Order Email" plugin have been successfully converted to the latest database system version 2.0', 'supplier-order-email' ) . '</p></div>';
            } );

        } else {
            // Add notice to admin
            add_action( 'admin_notices', function () {
                echo '<div class="notice notice-warning is-dismissible"><p>' . __( 'The old suppliers and emails from the "Supplier order email" plugin could not be imported into the new database format because the mcisoe_options table does not exist. Default options have been activated. Please check suppliers.', 'supplier-order-email' ) . '</p></div>';
            } );

        }
        // Delete old suppliers table
        $tabla_mcisoe_suppliers = sanitize_text_field( $wpdb->prefix ) . 'mcisoe_suppliers';
        $wpdb->query( "DROP TABLE IF EXISTS " . esc_sql( $tabla_mcisoe_suppliers ) );
    }

    public function insert_term( $supplier_name, $supplier_email, $term_id )
    {
        if ( $term_id ) {
            // Update the email in the term meta if already exists
            update_term_meta( $term_id, 'mcisoe_supplier_email', sanitize_email( $supplier_email ) );
        } else {

            // Insert supplier_name in the table 'terms' if not exists
            $response_term = wp_insert_term( sanitize_text_field( $supplier_name ), 'supplier' );

            if ( !is_object( $response_term ) && !is_wp_error( $response_term ) ) {
                // Save the email in the term meta
                add_term_meta( $response_term['term_id'], 'mcisoe_supplier_email', sanitize_email( $supplier_email ) );
            }
        }
    }

    public function search_term_by_supplier_name( $terms, $old_name )
    {
        //Search the 'supplier_name' in the table 'terms' of database
        $term_id = false;
        foreach ( $terms as $term ) {

            //Search the $old_line->supplier_name in the table 'terms' of database
            if ( $term->name == $old_name ) {
                $term_id = $term->term_id;
            }
        }
        //Return the term_id or false
        return $term_id;
    }

    public function get_old_options()
    {

        // Get options array from DB // Obtiene el array de opciones de la BD
        global $wpdb;
        $tabla_mcisoe_options = sanitize_text_field( $wpdb->prefix ) . 'mcisoe_options';

        // Check if table exists
        if ( $wpdb->get_var( "SHOW TABLES LIKE '$tabla_mcisoe_options'" ) == $tabla_mcisoe_options ) {

            // Get the options
            $options = $wpdb->get_results( "SELECT * FROM " . esc_sql( $tabla_mcisoe_options ) );

            return $options;

        } else {

            return null;
        }
    }

    public function get_old_suppliers()
    {
        // Get supppliers array from DB // Obtiene el array de proveedores de la BD
        global $wpdb;
        $tabla_mcisoe_suppliers = sanitize_text_field( $wpdb->prefix ) . 'mcisoe_suppliers';

        // Check if table exists
        if ( $wpdb->get_var( "SHOW TABLES LIKE '$tabla_mcisoe_suppliers'" ) == $tabla_mcisoe_suppliers ) {

            // Get the suppliers
            $suppliers = $wpdb->get_results( "SELECT * FROM " . esc_sql( $tabla_mcisoe_suppliers ) );

            return $suppliers;

        } else {

            return null;
        }
    }

} //End class