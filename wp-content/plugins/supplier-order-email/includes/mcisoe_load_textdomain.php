<?php
if ( !defined( 'ABSPATH' ) ) {
    exit;
}

class Mcisoe_load_textdomain
{

    public $domain;

    public function __construct()
    {
        $this->domain = 'supplier-order-email';
    }

    public function load_user_languaje()
    {
        // Load User language from wp-content/languages/plugins/*.mo
        load_plugin_textdomain( 'supplier-order-email', false, ABSPATH . 'wp-content/languages' );
    }

    public function load_author_language()
    {
        // Load Author language from wp-content/plugins/supplier-order-email/languages/*.mo
        $locale = apply_filters( 'plugin_locale', determine_locale(), $this->domain );
        $mofile = MCISOE_PLUGIN_DIR . 'languages/supplier-order-email-' . $locale . '.mo';

        load_textdomain( $this->domain, $mofile );
    }

    public function init()
    {
        add_action( 'init', [$this, 'load_user_languaje'], 10 );
        add_action( 'init', [$this, 'load_author_language'], 15 );
    }

}