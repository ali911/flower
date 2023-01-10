<?php
if ( !defined( 'ABSPATH' ) ) {
    exit;
}

class McisoeWpMenu
{
    public $options;
    public $suppliers;

    public function __construct()
    {
        $this->options = require_once MCISOE_PLUGIN_DIR . 'data/mcisoe_get_data.php';
    }

    public function create_item_menu()
    {

        add_menu_page(
            'Supplier Order Email', // Page title
            __( 'Supplier Order Email', 'supplier-order-email' ), // Menu title
            'manage_categories', // permissions
            'supplier-order-email', // slug
            [$this, 'mcisoe_settings_panel'], // callback function
            'dashicons-email', // icon
            56// position
        );
        add_submenu_page(
            'supplier-order-email', // parent slug
            'Suppliers', // Page title
            __( 'Suppliers', 'supplier-order-email' ), // Menu title
            'manage_categories', // permissions
            'suppliers', // slug
            [$this, 'mcisoe_suppliers'], // callback function
            10// position
        );

        add_submenu_page(
            'supplier-order-email', // parent slug
            'Help (Supplier Order Email)', // Page title
            __( 'Help', 'supplier-order-email' ), // Menu title
            'manage_categories', // permissions
            'mcisoe_help', // slug
            [$this, 'mcisoe_help'], // callback function
            10// position
        );

    }

    public function mcisoe_settings_panel()
    {
        require_once MCISOE_PLUGIN_DIR . 'data/mcisoe_get_data.php';
        $options = new McisoeGetData();

        require_once MCISOE_PLUGIN_DIR . 'admin/partials/mcisoe_save_panel.php';
        $save_panel = new McisoeSavePanel( $options );
        $save_panel->init();

        $options = new McisoeGetData();

        require_once MCISOE_PLUGIN_DIR . 'helpers/mcisoe_helpers.php';
        require_once MCISOE_PLUGIN_DIR . 'admin/partials/mcisoe_get_panel.php';

    }

    public function mcisoe_suppliers()
    {
        // Redirect to taxonomy edit suppliers page
        wp_redirect( admin_url( 'edit-tags.php?taxonomy=supplier&post_type=product' ) );
        exit;
    }

    public function mcisoe_help()
    {
        require_once MCISOE_PLUGIN_DIR . 'data/mcisoe_get_data.php';
        $options = new McisoeGetData();

        require_once MCISOE_PLUGIN_DIR . 'admin/partials/mcisoe_help.php';
        new McisoeHelp( $options->auth_premium );
    }

    public function init()
    {
        if ( is_plugin_active( 'woocommerce/woocommerce.php' ) ) {
            $this->create_item_menu();
        }

    }

}