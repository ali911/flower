<?php
if ( !defined( 'ABSPATH' ) ) {
    exit;
}

class McisoeAdmin
{
    public function check_woocommerce_is_active()
    {
        // Check if WooCommerce is installed and active
        if ( !in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) {

            //deactivate_plugins( MCISOE_PLUGIN_BASENAME );
            add_action( 'admin_notices', array( $this, 'woocommerce_required_message' ) );
        }
    }

    public function woocommerce_required_message()
    {
        echo '<div class="error"><p>' . __( 'Plugin "Supplier Order Email" requires WooCommerce to work correctly. Please install and activate "WooCommerce".', 'supplier-order-email' ) . '</p></div>';
    }

    public function wp_menu()
    {
        require_once MCISOE_PLUGIN_DIR . 'admin/partials/mcisoe_wp_menu.php';
        $mcisoe_wp_menu = new McisoeWpMenu();
        $mcisoe_wp_menu->init();
    }

    public function btn_order_list()
    {
        require_once MCISOE_PLUGIN_DIR . 'admin/partials/mcisoe_btn_order_list.php';
        $mcisoe_btn_order_list = new McisoeBtnOrderList();
        $mcisoe_btn_order_list->init();
    }

    public function enqueue_styles()
    {
        wp_enqueue_style( 'mcisoe-admin-style', MCISOE_PLUGIN_URL . 'admin/css/mcisoe_style.css' );
        wp_enqueue_style( 'mcisoe-help-style', MCISOE_PLUGIN_URL . 'admin/css/mcisoe_help.css' );

    }

    public function enqueue_scripts()
    {
        wp_enqueue_script( 'mcisoe-admin-script', MCISOE_PLUGIN_URL . 'admin/js/mcisoe_script.js', array( 'jquery' ), MCISOE_VERSION, true );
    }

    public function has_not_suppliers_message()
    {
        // Check if they are created Suppliers in Wordpress and display notice message if not exists.
        $terms = get_terms( array( 'taxonomy' => 'supplier', 'hide_empty' => false ) );
        $count = count( $terms );

        $actual_screen = get_current_screen();
        $actual_screen = $actual_screen->id;

        if ( empty( $count ) && $actual_screen != 'edit-supplier' && $actual_screen != 'woocommerce_page_supplier-order-email' ) {
            echo '<br><div class="notice notice-warning"><br>';
            echo __( "Before you start working with 'Supplier Order Email' plugin", "supplier-order-email" );
            echo ' <b>';
            echo __( "you must create the Suppliers", "supplier-order-email" );
            echo '</b>';
            echo ' ';
            echo '<a class="btn button mcisoe_notice_btn" href="edit-tags.php?taxonomy=supplier"> ';
            echo esc_html( __( 'Create "Suppliers" now in Products->Suppliers', 'supplier-order-email' ) );
            echo '</a>';
            echo '<br><br></div><br>';
        }

    }

    public function wc_import_export()
    {
        require_once MCISOE_PLUGIN_DIR . 'admin/partials/mcisoe_wc_import_export.php';
        $mcisoe_wc_importer = new McisoeWcImportExport();
        $mcisoe_wc_importer->init();
    }

    public function product_list_filter()
    {
        require_once MCISOE_PLUGIN_DIR . 'admin/partials/mcisoe_product_list_filter.php';
        $mcisoe_product_list_filter = new McisoeProductListFilter();
        $mcisoe_product_list_filter->init();
    }

    public function init()
    {
        // if it is admin area
        if ( is_admin() ) {
            add_action( 'admin_enqueue_scripts', [$this, 'enqueue_styles'] );
            add_action( 'admin_enqueue_scripts', [$this, 'enqueue_scripts'] );
            add_action( "admin_menu", [$this, 'wp_menu'] );
            add_action( 'admin_init', [$this, 'check_woocommerce_is_active'] );
            add_action( 'admin_notices', [$this, 'has_not_suppliers_message'] );
            add_action( 'admin_init', [$this, 'btn_order_list'] );
            add_action( 'admin_init', [$this, 'wc_import_export'] );
            add_action( 'admin_init', [$this, 'product_list_filter'] );
        }
    }

}