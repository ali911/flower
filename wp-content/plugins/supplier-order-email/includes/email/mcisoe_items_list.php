<?php
if ( !defined( 'ABSPATH' ) ) {exit;}

class MciSoeItemsList
{
    private $items;
    private $wp_supplier;
    private $options;

    public $order_total;
    public $cost_total;
    public $items_template;
    public $match_supplier;
    public $helpers;

    public function __construct( $items, $wp_supplier, $options )
    {
        require_once MCISOE_PLUGIN_DIR . 'helpers/mcisoe_helpers.php';
        $this->helpers        = new McisoeHelpers;
        $this->items          = $items;
        $this->wp_supplier    = $wp_supplier;
        $this->options        = $options;
        $this->order_total    = 0;
        $this->cost_total     = 0;
        $this->match_supplier = false;
        $this->items_template = '';
        $this->create_items_list();

    }

    ///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    ////// Create items list //////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    ///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

    public function create_items_list()
    {
        $items_template = '';

        //Print table header from template. Select file in child theme
        require_once $this->helpers->search_in_child_theme( 'mcisoe_table_header.php', $this->options->auth_premium );
        $table_header = new MciSoeTableHeader( $this->options );
        $items_template .= $table_header->get();

        $items_template .= '<tbody>';
        foreach ( $this->items as $item ) {

            // Get supplier primary taxonomy if exists (Yoast SEO)
            $item_supplier = $this->select_yoast_parent_supplier( $item );

            // Check if the supplier is the same as the supplier of the product
            if ( $item_supplier == (int) $this->wp_supplier['term_id'] ) {

                $this->match_supplier = true; // Mark the supplier as matched for send email

                $product_complete = $item->get_product();

                $product_qty = sanitize_text_field( $item['quantity'] ). ' '.get_post_meta($item['product_id'], 'stem-or-bunch', true);

                $product_sku      = !empty( $product_complete->get_sku() ) ? sanitize_text_field( $product_complete->get_sku() ) : '';
                $product_name     = !empty( $item['name'] ) ? sanitize_text_field( $item['name'] ) : '';
                ///// START PREMIUM ///////////////////////////
                if ( $this->options->show_shortdesc == '1' ) {
                    $product_shortdesc = !empty( $product_complete->get_short_description() ) ? sanitize_text_field( $product_complete->get_short_description() ) : '';
                } else {
                    $product_shortdesc = '';
                }
                if ( $this->options->show_ean == '1' ) {
                    $product_ean = !empty( $product_complete->get_meta( '_wpm_gtin_code' ) ) ? sanitize_text_field( $product_complete->get_meta( '_wpm_gtin_code' ) ) : '';
                } else {
                    $product_ean = '';
                }
                if ( $this->options->show_price_items == '1' ) {
                    $price_item    = $item['total'] + $item['total_tax'];
                    $product_price = $this->helpers->build_price_currency( $price_item );
                } else {
                    $product_price = '';
                }
                if ( $this->options->show_product_attributes == '1' ) {
                    $product_attributes = !empty( $this->get_product_attributes( $item ) ) ? $this->get_product_attributes( $item ) : '';
                } else {
                    $product_attributes = '';
                }
                if ( $this->options->show_product_meta == '1' ) {
                    $product_meta = $this->get_product_meta_fields( $item );
                } else {
                    $product_meta = '';
                }
                if ( $this->options->show_order_total == '1' ) {
                    $this->order_total += round( $item['total'], 2 ) + round( $item['total_tax'], 2 );
                }

                if ( $this->options->show_cost_total == '1' ) {
                    $product_cost        = !empty( $this->get_line_cost( $item ) ) ? $this->get_line_cost( $item ) : '';
                    $product_cost_format = $this->helpers->build_price_currency( $product_cost );
                    $this->cost_total += (float) $product_cost;
                } else {
                    $product_cost        = '';
                    $product_cost_format = '';
                }

                ///// END PREMIUM /////////////////////////////

                //Print table content from template. Select file in child theme
                require_once $this->helpers->search_in_child_theme( 'mcisoe_table_content.php', $this->options->auth_premium );
                $table_content = new MciSoeTableContent( $this->options, $product_sku, $product_name, $product_qty, $product_ean, $product_price, $product_attributes, $product_meta, $product_cost_format, $product_shortdesc );
                $items_template .= $table_content->get();
            }
        }

        // Set items_template
        $this->items_template = $items_template;
    }

    ///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    ////// End Create items list //////////////////////////////////////////////////////////////////////////////////////////////////////////////
    ///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

    private function select_yoast_parent_supplier( $item )
    {
        $taxonomy = 'supplier';

        // Get supplier primary taxonomy if exists (Yoast SEO)
        if ( !function_exists( 'is_plugin_active' ) ) {
            include_once ABSPATH . 'wp-admin/includes/plugin.php';
        }
        if ( is_plugin_active( 'wordpress-seo/wp-seo.php' ) ) {
            $primary_cat_id = yoast_get_primary_term_id( $taxonomy, $item->get_product_id() );
        } else {
            $primary_cat_id = false;
        }

        if ( isset( $primary_cat_id ) && is_plugin_active( 'wordpress-seo/wp-seo.php' ) && $primary_cat_id !== false && $primary_cat_id !== "" ) {

            $primary_cat      = get_term( $primary_cat_id, $taxonomy );
            $primary_cat_name = $primary_cat->term_id;
            $supplier         = $primary_cat_name;

        } else {
            //If Yoast is inactive or not has primary category
            $item_taxonomy_terms = get_the_terms( $item->get_product_id(), 'supplier' );
            $first_cat_id        = $item_taxonomy_terms != false ? $item_taxonomy_terms[0]->term_id : '';
            $supplier            = $first_cat_id;
        }

        return $supplier;
    }

    private function get_product_attributes( $item )
    {
        // Get Attributes
        $attributes = '';

        $product_complete   = $item->get_product();
        $product_attributes = $product_complete->get_attributes();

        foreach ( $product_attributes as $attribute => $value ) {

            if ( isset( $value ) && is_object( $value ) && !empty( $value ) ) {

                $attributes .= '<li>';

                // Defines attribute label
                $name = sanitize_text_field( $value->get_name() );
                if ( strpos( $name, 'pa_' ) !== false ) {
                    $name = str_replace( 'pa_', '', $name );
                    $name = ucfirst( $name );
                }
                $attributes .= $name . ': ';

                //Defines name of options (terms) of attribute
                $option_names = array();
                foreach ( $value->get_options() as $option ) {

                    if ( is_numeric( $option ) ) {
                        $option_name    = sanitize_text_field( get_term( $option )->name );
                        $option_names[] = sanitize_text_field( $option_name );
                    } else {
                        $option_names[] = sanitize_text_field( $option );
                    }
                }
                //Build list name of options separated by comma
                $attributes .= implode( ', ', $option_names );

                $attributes .= '</li>';
            }

        } //end foreach

        return $attributes;
    }

    private function get_product_meta_fields( $item )
    {
        //Get the product meta and product variations
        $product_id            = $item->get_product_id();
        $product               = wc_get_product( $product_id );
        $product_custom_fields = get_post_custom( $product_id );

        // Get meta_items for product line // Obtener meta_items para la línea de producto
        $product_meta_items = "";
        $meta_items         = $item->get_formatted_meta_data();

        if ( $meta_items && !empty( $meta_items ) ) {

            foreach ( $meta_items as $meta_item ) {

                if ( $meta_item->key !== '_metadate' ) {
                    $product_meta_items .= '<li>';
                    $product_meta_items .= sanitize_text_field( $meta_item->display_key );
                    $product_meta_items .= ': ';
                    $product_meta_items .= sanitize_text_field( $meta_item->display_value );
                }
            }
            $product_meta_items .= '</li>';

        } //end if meta_items
        return $product_meta_items;
    }

    private function get_line_cost( $item )
    {
        $cost = sanitize_text_field( $item->get_meta( '_wc_cog_item_total_cost' ) );
        $cost = str_replace( ',', '.', $cost );
        $cost = (float) $cost;
        $cost = number_format( $cost, 2, '.', '' );

        return $cost;
    }

} //End class