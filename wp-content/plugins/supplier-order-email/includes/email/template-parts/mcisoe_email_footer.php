<?php
/*
This email_footer template can be overridden in a WordPress child theme.
Copy this file (mcisoe_email_footer.php) and duplicate it into "child_theme/supplier-order-email/mcisoe_email_footer.php" to modify the footer layout.
 */
if ( !defined( 'ABSPATH' ) ) {exit;}

class McisoeEmailFooter
{
    public $email_footer;

    public function __construct( $site_name, $auth_premium )
    {
        $email_footer = "<footer style='margin:60px 0 0;padding: 40px 0 34px;text-align:center;background:#f4f8f9;border-bottom: 5px #d7d7d7 solid;'>" . __( 'Thanks', 'supplier-order-email' ) . ', ' . $site_name . "</footer>";
        $email_footer .= "</body>";
        $email_footer .= "</html>";

        $this->email_footer = $email_footer;
    }

    public function get()
    {
        return $this->email_footer;
    }

}