<?php
if ( !defined( 'ABSPATH' ) ) {
    exit;
}

class McisoeWcImportExport
{
    public function mcisoe_map_colums( $columns )
    {
        // column name
        $columns['supplier'] = __( 'Supplier', 'supplier-order-email' );
        return $columns;
    }

    public function mcisoe_add_column_to_importer( $columns )
    {
        // column slug
        $columns['supplier'] = 'supplier';
        return $columns;
    }

    public function mcisoe_set_taxonomy( $product, $data )
    {
        if ( is_a( $product, 'WC_Product' ) ) {

            if ( !empty( $data['supplier'] ) ) {

                // Get the supplier taxonomy term_id
                $term_id = (int) $data['supplier'];

                // Set the supplier taxonomy
                wp_set_object_terms( $product->get_id(), $term_id, 'supplier' );
            }
        }
        return $product;
    }

    //////////////////////////////////////////////////////////

    public function mcisoe_export_add_columns( $columns )
    {
        $columns['supplier'] = __( 'Supplier', 'supplier-order-email' );
        return $columns;
    }

    public function mcisoe_export_fill_taxonomy_column( $value, $product )
    {
        $product_id = $product->get_ID();

        $taxonomy = $this->select_yoast_primary_supplier( $product_id );

        return $taxonomy;
    }

    private function select_yoast_primary_supplier( $product_id )
    {
        $taxonomy = 'supplier';

        // Get supplier primary taxonomy if exists (Yoast SEO)
        if ( !function_exists( 'is_plugin_active' ) ) {
            include_once ABSPATH . 'wp-admin/includes/plugin.php';
        }
        if ( is_plugin_active( 'wordpress-seo/wp-seo.php' ) ) {
            $primary_cat_id = yoast_get_primary_term_id( $taxonomy, $product_id );
        } else {
            $primary_cat_id = false;
        }
        if ( isset( $primary_cat_id ) && is_plugin_active( 'wordpress-seo/wp-seo.php' ) && $primary_cat_id !== false && $primary_cat_id !== "" ) {

            $primary_cat      = get_term( $primary_cat_id, $taxonomy );
            $primary_cat_name = $primary_cat->term_id;
            $supplier         = $primary_cat_name;

        } else {
            //If Yoast is inactive or not has primary category
            $product_taxonomy_terms = get_the_terms( $product_id, 'supplier' );
            $first_cat_id           = $product_taxonomy_terms != false ? $product_taxonomy_terms[0]->term_id : '';
            $supplier               = $first_cat_id;
        }
        return $supplier;
    }

    public function init()
    {
        // require mcisoe_get_data.php
        require_once MCISOE_PLUGIN_DIR . 'data/mcisoe_get_data.php';
        $options = new McisoeGetData();

        if ( $options->auth_premium ) {
            add_filter( 'woocommerce_csv_product_import_mapping_options', [$this, 'mcisoe_map_colums'] );
            add_filter( 'woocommerce_csv_product_import_mapping_default_columns', [$this, 'mcisoe_add_column_to_importer'] );
            add_filter( 'woocommerce_product_import_inserted_product_object', [$this, 'mcisoe_set_taxonomy'], 10, 2 );

            add_filter( 'woocommerce_product_export_column_names', [$this, 'mcisoe_export_add_columns'] );
            add_filter( 'woocommerce_product_export_product_default_columns', [$this, 'mcisoe_export_add_columns'] );
            add_filter( 'woocommerce_product_export_product_column_supplier', [$this, 'mcisoe_export_fill_taxonomy_column'], 10, 2 );
        }
    }

} // End class