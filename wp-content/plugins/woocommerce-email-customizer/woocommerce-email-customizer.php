<?php
/**
 * Plugin Name:       Email Customizer for WooCommerce (Pro)
 * Plugin URI:        https://themehigh.com/product/woocommerce-email-customizer
 * Description:       Add and customize WooCommerce transactional emails with the new drag and drop email builder (Create, edit, and delete email templates).
 * Version:           3.6.0
 * Author:            ThemeHigh
 * Author URI:        https://themehigh.com/
 *
 * Text Domain:       woocommerce-email-customizer-pro
 * Domain Path:       /languages
 * Update URI:		  https://www.themehigh.com/product/woocommerce-email-customizer/
 *
 * WC requires at least: 3.0.0
 * WC tested up to: 6.5.1
 */

if(!defined('WPINC')){	die; }

/**
 * Check if WooCommerce is active
 */
if (!function_exists('is_woocommerce_active')){
	function is_woocommerce_active(){
	    $active_plugins = (array) get_option('active_plugins', array());
	    if(is_multisite()){
		   $active_plugins = array_merge($active_plugins, get_site_option('active_sitewide_plugins', array()));
	    }
	    return in_array('woocommerce/woocommerce.php', $active_plugins) || array_key_exists('woocommerce/woocommerce.php', $active_plugins);
	}
}

/**
 * Define plugin constants
 */
if(is_woocommerce_active()) {
	define('THWEC_VERSION', '3.6.0');
	!defined('THWEC_SOFTWARE_TITLE') && define('THWEC_SOFTWARE_TITLE', 'WooCommerce Email Customizer');
	!defined('THWEC_FILE') && define('THWEC_FILE', __FILE__);
	!defined('THWEC_PATH') && define('THWEC_PATH', plugin_dir_path( __FILE__ ));
	!defined('THWEC_URL') && define('THWEC_URL', plugins_url( '/', __FILE__ ));
	!defined('THWEC_BASE_NAME') && define('THWEC_BASE_NAME', plugin_basename( __FILE__ ));
	
	/**
	 * The code that runs during plugin activation.
	 */
	function activate_thwec() {
		require_once plugin_dir_path( __FILE__ ) . 'includes/class-thwec-activator.php';
		THWEC_Activator::activate();
	}
	
	/**
	 * The code that runs during plugin deactivation.
	 */
	function deactivate_thwec() {
		require_once plugin_dir_path( __FILE__ ) . 'includes/class-thwec-deactivator.php';
		THWEC_Deactivator::deactivate();
	}
	
	register_activation_hook( __FILE__, 'activate_thwec' );
	register_deactivation_hook( __FILE__, 'deactivate_thwec' );
	
	/**
	 * License form title note
	 */
	function thwecm_license_form_title_note($title_note){
        $help_doc_url = 'https://www.themehigh.com/help-guides/general-guides/download-purchased-plugin-file';

        $title_note .= ' Find out how to <a href="%s" target="_blank">get your license key</a>.';
        $title_note  = sprintf($title_note, $help_doc_url);
        return $title_note;
    }

    /**
	 * License page url
	 */
	function thwecm_license_page_url($url, $prefix){
		$url = 'admin.php?page=th_email_customizer_license_settings';
		return admin_url($url);
	}

	/**
	 * Auto update
	 */

	function init_edd_updater_thwec_plugin(){
		if(!class_exists('THWECM_License_Manager') ) {

			require_once( plugin_dir_path( __FILE__ ) . 'class-thwecm-license-manager.php' );
			$helper_data = array(
				'api_url' => 'https://themehigh.com', // API URL
				'product_id' => 22, // Product ID in store
				'product_name' => 'Email Customizer for WooCommerce', // Product name in store. This must be unique.
				'license_page_url' => admin_url('plugins.php?page=my-plugin-license'), // ;icense page URL
			);

			THWECM_License_Manager::instance(__FILE__, $helper_data);
		}
	}
	init_edd_updater_thwec_plugin();
	
	/**
	 * The core plugin class that is used to define internationalization,
	 * admin-specific hooks, and public-facing site hooks.
	 */
	require plugin_dir_path( __FILE__ ) . 'includes/class-thwec.php';
	
	/**
	 * Begins execution of the plugin.
	 */
	function run_thwec() {
		$plugin = new THWEC();
		$plugin->run();
	}
	run_thwec();
}

function thwec_lm_to_edd_license_migration() {
	$edd_license_key = 'th_email_customizer_for_woocommerce_license_data';
	$edd_license_data = get_option($edd_license_key, array());
	if(empty($edd_license_data)){
		$lm_software_title = "WooCommerce Email Customizer";
		$lm_prefix = str_ireplace(array( ' ', '_', '&', '?', '-' ), '_', strtolower($lm_software_title));
		$lm_license_key = $lm_prefix . '_thlmdata';
		$lm_license_data = get_thlm_thwec_saved_license_data($lm_license_key);
		if($lm_license_data){
			$status = isset($lm_license_data['status']) ? $lm_license_data['status'] : '';
			if($status = 'active'){
				$new_data = array(
					'license_key' => isset($lm_license_data['license_key']) ? $lm_license_data['license_key'] : '',
					'expiry' => isset($lm_license_data['expiry_date']) ? $lm_license_data['expiry_date'] : '',
					'status' => 'valid',
				);
				$result = update_thwec_edd_license_data($edd_license_key, $new_data);
				if($result){
					delete_thwec_lm_license_data($lm_license_key);
				}
			}
		}
	}
	
}
add_action( 'admin_init', 'thwec_lm_to_edd_license_migration' );

function get_thlm_thwec_saved_license_data($key){
	$license_data = '';
	if(is_multisite()){
		$license_data = get_site_option($key);
	}else{
		$license_data = get_option($key);
	}
	return $license_data;
}

function update_thwec_edd_license_data($edd_license_key, $data){
	$result = false;
	if(is_multisite()){
		$result = update_site_option($edd_license_key, $data, 'no');
	}else{
		$result = update_option($edd_license_key, $data, 'no');
	}
	return $result;
}

function delete_thwec_lm_license_data($key){
	if(is_multisite()){
		delete_site_option($key);
	}else{
		delete_option($key);
	}
}
