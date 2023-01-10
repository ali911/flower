<?php
if ( !defined( 'ABSPATH' ) ) {
    exit;
}

class McisoeMasterEmail
{
    public function triggers()
    {
        // Trigger on paid orders
        // add_action( 'woocommerce_order_status_on-hold_to_processing_notification', array( $this, 'send_emails' ) );
        // add_action( 'woocommerce_order_status_pending_to_processing_notification', array( $this, 'send_emails' ) );
        // add_action( 'woocommerce_order_status_failed_to_processing_notification', array( $this, 'send_emails' ) );

        
        add_action( 'woocommerce_order_status_on-hold', array( $this, 'send_emails' ) );
        // add_action( 'woocommerce_order_status_processing_to_on-hold_notification', array( $this, 'send_emails' ) );
        // add_action( 'woocommerce_order_status_processing_to_pending_notification', array( $this, 'send_emails' ) );
        // add_action( 'woocommerce_order_status_processing_to_failed_notification', array( $this, 'send_emails' ) );

    }

    public function send_emails( $order_id )
    {
        require_once MCISOE_PLUGIN_DIR . 'data/mcisoe_get_data.php';

        //Get wp_suppliers list
        $options      = new McisoeGetData;
        $wp_suppliers = $options->suppliers;

        // Initzialize the response wp_mail_ok
        $wp_mail_ok = true;

        ////////////////////////////////////////////////////////////////////////////////////////////
        // Send email for each supplier ////////////////////////////////////////////////////////////
        ////////////////////////////////////////////////////////////////////////////////////////////
        foreach ( $wp_suppliers as $wp_supplier ) {

            //Get order data
            $order = wc_get_order( $order_id );

            // Get wp_supplier data
            $wp_supplier_email = sanitize_email( $wp_supplier['email'] );
            $wp_supplier_name  = sanitize_text_field( $wp_supplier['name'] );

            //Get order items
            $items = $order->get_items();

            ///////////////////////////////////////
            //Create header
            require_once MCISOE_PLUGIN_DIR . 'includes/email/mcisoe_header.php';
            $header       = new McisoeHeader( $options, $order, $wp_supplier );
            $email_header = $header->get_header();

            // Create Customer data
            require_once MCISOE_PLUGIN_DIR . 'includes/email/mcisoe_customer_data.php';
            $customer_data = new McisoeCustomerData( $options, $order );
            $email_content = $customer_data->get_customer_data();

            // Create product items list
            require_once MCISOE_PLUGIN_DIR . 'includes/email/mcisoe_items_list.php';
            $items_list     = new McisoeItemsList( $items, $wp_supplier, $options );
            $email_table    = $items_list->items_template;
            $supplier_total = $items_list->order_total;
            $cost_total     = $items_list->cost_total;

            // Create totals
            require_once MCISOE_PLUGIN_DIR . 'includes/email/mcisoe_totals.php';
            $totals       = new McisoeTotals( $supplier_total, $options, $cost_total, $order );
            $email_totals = $totals->get_totals();

            // Create footer
            require_once MCISOE_PLUGIN_DIR . 'includes/email/mcisoe_footer.php';
            $footer       = new McisoeFooter( $options, $order );
            $email_footer = $footer->get_footer();

            // Data for email
            $to        = $wp_supplier_email.','.'orders@flowers-direct.ca';
            $subject   = $header->email_subject; //Get filtered subject
            $message   = $email_header . $email_content . $email_table . $email_totals . $email_footer;
            $headers[] = 'Content-Type: text/html';
            $headers[] = 'MIME-Version: 1.0';
            $headers[] = 'From: ' . sanitize_text_field( get_bloginfo() ) . ' <' . sanitize_text_field( get_option( 'admin_email' ) ) . '>';

            if ( $items_list->match_supplier ) {
                // Send email to supplier
                $response = wp_mail( $to, $subject, $message, $headers );

                if ( !$response ) {
                    $wp_mail_ok = false;
                }

                // Send email to admin
                if ( $options->select_email_admin == '1' ) {

                    $wp_admin_email = sanitize_email( get_option( 'admin_email' ) );
                    $subject_admin  = __( 'Email sent to the supplier', 'supplier-order-email' ) . ': ' . $wp_supplier_name;
                    $intro_admin    = '<p>' . __( 'An order email has been sent to the supplier.', 'supplier-order-email' ) . '</p>';
                    $intro_admin .= '<b style="display:block;margin-bottom:20px;">' . __( 'This is a copy of the email sent to', 'supplier-order-email' ) . ': ' . $wp_supplier_name . ' (' . $wp_supplier_email . ')</b>';
                    $intro_admin .= '<p>----</p>';
                    $message = $intro_admin . $message;

                    $response = wp_mail( $wp_admin_email, $subject_admin, $message, $headers );

                    if ( !$response ) {
                        $wp_mail_ok = false;
                    }
                }
            }

        }
        ////////////////////////////////////////////////////////////////////////////////////////////
        // End send email for each supplier //////////////////////////////////////////////////
        ////////////////////////////////////////////////////////////////////////////////////////////

        return $wp_mail_ok;
    }

    public function init()
    {
        $this->triggers();
    }

} // End class McisoeSendEmails