<?php
if ( !defined( 'ABSPATH' ) ) {exit;}

class McisoeCustomerData
{
    private $helpers;
    public $email_customer_data;
    private $pdf_customer_data;
    private $options;
    private $order;
    private $supplier_id;

    private $order_number;
    private $address;
    private $address_error;
    private $phone;
    private $customer_email;
    private $customer_notes;
    private $delivery_date;
    private $delivery_date_odd_plugin;

    private $supplier_data;

    public function __construct( $options, $order, $supplier_id = null )
    {
        require_once MCISOE_PLUGIN_DIR . 'helpers/mcisoe_helpers.php';
        $this->helpers       = new McisoeHelpers;
        $this->customer_data = '';
        $this->options       = $options;
        $this->order         = $order;
        $this->supplier_id   = $supplier_id;

        $order_id = $this->order->get_id();
        if ( !$order_id ) {
            return;
        }
        $this->order_number = $this->build_order_number( $order_id );

        $response_address    = $this->select_address( $order_id );
        $this->address       = $response_address['address'];
        $this->address_error = $response_address['address_error'];

        $this->phone                    = $this->select_phone( $order_id );
        $this->customer_email           = $this->get_customer_email();
        $this->customer_notes           = $this->get_customer_notes();
        $this->delivery_date            = $this->get_delivery_date( $order_id );
        $this->delivery_date_odd_plugin = $this->get_delivery_date_odd( $order_id );

        $this->supplier_data = $this->get_supplier_data( $this->supplier_id );
    }

    ///////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    // Get customer data
    ///////////////////////////////////////////////////////////////////////////////////////////////////////////////////

    public function email_customer_data()
    {
        //If hide customer
        if ( $this->options->hide_customer == '1' ) {
            $this->customer_data = '';
            return $this->email_customer_data;

        } else {

            //Print customer data from template. Select file in child theme
            require_once $this->helpers->search_in_child_theme( 'mcisoe_email_customer_data.php', 'email', $this->options->auth_premium );
            $email_customer_data = new MciSoeEmailCustomerData( $this->order_number, $this->address_error, $this->address, $this->phone, $this->customer_email, $this->delivery_date, $this->customer_notes, $this->options->auth_premium, $this->order, $this->delivery_date_odd_plugin, $this->supplier_data );

            $this->email_customer_data = $email_customer_data->get();

            return $this->email_customer_data;
        }
    }

    public function pdf_customer_data()
    {
        //If hide customer
        if ( $this->options->hide_customer == '1' ) {
            $this->customer_data = '';
            return $this->pdf_customer_data;

        } else {

            //Print customer data from template. Select file in child theme
            require_once $this->helpers->search_in_child_theme( 'mcisoe_pdf_customer_data.php', 'pdf', $this->options->auth_premium );
            $pdf_customer_data = new MciSoePdfCustomerData( $this->order_number, $this->address_error, $this->address, $this->phone, $this->customer_email, $this->delivery_date, $this->customer_notes, $this->options->auth_premium, $this->order, $this->delivery_date_odd_plugin, $this->supplier_data );

            $this->pdf_customer_data = $pdf_customer_data->get();

            return $this->pdf_customer_data;
        }
    }

    ///////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    ///////////////////////////////////////////////////////////////////////////////////////////////////////////////////

    private function build_order_number( $order_id )
    {
        if ( $this->options->show_order_number == '1' ) {
            $label_order_number = __( 'Order number:', 'supplier-order-email' );
			 $label_order_date = __( 'Shipping Date:', 'supplier-order-email' );
            $label_order_number .= ' ';
            $order_number = $order_id;
            $order_number = sanitize_text_field( $this->order->get_order_number() );
            $order_number = $label_order_number . $order_number;
			

            $get_order = wc_get_order( $order_id );

            $get_shipping_method = $get_order->get_shipping_method();
  
     
            $get_delry_date =  get_post_meta( $order_id, 'ctm_date_picker', true );
      
          if ($get_shipping_method === "Flat rate") {
            $dateTostr = strtotime( $get_delry_date);
            $shipping_date_ = date('M/d/Y', $dateTostr);
            //    $shipping_date_ = $get_delry_date;
          } else {
              $shipping_date = explode("/", $get_delry_date);
              $from_unix_time = mktime(0, 0, 0, $shipping_date[0], $shipping_date[1], $shipping_date[2]);
              $day_before = strtotime("yesterday", $from_unix_time);
              $shipping_date_ = date('M/d/Y', $day_before);
          }
          
            $order_number = $label_order_date . $shipping_date_;
			
        } else {
            $order_number = '';
        }
        return $order_number;
    }

    private function select_address( $order_id )
    {
        $shipping_address = $this->order->get_formatted_shipping_address();
        $billing_address  = $this->order->get_formatted_billing_address();
        $address_error    = false;
        if ( empty( $shipping_address ) && $this->options->replace_address == '1' ) {
            $address = $billing_address;
        } else {
            if ( !empty( $shipping_address ) ) {
                $address = $shipping_address;
            } else {
                $address_error = true;
                $address       = __( 'The shipping address are empty because the order has no address. Contact us to solve it.', 'supplier-order-email' );
            }
        }
        $response = array(
            'address'       => $address,
            'address_error' => $address_error,
        );
        return $response;
    }

    private function select_phone( $order_id )
    {
        if ( $this->options->show_customer_phone == '1' ) {
            $phone_shipping = $this->order->get_shipping_phone();
            $phone_billing  = $this->order->get_billing_phone();
            if ( !empty( $phone_shipping ) && !isset( $address_error ) ) {
                $phone = $phone_shipping;
            } else {
                if ( !empty( $phone_billing ) ) {
                    $phone = $phone_billing;
                } else {
                    $phone = '';
                }
            }
        } else {
            $phone = '';
        }
        return $phone;
    }

    private function get_customer_email()
    {
        if ( $this->options->show_customer_email == '1' ) {
            $customer_email = sanitize_email( $this->order->get_billing_email() );
        } else {
            $customer_email = '';
        }
        return $customer_email;
    }

    private function get_customer_notes()
    {
        if ( $this->options->show_notes == '1' ) {
            $customer_note_label = '<strong>' . __( 'Note:', 'supplier-order-email' ) . '</strong> ';
            $customer_notes      = $customer_note_label . sanitize_text_field( $this->order->get_customer_note() );
        } else {
            $customer_notes = '';
        }
        return $customer_notes;
    }

    private function get_delivery_date( $order_id )
    {
        if ( $this->options->auth_premium == '1' ) {
            $delivery_date = get_post_meta( $order_id, '_delivery_date', true );

            if ( !empty( $delivery_date ) ) {
                $delivery_date_label = '<strong>' . __( 'Delivery date:', 'supplier-order-email' ) . '</strong> ';
                $delivery_date       = $delivery_date_label . sanitize_text_field( $delivery_date );
            } else {
                $delivery_date = '';
            }
        } else {
            $delivery_date = '';
        }
        return $delivery_date;
    }

    private function get_delivery_date_odd( $order_id )
    {
        if (
            $this->options->auth_premium == '1' &&
            in_array( 'order-delivery-date-for-woocommerce/order_delivery_date.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) )
        ) {

            $label_delivery_date = get_option( 'orddd_lite_delivery_date_field_label' );
            $delivery_date_odd   = $label_delivery_date ? get_post_meta( $order_id, $label_delivery_date, true ) : "";

            if ( !empty( $delivery_date_odd ) ) {
                $delivery_date_odd_label = '<strong>' . __( 'Delivery date:', 'supplier-order-email' ) . '</strong> ';
                $delivery_date_odd       = $delivery_date_odd_label . sanitize_text_field( $delivery_date_odd );
            } else {
                $delivery_date_odd = '';
            }
        } else {
            $delivery_date_odd = '';
        }

        return $delivery_date_odd;
    }

    public function get_supplier_data( $supplier_id )
    {
        //Get term name from taxonomy supplier
        $supplier_name        = get_term( $supplier_id, 'supplier' )->name;
        $supplier_email       = get_term_meta( $supplier_id, 'mcisoe_supplier_email', true );
        $supplier_custom_text = sanitize_textarea_field( get_term_meta( $supplier_id, 'mcisoe_supplier_custom_text', true ) );
        $supplier_custom_text = $this->helpers->nl_to_br( $supplier_custom_text );
        $supplier_data_text   = sanitize_textarea_field( get_term_meta( $supplier_id, 'mcisoe_supplier_data_text', true ) );
        $supplier_data_text   = $this->helpers->nl_to_br( $supplier_data_text );

        //Build array with supplier data
        $supplier_data = array(
            'name'        => sanitize_text_field( $supplier_name ),
            'email'       => sanitize_email( $supplier_email ),
            'custom_text' => $supplier_custom_text,
            'data_text'   => $supplier_data_text,
        );
        return $supplier_data;
    }

}