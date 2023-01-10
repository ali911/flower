<?php
/*
This table_content template can be overridden in a WordPress child theme.
Copy this file (mcisoe_table_content.php) and duplicate it into "child_theme/supplier-order-email/mcisoe_table_content.php" to modify the table_content layout.
 */
if ( !defined( 'ABSPATH' ) ) {exit;}

class McisoeTableContent
{
    private $table_content;

    public function __construct( $options, $product_sku, $product_name, $product_qty, $product_ean, $product_price, $product_attributes, $product_meta, $product_cost_format, $product_shortdesc )
    {

        // Template item //////////////////////////////////////////////////////////////
        $items_template = '
                <tr>
                <td style="border: 1px solid #d9d9d9; padding: 15px; text-align: center; vertical-align: middle; font-family: sans-serif; font-size: 15px; line-height: 20px; color: #555555;white-space:nowrap;">
                <span class="text" style="font-weight:bold;">' . $product_qty . '</span>
                </td>
                <td style="border: 1px solid #d9d9d9; padding: 15px; text-align: left; vertical-align: middle; font-family: sans-serif; font-size: 15px; line-height: 20px; color: #555555;white-space:nowrap;">
                <span class="text" style="font-weight:bold;">' . $product_sku . '</span>
                </td>
                <td style="border: 1px solid #d9d9d9; padding: 15px; text-align: left; vertical-align: middle; font-family: sans-serif; font-size: 15px; line-height: 20px; color: #555555;">
                <span class="text" style="font-weight:bold;">' . $product_name . '</span>';
        ///// START PREMIUM ///////////////////////////
        if ( $options->show_shortdesc == '1' && !empty( $product_shortdesc ) ) {
            $items_template .= '
                <p class="text" style="font-weight:normal;">' . $product_shortdesc . '</p>';
        }
        if ( $options->show_product_attributes == '1' && !empty( $product_attributes ) && !is_array( $product_attributes ) ) {
            $items_template .= '
                    <ul style="margin:7px 0px 0px 0px;">' . $product_attributes . '</ul>';
        }
        if ( $options->show_product_variations == '1' && !empty( $product_variations ) ) {
            $items_template .= '
                    <ul style="margin:7px 0px 0px 0px;">' . $product_variations . '</ul>';
        }
        if ( $options->show_product_meta == '1' && !empty( $product_meta ) ) {
            $items_template .= '
                    <ul style="margin:7px 0px 0px 0px;">' . $product_meta . '</ul>';
        }
        if ( $options->show_ean == '1' ) {
            $items_template .= '<td style="border: 1px solid #d9d9d9; padding: 12px; text-align: left; vertical-align: middle; font-family: sans-serif; font-size: 15px; line-height: 20px; color: #555555;white-space:nowrap;">
                          <span class="text">' . $product_ean . '</span>
                          </td>';
        }
        if ( $options->show_cost_prices == '1' ) {
            $items_template .= '<td style="border: 1px solid #d9d9d9; padding: 12px; text-align: center; vertical-align: middle; font-family: sans-serif; font-size: 15px; line-height: 20px; color: #555555;white-space:nowrap;">
                          <span class="text">' . $product_cost_format . '</span>
                          </td>';
        }
        if ( $options->show_price_items == '1' ) {
            $items_template .= '<td style="border: 1px solid #d9d9d9; padding: 12px; text-align: center; vertical-align: middle; font-family: sans-serif; font-size: 15px; line-height: 20px; color: #555555;white-space:nowrap;">
                          <span class="text">' . $product_price . '</span>
                          </td>';
        }
        ///// END PREMIUM /////////////////////////////

        $items_template .= '</tr>';

        $this->table_content = $items_template;
    }

    public function get()
    {
        return $this->table_content;
    }

}