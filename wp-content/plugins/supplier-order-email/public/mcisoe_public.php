<?php
if ( !defined( 'ABSPATH' ) ) {
    exit;
}

class McisoePublic
{
    public function register_taxonomy()
    {

        require_once MCISOE_PLUGIN_DIR . 'public/partials/mcisoe_wp_taxonomy.php';
        $mcisoe_wp_taxonomy = new McisoeWpTaxonomy();
        $mcisoe_wp_taxonomy->init();

    }

    public function init()
    {
        // if it is admin area
        add_action( "init", [$this, 'register_taxonomy'] );
    }

}