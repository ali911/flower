<?php

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) {
    exit;
}

class MciOldAuth
{
    private $mci_correct_pass;
    private $admin_email;

    //Get pass & verify
    public function __construct()
    {
        $this->mci_correct_pass = false;
        $this->admin_email      = get_option( 'admin_email' );

        // Get password of database
        global $wpdb;
        $tabla_mcisoe_options         = sanitize_text_field( $wpdb->prefix ) . 'mcisoe_options';
        $get_pass_premium_form_sequre = $wpdb->get_results( "SELECT value_mci FROM " . esc_sql( $tabla_mcisoe_options ) . " WHERE option_mci = 'pass'" );

        if ( !empty( $get_pass_premium_form_sequre ) ) {
            $get_pass_premium_form_sequre = $get_pass_premium_form_sequre[0]->value_mci;

            //Compare password
            $old_pass     = "g756eyTRk51dJ?&k_)m876";
            $correct_pass = password_verify( $old_pass, $get_pass_premium_form_sequre );
        }

        if ( isset( $correct_pass ) && $correct_pass ) {
            $this->mci_correct_pass = $old_pass;
        }
    }

    //Create new user
    public function create_user_from_old()
    {
        //Create user if are old_pass and not are mcisoe_manual_auth
        if ( $this->mci_correct_pass == "g756eyTRk51dJ?&k_)m876" && get_option( 'mcisoe_manual_auth' ) == false ) {

            $urlparts      = parse_url( home_url() );
            $domain        = $urlparts['host'];
            $mci_user_pass = wp_generate_password( 32 );

            $url  = "https://api.mci-desarrollo.es/api/create_user_from_old";
            $args = [
                "headers" => [
                    //"content-type" => "application/json",
                    "Accept" => "application/json",
                ],
                "body"    => [
                    "admin_email"   => $this->admin_email,
                    "payment_email" => $this->admin_email,
                    "code_key"      => $this->mci_correct_pass,
                    "mci_api_key"   => MCI_API_KEY,
                    "password"      => $mci_user_pass,
                    "domain"        => $domain,
                ],
            ];

            $response = wp_remote_post( $url, $args );
            $retrieve = wp_remote_retrieve_body( $response );

            //Check if data exists in DB and save or update value
            update_option( 'mci_pay_email', $this->admin_email );
            update_option( 'mci_pay_code_key', $this->mci_correct_pass );
            update_option( 'mci_user_pass', $mci_user_pass );
            update_option( 'mci_auth_premium', $retrieve );
            update_option( 'mcisoe_auth_premium', true );
            update_option( 'mcisoe_manual_auth', true );

            return $retrieve;
        }

    }

}