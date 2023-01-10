<?php
if ( !defined( 'ABSPATH' ) ) {
    exit;
}

class MciCheckApi
{
    private $mci_pay_email;
    private $mci_pay_code_key;
    private $mci_plugin_alias;

    public function __construct()
    {
        $this->mci_pay_email    = get_option( 'mci_pay_email' );
        $this->mci_pay_code_key = get_option( 'mci_pay_code_key' );
        $this->mci_plugin_alias = 'soe';
    }

    ////////////////////////////////////////////////////////////////////
    //Make request to create user or add abilities if already exists
    ////////////////////////////////////////////////////////////////////
    public function convert_pay_to_user()
    {
        $check_license_lemon = new CheckLicenseLemonMci;

        if ( isset( $_POST['mci_pay_email'] ) && $_POST['mci_pay_email'] != '' && isset( $_POST['mci_code_key'] ) ) {
            $this->mci_pay_email    = sanitize_email( $_POST['mci_pay_email'] );
            $this->mci_pay_code_key = sanitize_text_field( $_POST['mci_code_key'] );
        }
        //$code_key_premium_sequre = password_hash($code_key_premium, PASSWORD_BCRYPT);

        //Check if data exists in DB and save or update value
        update_option( 'mci_pay_email', $this->mci_pay_email );
        update_option( 'mci_pay_code_key', $this->mci_pay_code_key );

        //Check if email already exists in api
        $url_email_exists  = "https://api.mci-desarrollo.es/api/duplicate_email";
        $args_email_exists = [
            "headers" => [
                //"Content-Type" => "application/json",
                "Accept" => "application/json",
            ],
            "body"    => [
                "email"       => $this->mci_pay_email,
                "mci_api_key" => MCI_API_KEY,
            ],
        ];
        $response_email_exists = wp_remote_post( $url_email_exists, $args_email_exists );
        $retrieve_email_exists = wp_remote_retrieve_body( $response_email_exists );

        //Generate login password & store in mci_user_pass if email not exists
        if ( $retrieve_email_exists == false ) {
            $mci_user_pass = wp_generate_password( 32 );
            update_option( 'mci_user_pass', $mci_user_pass );
        }

        //Make API post request to create user or convert_pay_to_user
        $url_convert = "https://api.mci-desarrollo.es/api/convert_pay_to_user";
        $urlparts    = parse_url( home_url() );
        $domain      = $urlparts['host'];

        $args_convert = [
            "headers" => [
                //"Content-Type" => "application/json",
                "Accept" => "application/json",
            ],
            "body"    => [
                "mci_api_key" => MCI_API_KEY,
                "code_key"    => $this->mci_pay_code_key,
                "email"       => $this->mci_pay_email,
                "password"    => get_option( 'mci_user_pass' ),
                "domain"      => $domain,
            ],
        ];

        $response      = wp_remote_post( $url_convert, $args_convert );
        $retrieve      = wp_remote_retrieve_body( $response );
        $json          = json_decode( $retrieve );
        $retrieve_code = wp_remote_retrieve_response_code( $response );

        //Return premium abilities or null
        if (  ( $retrieve_code == 200 && $json->access_token !== null ) ) {

            update_option( 'mci_auth_premium', $json->abilities );
            update_option( 'mci' . $this->mci_plugin_alias . '_auth_premium', true );

            return $json->abilities;
        } else {

            update_option( 'mci' . $this->mci_plugin_alias . '_auth_premium', null );

            return null;
        }
    }

    //Check if a user is abilitie for use in this domain and for this plugin
    //Returns true or false
    public function mci_verify()
    {
        $urlparts = parse_url( home_url() );
        $domain   = $urlparts['host'];

        $url = "https://api.mci-desarrollo.es/api/verify_user";

        // Make API post request to verify user
        $args = [
            "headers" => [
                "Accept" => "application/json",
            ],
            "body"    => [
                "mci_api_key"  => MCI_API_KEY,
                "email"        => $this->mci_pay_email,
                "domain"       => $domain,
                "plugin_alias" => $this->mci_plugin_alias,
            ],
        ];

        $response_mci_verify      = wp_remote_post( $url, $args );
        $retrieve_body_mci_verify = wp_remote_retrieve_body( $response_mci_verify );

        if ( $retrieve_body_mci_verify == true ) {
            return true;
        } else {
            return false;
        }
    }
}