<?php
if ( !defined( 'ABSPATH' ) ) {exit;}

class McisoeFooter
{
    public $helpers;
    public $footer;
    private $options;
    private $order;

    public function __construct( $options, $order )
    {
        require_once MCISOE_PLUGIN_DIR . 'helpers/mcisoe_helpers.php';
        $this->helpers = new McisoeHelpers;
        $this->footer  = '';
        $this->options = $options;
        $this->order   = $order;
    }

    public function get_footer()
    {
        $order_id = $this->order->get_id();
        if ( !$order_id ) {
            return;
        }

        $site_name = sanitize_text_field( get_bloginfo( 'name' ) );

        //Print table content from template. Select file in child theme
        require_once $this->helpers->search_in_child_theme( 'mcisoe_email_footer.php', $this->options->auth_premium );
        $email_footer = new MciSoeEmailFooter( $site_name, $this->options->auth_premium );
        $this->footer = $email_footer->get();

        return $this->footer;
    }
}