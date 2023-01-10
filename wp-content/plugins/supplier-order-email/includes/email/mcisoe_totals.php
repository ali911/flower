<?php
if ( !defined( 'ABSPATH' ) ) {exit;}

class McisoeTotals
{
    private $supplier_total;
    private $options;
    private $cost_total;
    private $order;
    private $payment_method;
    private $shipping_method;
    private $helpers;

    public function __construct( $supplier_total, $options, $cost_total, $order )
    {
        $this->supplier_total = sanitize_text_field( $supplier_total );
        $this->cost_total     = sanitize_text_field( $cost_total );
        $this->options        = $options;
        $this->order          = $order;

        require_once MCISOE_PLUGIN_DIR . 'helpers/mcisoe_helpers.php';
        $this->helpers = new McisoeHelpers;
    }

    public function supplier_total()
    {
        if ( $this->options->show_order_total == '1' && $this->supplier_total > 0 ) {
            $label_order_total = __( 'Total', 'supplier-order-email' );
            $label_order_total .= "\n";
            $order_total          = $this->helpers->build_price_currency( $this->supplier_total );
            $this->supplier_total = $label_order_total . $order_total;
        } else {
            $this->supplier_total = '';
        }
    }

    public function cost_total()
    {
        if ( is_plugin_active( 'woocommerce-cost-of-goods/woocommerce-cost-of-goods.php' ) && $this->cost_total > 0 && $this->options->show_cost_total == '1' ) {
            $label_cost_total = __( 'Cost', 'supplier-order-email' );
            $label_cost_total .= "<br>";
            $cost_total       = $this->helpers->build_price_currency( $this->cost_total );
            $this->cost_total = $label_cost_total . $cost_total;
        } else {
            $this->cost_total = '';
        }
    }

    private function payment_method()
    {
        $payment_method = sanitize_text_field( $this->order->get_payment_method_title() );

        if ( $this->options->show_payment_method == '1' && $payment_method != '' ) {

            $label_payment_method = __( 'Payment method', 'supplier-order-email' );
            $this->payment_method = $label_payment_method . ': ' . esc_html( $payment_method );
        } else {
            $this->payment_method = '';
        }
    }

    private function shipping_method()
    {
        $shipping_method = sanitize_text_field( $this->order->get_shipping_method() );

        if ( $this->options->show_shipping_method == '1' && $shipping_method != '' ) {

            $label_shipping_method = __( 'Shipping method', 'supplier-order-email' );
            $this->shipping_method = $label_shipping_method . ': ' . esc_html( $shipping_method );

        } else {
            $this->shipping_method = '';
        }
    }

    public function get_totals()
    {

        $this->supplier_total();
        $this->cost_total();
        $this->payment_method();
        $this->shipping_method();

        //Print totals from template. Select file in child theme
        require_once $this->helpers->search_in_child_theme( 'mcisoe_email_totals.php', $this->options->auth_premium );
        $supplier_totals = new MciSoeEmailTotals( $this->supplier_total, $this->options, $this->cost_total, $this->payment_method, $this->shipping_method );

        $this->supplier_total = $supplier_totals->get() . "</tbody></table>";

        return $this->supplier_total;

    }
}