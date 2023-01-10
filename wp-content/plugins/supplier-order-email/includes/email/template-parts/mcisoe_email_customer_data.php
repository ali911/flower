<?php
/*
This email_customer_data template can be overridden in a WordPress child theme.
Copy this file (mcisoe_email_customer_data.php) and duplicate it into "child_theme/supplier-order-email/mcisoe_email_customer_data.php" to modify the customer_data layout.
 */
if ( !defined( 'ABSPATH' ) ) {exit;}

class MciSoeEmailCustomerData
{
    public $email_customer_data;

    public function __construct( $order_number, $address_error, $address, $phone, $customer_email, $delivery_date, $customer_notes, $auth_premium, $order = null )
    {
        $order_number        = !empty( $order_number ) ? "<p><strong style='margin:15px 0 15px 0;text-align:left;text-transform:uppercase;'> {$order_number} </strong></p>" : "";
        $h2_shipping_address = !$address_error ? "<h2 style='margin:0 0 0px 0;text-align:left;padding-bottom:15px;'>" . __( "Send the order to:", "supplier-order-email" ) . "</h2>" : "";
        $address             = "<p style='margin:0 0 3px;text-align:left;'> {$address} </p>";
        $phone               = !empty( $phone ) ? "<p style='margin:0 0 0 0;text-align:left;'> {$phone} </p>" : "";
        $customer_email      = !empty( $customer_email ) ? "<p style='margin:0;text-align:left;'> {$customer_email} </p>" : "";
        $delivery_date       = !empty( $delivery_date ) ? "<p style='margin:10px 0 0;text-align:left;'> {$delivery_date} </p>" : "";
        $customer_notes      = !empty( $customer_notes ) ? "<p style='margin:10px 0;text-align:left;'> {$customer_notes} </p>" : "";

        $this->email_customer_data = $order_number . $h2_shipping_address . $address . $phone . $customer_email . $delivery_date . $customer_notes;
    }

    public function get()
    {
        return $this->email_customer_data;
    }

}