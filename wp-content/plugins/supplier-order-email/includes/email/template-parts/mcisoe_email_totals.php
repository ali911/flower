<?php
/*
This email_totals template can be overridden in a WordPress child theme.
Copy this file (mcisoe_email_totals.php) and duplicate it into "child_theme/supplier-order-email/mcisoe_email_totals.php" to modify the email_totals layout.
 */
if ( !defined( 'ABSPATH' ) ) {exit;}

class McisoeEmailTotals
{
    private $supplier_total;
    private $options;
    private $cost_total;
    private $payment_method;
    private $shipping_method;

    public function __construct( $supplier_total, $options, $cost_total, $payment_method, $shipping_method )
    {
        $this->supplier_total  = sanitize_text_field( $supplier_total );
        $this->cost_total      = sanitize_text_field( $cost_total );
        $this->options         = $options;
        $this->payment_method  = sanitize_text_field( $payment_method );
        $this->shipping_method = sanitize_text_field( $shipping_method );
    }

    public function table_padding_cells()
    {
        $padding_cells = 2;

        //Calculate padding cells
        if ( $this->options->show_ean == '1' ) {
            $padding_cells++;
        }
        if ( $this->options->show_price_items == '1' ) {
            $padding_cells++;
        }
        if ( is_plugin_active( 'woocommerce-cost-of-goods/woocommerce-cost-of-goods.php' ) && $this->options->show_cost_prices == '1' ) {
            $padding_cells++;
        }
        if ( $this->options->show_order_total == '1' && $this->supplier_total != '' ) {
            $padding_cells--;
        }
        if ( is_plugin_active( 'woocommerce-cost-of-goods/woocommerce-cost-of-goods.php' ) && $this->options->show_cost_total == '1' && $this->cost_total != '' ) {
            $padding_cells--;
        }

        return $padding_cells;
    }

    public function get()
    {
        $padding_cells = $this->table_padding_cells();

        $email_totals = "<tr>";

        //Fill required padding cells
        if (  ( $this->options->show_cost_total == '1' && $this->cost_total != '' ) || ( $this->options->show_order_total == '1' && $this->supplier_total != '' ) ) {
            for ( $i = 0; $i <= $padding_cells; $i++ ) {
                $email_totals .= "<td></td>";
            }
        }

        if ( $this->options->show_cost_total == '1' && $this->cost_total != '' ) {
            $email_totals .= "<td style='border: 1px solid #d9d9d9; padding: 15px; text-align: center; vertical-align: middle; font-family: sans-serif; text-transform:uppercase; font-size: 15px; line-height: 20px; color: #555555;'>";
            $email_totals .= "<span class='text' style='font-weight:bold;white-space:nowrap;'>{$this->cost_total}</span>";
            $email_totals .= "</td>";
        }
        if ( $this->options->show_order_total == '1' && $this->supplier_total != '' ) {
            $email_totals .= "<td style='border: 1px solid #d9d9d9; padding: 15px; text-align: center; vertical-align: middle; font-family: sans-serif; text-transform:uppercase; font-size: 15px; line-height: 20px; color: #555555;'>";
            $email_totals .= "<span class='text' style='font-weight:bold;white-space:nowrap;'>{$this->supplier_total}</span>";
            $email_totals .= "</td>";
        }
        $email_totals .= "</tr>";

        // Create payment & shipping method row
        $total_width_cells = 2 + $padding_cells;

        if ( $this->options->show_payment_method == '1' && $this->payment_method != '' ) {
            $email_totals .= "<tr>";
            $email_totals .= "<td class='text' style='text-align:right;border: 1px solid #d9d9d9; padding: 15px;' colspan='{$total_width_cells}'>{$this->payment_method}</td>";
            $email_totals .= "</tr>";
        }
        if ( $this->options->show_shipping_method == '1' && $this->shipping_method != '' ) {
            $email_totals .= "<tr>";
            $email_totals .= "<td class='text' style='text-align:right;border: 1px solid #d9d9d9; padding: 15px;' colspan='{$total_width_cells}'>{$this->shipping_method}</td>";
            $email_totals .= "</tr>";
        }

        return $email_totals;
    }

}