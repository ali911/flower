<?php
/*
This table_header template can be overridden in a WordPress child theme.
Copy this file (mcisoe_table_header.php) and duplicate it into "child_theme/supplier-order-email/mcisoe_table_header.php" to modify the table_header layout.
 */
if ( !defined( 'ABSPATH' ) ) {exit;}

class McisoeTableHeader
{
    private $table_header;

    public function __construct( $options )
    {

        // Table header
        $items_template = '<table style="width:100%;border-collapse:collapse;border-spacing:0;border:1px solid #d9d9d9;margin-bottom:20px;margin-top:20px;">';
        $items_template .= '<thead><tr>';
        $items_template .= '<th style="border:1px solid #ccc;padding:12px;text-align:center;font-weight:normal;color:#ffffff;background:' . esc_attr( $options->header_color ) . '">' . __( 'Quantity', 'supplier-order-email' ) . '</th>'; //Quantity
        $items_template .= '<th style="border:1px solid #ccc;padding:12px;text-align:left;font-weight:normal;color:#ffffff;background:' . esc_attr( $options->header_color ) . '">' . __( 'Reference', 'supplier-order-email' ) . '</th>'; //SKU
        $items_template .= '<th style="border:1px solid #ccc;padding:12px;text-align:left;font-weight:normal;color:#ffffff;background:' . esc_attr( $options->header_color ) . '">' . __( 'Product', 'supplier-order-email' ) . '</th>'; //Name
        ///// START PREMIUM ///////////////////////////
        if ( $options->show_ean == '1' ) {
            $items_template .= '<th style="border:1px solid #ccc;padding:12px;text-align:left;font-weight:normal;color:#ffffff;background:' . esc_attr( $options->header_color ) . '">' . __( 'EAN', 'supplier-order-email' ) . '</th>'; //EAN
        }
        if ( is_plugin_active( 'woocommerce-cost-of-goods/woocommerce-cost-of-goods.php' ) && $options->show_cost_prices == '1' ) {
            $items_template .= '<th style="border:1px solid #ccc;padding:12px;text-align:center;font-weight:normal;color:#ffffff;background:' . esc_attr( $options->header_color ) . '">' . __( 'Cost', 'supplier-order-email' ) . '</th>'; //Cost price
        }
        if ( $options->show_price_items == '1' ) {
            $items_template .= '<th style="border:1px solid #ccc;padding:12px;text-align:center;font-weight:normal;color:#ffffff;background:' . esc_attr( $options->header_color ) . '">' . __( 'Price', 'supplier-order-email' ) . '</th>'; //Price
        }
        ///// END PREMIUM /////////////////////////////

        $items_template .= '</tr></thead>';

        $this->table_header = $items_template;
    }

    public function get()
    {
        return $this->table_header;
    }

}