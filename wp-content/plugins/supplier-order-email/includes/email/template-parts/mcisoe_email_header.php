<?php
/*
This email_header template can be overridden in a WordPress child theme.
Copy this file (mcisoe_email_header.php) and duplicate it into "child_theme/supplier-order-email/mcisoe_email_header.php" to modify the header layout.
 */
if ( !defined( 'ABSPATH' ) ) {exit;}

class McisoeEmailHeader
{
    public $email_header;

    public function __construct( $header_color, $site_name, $email_intro, $email_subject, $auth_premium, $store_logo, $logo_original_width )
    {

        $min_width = $logo_original_width >= 190 ? '190px' : $logo_original_width . 'px';
        $lang      = get_bloginfo( 'language' );

        $head = "<!DOCTYPE html>
                <html lang='{$lang}'>
                <head>
                    <meta charset='utf-8'>
                    <meta name='viewport' content='width=device-width,initial-scale=1'>
                    <title>{$site_name} - {$email_subject}</title>
                    <style>
                        body {
                            font-family: Roboto, Arial, sans-serif;
                        }
                        li p{margin-top:0px;margin-bottom:7px;}
                        .mcisoe_email_logo img{
                            min-width:" . $min_width . ";
                            max-width:300px;
                            height:auto;
                            text-align:center;
                            display:block;
                            margin:15px auto 0px;
                        }
                        @media only screen and (min-width: 2200px) {
                            .mcisoe_email_logo img{
                                margin: 15px 0px 0px;
                            }
                        }
                    </style>
                </head>
                <body>
                ";
        if ( $store_logo == '' ) {
            $header_bar = "<h1 style='margin:0 0 10px 0;background:{$header_color};text-align:center;color:#ffffff;padding:20px;'>{$site_name}</h1>";
        } else {
            $header_bar = "<div class='mcisoe_email_logo'>{$store_logo}</div>";

        }

        $email_intro = !empty( $email_intro ) ? "<p style='margin:35px 0 30px 0;text-align:left;'>{$email_intro}</p>" : "";

        $this->email_header = $head . $header_bar . $email_intro;

    }

    public function get()
    {
        return $this->email_header;
    }

}