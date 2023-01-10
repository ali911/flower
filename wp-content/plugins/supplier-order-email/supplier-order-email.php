<?php
/**
 * Plugin Name: Supplier Order Email
 * Plugin URI: https://mci-desarrollo.es/supplier-order-email-premium/?lang=en
 * Author: MCI Desarrollo
 * Author URI: https://mci-desarrollo.es
 * Version: 2.2.11
 * Text Domain: supplier-order-email
 * Domain Path: /languages
 * Description: Sends automatic order emails to the suppliers to send the corresponding products to the customer. The mail is sent when the order goes to the "Processing" status.
 * Creates a new taxonomy (Suppliers) and a new selection box for each product.
 **/
if ( !defined( 'ABSPATH' ) ) {exit;}

//=======================================================================
define( 'MCISOE_VERSION', '2.2.11' );
define( 'MCISOE_REAL_ENVIRONMENT', true );
//=======================================================================

define( 'MCISOE_PLUGIN_BASENAME', plugin_basename( __FILE__ ) );
define( 'MCISOE_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'MCISOE_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
define( 'MCISOE_SLUG', 'supplier-order-email' );
define( 'MCISOE_HEADER_COLOR', '#0073aa' ); //Default header color
define( 'MCISOE_PLUGIN_OPTIONS_PAGE', admin_url( 'admin.php?page=supplier-order-email' ) );

///////////////////////////////////////////////////////////////////////////

//Load the plugin
require_once MCISOE_PLUGIN_DIR . 'includes/mcisoe_master.php';
$mcisoe_master = new McisoeMaster();
$mcisoe_master->init();

//=========================================================================
//Includes activate functions
function mcisoe_activate()
{
    require_once MCISOE_PLUGIN_DIR . 'includes/mcisoe_activate.php';
    $mcisoe_activate = new McisoeActivate;
    $mcisoe_activate->activate();
}

register_activation_hook( __FILE__, 'mcisoe_activate' );
//=========================================================================
//Execute activation if the version is different
if ( MCISOE_VERSION != get_option( 'mcisoe_version' ) ) {
    mcisoe_activate();
}

//=========================================================================
//Includes uninstall functions
function mcisoe_uninstall()
{
    require_once MCISOE_PLUGIN_DIR . 'includes/mcisoe_uninstall.php';
    $mcisoe_uninstall = new McisoeUninstall;
    $mcisoe_uninstall->init();
}

register_uninstall_hook( __FILE__, 'mcisoe_uninstall' );

//==========================================================================

// Execute class to send emails
require_once MCISOE_PLUGIN_DIR . 'includes/email/mcisoe_master_email.php';
$mcisoe_master_email = new McisoeMasterEmail;
if ( !isset( $_GET['send_soe'] ) ) { //If the send button has not been pressed manually in the order list.
    $mcisoe_master_email->init();
}
//========================================================================