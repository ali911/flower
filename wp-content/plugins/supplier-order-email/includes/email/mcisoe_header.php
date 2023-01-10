<?php
if ( !defined( 'ABSPATH' ) ) {exit;}

class McisoeHeader
{
    public $helpers;
    public $header;
    private $options;
    private $order;
    public $email_subject;
    public $email_intro;
    private $store_logo;
    private $wp_supplier;

    public function __construct( $options, $order, $wp_supplier )
    {
        require_once MCISOE_PLUGIN_DIR . 'helpers/mcisoe_helpers.php';
        $this->helpers       = new McisoeHelpers;
        $this->header        = '';
        $this->options       = $options;
        $this->order         = $order;
        $this->wp_supplier   = $wp_supplier;
        $this->email_subject = $this->options->email_subject;
        $this->email_intro   = $this->options->email_intro;
        $this->filter_email_labels( $this->wp_supplier ); // Filter email for subject and intro
        $this->store_logo = $this->options->store_logo;
    }

    public function get_header()
    {
        $order_id = $this->order->get_id();
        if ( !$order_id ) {
            return;
        }

        //Get data for header
        $header_color  = sanitize_hex_color( $this->options->header_color );
        $site_name     = sanitize_text_field( get_bloginfo( 'name' ) );
        $email_subject = sanitize_text_field( $this->email_subject );
        $email_intro   = sanitize_textarea_field( $this->email_intro );
        $email_intro   = $this->helpers->nl_to_br( $email_intro );

        if ( has_custom_logo() && $this->store_logo == '1' ) {
            $logo                = get_theme_mod( 'custom_logo' );
            $image               = wp_get_attachment_image_src( $logo, 'full' );
            $image_url           = $image[0];
            $logo_original_width = (int) $image[1];
            $store_logo          = "<img src='{$image_url}' alt='{$site_name}'>";
        } else {
            $store_logo          = '';
            $logo_original_width = 0;
        }

        //Print table content from template. Select file in child theme
        require_once $this->helpers->search_in_child_theme( 'mcisoe_email_header.php', $this->options->auth_premium );
        $email_header = new MciSoeEmailHeader( $header_color, $site_name, $email_intro, $this->email_subject, $this->options->auth_premium, $store_logo, $logo_original_width );
        $this->header = $email_header->get();

        return $this->header;
    }

    public function filter_email_labels( $wp_supplier )
    {
        //Get order_date WordPress format
        $wp_date_format = get_option( 'date_format' );
        $order_date     = sanitize_text_field( $this->order->get_date_created()->date( $wp_date_format ) );
        //Get order number
        $order_number = sanitize_text_field( $this->order->get_order_number() );
        //Get supplier_custom_text
        $term_id              = $this->wp_supplier['term_id'];
        $supplier_custom_text = sanitize_textarea_field( get_term_meta( $term_id, 'mcisoe_supplier_custom_text', true ) );

        if ( $this->options->auth_premium == '1' ) {
            $this->email_subject = str_replace( '{order_number}', esc_html( $order_number ), $this->email_subject );
            $this->email_subject = str_replace( '{order_date}', esc_html( $order_date ), $this->email_subject );
            $this->email_subject = str_replace( '{supplier_custom_text}', esc_textarea( $supplier_custom_text ), $this->email_subject );

            $this->email_intro = str_replace( '{order_number}', esc_html( $order_number ), $this->email_intro );
            $this->email_intro = str_replace( '{order_date}', esc_html( $order_date ), $this->email_intro );
            $this->email_intro = str_replace( '{supplier_custom_text}', esc_textarea( $supplier_custom_text ), $this->email_intro );
        }
    }
}