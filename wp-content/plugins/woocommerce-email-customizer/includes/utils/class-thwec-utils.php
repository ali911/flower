<?php
/**
 * The common utility functionalities for the plugin.
 *
 * @link       https://themehigh.com
 * @since      1.0.0
 *
 * @package    woocommerce-email-customizer-pro
 * @subpackage woocommerce-email-customizer-pro/includes/utils
 */
if(!defined('WPINC')){	die; }

if(!class_exists('THWEC_Utils')):

class THWEC_Utils {
	const OPTION_KEY_TEMPLATE_SETTINGS = 'thwec_template_settings';
	const SETTINGS_KEY_TEMPLATE_LIST = 'templates';
	const SETTINGS_KEY_TEMPLATE_SAMPLES = 'thwec_samples';
	const SETTINGS_KEY_TEMPLATE_MAP = 'template_map';
	const SETTINGS_KEY_SUBJECT_MAP = 'email_subject';
	const SETTINGS_KEY_EMAIL_ATTACHMENTS = 'email_attachments';
	const SETTINGS_KEY_WPML_MAP = 'thwec_wpml_map';
	const SETTINGS_KEY_COMPATIBILITY = 'compatibility_settings';
	const OPTION_KEY_ADVANCED_SETTINGS = 'thwec_advanced_settings';
	const OPTION_KEY_VERSION = 'thwec_version';
	const OPTION_KEY_MISSING_TEMPLATE_FILES = 'thwec_missing_templates';

	const THWEC_EMAIL_INDEX = array(
		'new_order',
		'cancelled_order',
		'failed_order',
		'customer_on_hold_order',
		'customer_processing_order',
		'customer_completed_order',
		'customer_refunded_order',
		'customer_invoice',
		'customer_note',
		'customer_reset_password',
		'customer_new_account'
	);

	const THWEC_ACTIONS = array(
		'thwec_save_email_template', 
		'thwec_send_test_mail', 
		'thwec_initialize_builder', 
		'thwec_preview_template',
		// 'thwec_do_export',
		// 'thwec_do_import'
	);
	
	/**
	 * Sample template key
	 */
	public static function get_template_samples_key(){
		return self::SETTINGS_KEY_TEMPLATE_SAMPLES;
	}

	/**
	 * Subject key
	 */
	public static function get_template_subject_key(){
		return self::SETTINGS_KEY_SUBJECT_MAP;
	}

	/**
	 * Attachment key
	 */
	public static function get_email_attachment_key(){
		return self::SETTINGS_KEY_EMAIL_ATTACHMENTS;
	}

	/**
	 * Templates key
	 */
	public static function get_templates_key(){
		return self::SETTINGS_KEY_TEMPLATE_LIST;
	}

	/**
	 * Template map key
	 */
	public static function get_templates_map_key(){
		return self::SETTINGS_KEY_TEMPLATE_MAP;
	}

	/**
	 * Version key
	 */
	public static function get_version_key(){
		return self::OPTION_KEY_VERSION;
	}

	/**
	 * wpml template map key
	 */
	public static function wpml_map_key(){
		return self::SETTINGS_KEY_WPML_MAP;
	}

	/**
	 * Compatibility data key
	 */
	public static function thwec_compatibility_key(){
		return self::SETTINGS_KEY_COMPATIBILITY;
	}

	/**
	 * Save missing template list
	 *
	 * @param array $data missing templates
	 * @return boolean saved or not
	 */
	public static function save_missing_tfiles( $data ){
		delete_option(self::OPTION_KEY_MISSING_TEMPLATE_FILES);
		return add_option(self::OPTION_KEY_MISSING_TEMPLATE_FILES, $data);
	}

	/**
	 * Get missing template list
	 *
	 * @return array missing templates
	 */
	public static function get_missing_tfiles(){
		$files = get_option(self::OPTION_KEY_MISSING_TEMPLATE_FILES);
		return is_array( $files ) ? $files : array();
	}
		
	/**
	 * Check menu capability allowed
	 *
	 * @param string $menu_cap capability
	 * @return string $menu_cap capability
	 */
	public static function is_allowed_menu_cap( $menu_cap ){
		if( in_array( $menu_cap, array('edit_posts', 'manage_options') ) ){
			return $menu_cap;
		}
		return 'manage_options';
	}

	/**
	 * Validating plugin menu position
	 *
	 * @param integer $pos menu position
	 * @return integer menu position
	 */
	public static function is_a_menu_position( $pos ){
		return absint( intval( $pos ) );
	}

	/**
	 * Check if empty
	 *
	 * @param array    $value array
	 * @param string  $type type of field
	 * @param boolean||string $index array key
	 * @return boolean $empty empty or not
	 */
	public static function is_not_empty( $value, $type, $index=false ){
		switch ( $type ) {
			case 'array':
				$empty = is_array( $value ) && !empty( $value );
				break;
			default:
				$empty = isset( $value[$index] ) && !empty( $value[$index] ); 
				break;
		}

		return $empty;
	}

	public static function get_thwec_version(){
		return get_option(self::OPTION_KEY_VERSION);
	}

	/**
	 * Add plugin version
	 */
	public static function add_version(){
		$prev_version = get_option( self::OPTION_KEY_VERSION );
		if( THWEC_VERSION > $prev_version ){
			delete_option(self::OPTION_KEY_VERSION);
			add_option(self::OPTION_KEY_VERSION, THWEC_VERSION);
		}
	}

	/**
	 * Check whether to copy free version settings
	 *
	 * @param array $settings settings available in site
	 * @return boolean $copy copied or not
	 */
	public static function should_copy_free_settings( $settings ){
		$copy = false;
		if( isset( $settings[self::get_templates_key()] ) && empty( $settings[self::get_templates_key()] ) ){
			$copy = true;
		} 

		return apply_filters( 'thwec_copy_free_version_settings', $copy );
	}

	/**
	 * Restore the sample templates
	 *
	 * @param array $settings template settings
	 * @return boolean $restore sample templates restored or not
	 */
	public static function restore_sample_templates( $settings ){
		$restore = false;
		if( !isset( $settings[self::get_template_samples_key()] ) ){
			$restore = true;
		}else if( isset( $settings[self::get_template_samples_key()] ) && empty( $settings[self::get_template_samples_key()] ) ){
			$restore = true;
		}else if( THWEC_VERSION <= '3.0.0' ){
			$restore = true;
		}

		return apply_filters( 'thwec_reset_sample_templates', $restore );
	}

	/**
	 * Restore the email subject
	 *
	 * @param array $settings template settings
	 * @return boolean $restore subject restored or not
	 */
	public static function restore_email_subjects( $settings ){
		$restore = false;
	
		if( !isset( $settings[self::SETTINGS_KEY_SUBJECT_MAP] ) ){
			$restore = true;

		}else if( isset( $settings[self::SETTINGS_KEY_SUBJECT_MAP] ) && empty( $settings[self::SETTINGS_KEY_SUBJECT_MAP] ) ){
			$restore = true;

		}
		return apply_filters( 'thwec_reset_email_subjects', $restore );
	}
	
	/**
	 * Get default template settings
	 * 
	 * @return array template settings
	 */		
	public static function get_template_settings(){
		$settings = get_option(self::OPTION_KEY_TEMPLATE_SETTINGS);
		if(empty($settings)){
			$settings = array(
				self::SETTINGS_KEY_TEMPLATE_LIST => array(), 
				self::SETTINGS_KEY_TEMPLATE_MAP => array(),
				self::SETTINGS_KEY_TEMPLATE_SAMPLES => array(),
				self::SETTINGS_KEY_TEMPLATE_SAMPLES => array(),
				self::SETTINGS_KEY_SUBJECT_MAP => array(),
				self::SETTINGS_KEY_WPML_MAP => array(),
			);
		}
		return $settings;
	}

	/**
	 * Get wpml template map
	 *
	 * @param array $settings template settings
	 * @return array wpml template map
	 */
	public static function get_wpml_map( $settings ){
		return isset( $settings[self::wpml_map_key()] ) ? $settings[self::wpml_map_key()] : array();
	}

	/**
	 * Delete template settings
	 * 
	 * @return boolean settings deleted or not
	 */
	public static function delete_settings(){
		$status1 = $status2 = $status3 = $save = false;
		$settings = get_option(self::OPTION_KEY_TEMPLATE_SETTINGS);
		if( !empty($settings) ){
			if( isset( $settings[self::SETTINGS_KEY_TEMPLATE_MAP] ) && !empty( $settings[self::SETTINGS_KEY_TEMPLATE_MAP] ) ){
				$settings[self::SETTINGS_KEY_TEMPLATE_MAP] = array();
				$status1 = true;
			}
			if( isset( $settings[self::SETTINGS_KEY_SUBJECT_MAP] ) && !empty( $settings[self::SETTINGS_KEY_SUBJECT_MAP] ) ){
				$settings[self::SETTINGS_KEY_SUBJECT_MAP] = self::email_subjects();
				$status2 = true;
			}
			if( isset( $settings[self::SETTINGS_KEY_EMAIL_ATTACHMENTS] ) && !empty( $settings[self::SETTINGS_KEY_EMAIL_ATTACHMENTS] ) ){
				$settings[self::SETTINGS_KEY_EMAIL_ATTACHMENTS] = array();
				$status3 = true;
			}
			if( $status1 || $status2 || $status3 ){
				$save = self::save_template_settings( $settings );
				return $save;
			}		
		}
		return ($status1 || $status2 );
	}

	/**
	 * Get templates list
	 * 
	 * @param array $settings template settings
	 * @param boolean $flag seperate index for user and sample templates
	 * @return array $list templates list
	 */
	public static function get_template_list($settings=false, $flag=false){
		$list = [];

		if(!is_array($settings)){
			$settings = self::get_template_settings();
		}
		
		if( $flag ){
			if( is_array( $settings ) ){
				$list['user'] = isset($settings[self::SETTINGS_KEY_TEMPLATE_LIST]) ? $settings[self::SETTINGS_KEY_TEMPLATE_LIST] : array();
				$list['sample'] = isset($settings[self::SETTINGS_KEY_TEMPLATE_SAMPLES]) ? $settings[self::SETTINGS_KEY_TEMPLATE_SAMPLES] : array();
			}		
		}else{
			$list = is_array($settings) && isset($settings[self::SETTINGS_KEY_TEMPLATE_LIST]) ? $settings[self::SETTINGS_KEY_TEMPLATE_LIST] : array();
		}
		return $list;
	}

	/**
	 * Get template mapping
	 * 
	 * @param array $settings template settings
	 * @return array template mapping
	 */
	public static function get_template_map($settings=false){
		if(!is_array($settings)){
			$settings = self::get_template_settings();
		}
		return is_array($settings) && isset($settings[self::SETTINGS_KEY_TEMPLATE_MAP]) ? $settings[self::SETTINGS_KEY_TEMPLATE_MAP] : array();
	}

	/**
	 * Get template subject
	 * 
	 * @param array $settings template settings
	 * @return array subjects
	 */
	public static function get_template_subject($settings=false){
		if(!is_array($settings)){
			$settings = self::get_template_settings();
		}
		return is_array($settings) && isset($settings[self::SETTINGS_KEY_SUBJECT_MAP]) ? $settings[self::SETTINGS_KEY_SUBJECT_MAP] : array();
	}

	/**
	 * Get compatibility settings
	 *
	 * @param array $settings template settings
	 * @return array compatibility settings
	 */
	public static function get_compatibility( $settings=false ){
		if( !is_array( $settings ) ){
			$settings = self::get_template_settings();
		}
		return is_array($settings) && isset( $settings[self::SETTINGS_KEY_COMPATIBILITY] ) ? $settings[self::SETTINGS_KEY_COMPATIBILITY] : array();
	}

	/**
	 * Save template settings
	 *
	 * @param array   $settings settings
	 * @param boolean  $new new settings or not
	 * @return boolean settings saved or not
	 */
	public static function save_template_settings($settings, $new=false){
		$result = false;
		if($new){
			$result = add_option(self::OPTION_KEY_TEMPLATE_SETTINGS, $settings);
		}else{
			$result = update_option(self::OPTION_KEY_TEMPLATE_SETTINGS, $settings);
		}
		return $result;
	}

	/**
	 * Get advanced settings
	 *
	 * @return array $settings settings
	 */
	public static function get_advanced_settings(){
		$settings = get_option(self::OPTION_KEY_ADVANCED_SETTINGS);
		return empty($settings) ? false : $settings;
	}
	
	/**
	 * Get specific value from settings 
	 *
	 * @param array   $settings settings
	 * @param string  $key key in settings
	 * @return array $settings settings
	 */
	public static function get_setting_value($settings, $key){
		if(is_array($settings) && isset($settings[$key])){
			return $settings[$key];
		}
		return '';
	}

	/**
	 * Get template settings
	 *
	 * @return array $settings settings
	 */
	public static function get_settings($key){
		$settings = self::get_advanced_settings();
		if(is_array($settings) && isset($settings[$key])){
			return $settings[$key];
		}
		return '';
	}
	
	/**
	 * Get template directory
	 *
	 * @return string $dir directory path
	 */
	public static function get_template_directory(){
	    $upload_dir = wp_upload_dir();
	    $dir = $upload_dir['basedir'].'/thwec_templates';
      	//wp_mkdir_p($templates_folder);
      	$dir = trailingslashit($dir);
      	return $dir;
	}

	/**
	 * Get uploads folder base directory and url 
	 *
	 * @return array base directory and url
	 */
	public static function get_uploads_folder_info(){
	    $upload_dir = wp_upload_dir();
	    return array(
	    	'basedir' 	=>	$upload_dir['basedir'],
        	'baseurl' 	=>	$upload_dir['baseurl']
	    );
	}

	/**
	 * Check if plugin template file
	 *
	 * @return boolean template file or not
	 */
	public static function is_template_file( $filename ){
		if( $filename ){
			$file = self::get_template_directory().'/'.$filename;
			if( file_exists( $file ) ){
				return true;
			}
		}
		return false;
	}

	/**
	 * Get sample template settings
	 *
	 * @return array $settings settings
	 */
	public static function get_sample_settings(){
		$path = THWEC_PATH.'includes/settings.txt';
		$content = file_get_contents( $path );
		$settings = unserialize(base64_decode($content));
		$settings = isset( $settings['templates'] ) ? $settings['templates'] : '';
		return $settings;
	}

	/**
	 * Get sample template settings
	 *
	 * @return array $settings settings
	 */
	public static function get_compatible_plugin_samples($content){
		$settings = unserialize(base64_decode($content));
		$settings = isset( $settings['templates'] ) ? $settings['templates'] : '';
		return $settings;
	}

	/**
	 * Save sample template settings
	 *
	 * @param string  $content content
	 */
	public static function save_sample_settings( $content ){
		$content = base64_encode(serialize($content));
		$path = THWEC_PATH.'includes/settings.txt';
		$file = fopen($path, "w") or die("Unable to open file!");
		if(false !== $file){
			fwrite($file, $content);
			fclose($file);
		}
	}

	/**
	 * Reset sample templates
	 */
	public function sample_template_reset(){
		$settings = THWEC_Utils::get_template_settings();
		$sample = THWEC_Utils::get_sample_settings();
		$sample['template_name']['template_data'] = $settings['templates']['template_name']['template_data'];
		$new_sample = array();
		$new_sample['templates'] = $sample;
		THWEC_Utils::save_sample_settings( $new_sample );
	}

	/**
	 * Default email subjects
	 *
	 * @return array $subjects default subjects
	 */
	public static function email_subjects(){
		$subjects = array(
			'admin-new-order'					=> '[{site_name}]: New order #{order_id}',
			'admin-failed-order'				=> '[{site_name}]: Order #{order_id} has failed',
			'customer-reset-password'			=> 'Password Reset Request for {site_name}',
			'customer-refunded-order'			=> 'Your {site_name} order #{order_id} has been refunded',
			'customer-partially-refunded-order'	=> 'Your {site_name} order #{order_id} has been refunded',
			'customer-processing-order'			=> 'Your {site_name} order has been received!',
			'customer-on-hold-order'			=> 'Your {site_name} order has been received!',
			'customer-note'						=> 'Note added to your {site_name} order from {order_created_date}',
			'customer-new-account'				=> 'Your {site_name} account has been created!',
			'customer-invoice'					=> 'Invoice for order #{order_id} on {site_name}',
			'customer-completed-order'			=> 'Your {site_name} order is now complete',
			'admin-cancelled-order'				=> '[{site_name}]: Order #{order_id} has been cancelled',
			'ywgc-email-send-gift-card' 		=> '[{site_name}] You have received a gift card',
			'ywgc-email-notify-customer' 		=> '[{site_name}] Your gift card has been used',
			'ywgc-email-delivered-gift-card' 	=> '[{site_name}] Your gift card has been delivered',
		);
		return apply_filters('thwec_default_email_subjects', $subjects);
	}

	/**
	 * Default email subjects without placeholder
	 *
	 * @return array $subjects default subjects
	 */
	public static function email_subjects_plain(){
		$subjects = array(
			'customer-reset-password'	=> 'Password Reset Request for %s',
			'customer-refunded-order'	=> 'Your %s order #%s has been refunded',
			'customer-processing-order'	=> 'Your %s order has been received!',
			'customer-on-hold-order'	=> 'Your %s order has been received!',
			'customer-note'				=> 'Note added to your %s order from %s',
			'customer-new-account'		=> 'Your %s account has been created!',
			'customer-invoice'			=> 'Invoice for order #%s on %s',
			'customer-completed-order'	=> 'Your %s order is now complete',
		);
		return $subjects;
	}

	/**
	 * Default WC Email statuses
	 *
	 * @return array $email_statuses email statuses
	 */
	public static function email_statuses(){
		$email_statuses = array(
			'admin-new-order' 					=> 'Admin New Order Email',
			'admin-cancelled-order'				=> 'Admin Cancelled Order Email',
			'admin-failed-order'				=> 'Admin Failed Order Email',
			'customer-completed-order'			=> 'Customer Completed Order',
			'customer-on-hold-order'			=> 'Customer On Hold Order Email',
			'customer-processing-order'			=> 'Customer Processing Order',
			'customer-refunded-order'			=> 'Customer Refunded Order',
			'customer-partially-refunded-order'	=> 'Customer Refunded Order (Partial)',
			'customer-invoice'					=> 'Customer invoice / Order details',
			'customer-note'						=> 'Customer Note',
			'customer-reset-password'			=> 'Reset Password',
			'customer-new-account'				=> 'New Account',
		);
		return apply_filters('thwec_email_statuses', $email_statuses);
	}

	/**
	 * Get YITH Giftcard emails
	 *
	 * @return array $emails emails
	 */
	public static function ywgc_emails(){
		$emails = array(
			'ywgc-email-send-gift-card' => 'YITH Gift Cards - Dispatch of the code',
			'ywgc-email-notify-customer' => 'YITH Gift Cards - Customer Notification',
			'ywgc-email-delivered-gift-card' => 'YITH Gift Cards - Delivered Gift Card Notification',
		);
		return $emails;
	}

	/**
	 * Checks whether the email is plugin compatible
	 *
	 * @param object $emails WooCommerce emails
	 * @return boolean whether plugin compatible email status or not
	 */
	public static function is_compatible_email_status( $email ){
		if( in_array( $email->id, self::THWEC_EMAIL_INDEX ) ){
			return true;
		}
		
		if( self::is_order_status_manager_active() ){
			if( strpos( get_class( $email ), 'WC_Order_Status_Manager') !== false ){
				return true;
			}
		}

		if( self::is_ywgc_active() ){
			if( array_key_exists( $email->id, self::ywgc_emails() ) ){
				return false;
			}
		}
		return false;
	}

	/**
	 * Checks if YITH Gift Card premium plugin is active or not
	 *
	 * @return plugin active or not
	 */
	public static function is_ywgc_active(){
		if( is_plugin_active( 'yith-woocommerce-gift-cards-premium/init.php' ) && defined( 'YITH_YWGC_INIT' ) ){
			return true;
		}
		return false;
	}

	/**
	 * Checks if YITH Gift Card premium plugin is active or not
	 *
	 * @return plugin active or not
	 */
	public static function is_back_in_stock_notifier_active(){
		if( is_plugin_active( 'back-in-stock-notifier-for-woocommerce/cwginstocknotifier.php' ) && defined( 'CWGINSTOCK_VERSION' ) ){
			return true;
		}
		return false;
	}
	
	/**
	 * Format subject placeholders
	 *
	 * @param string   $data subject
	 * @param string  $status email status
	 * @param object $order array key
	 * @return string $data formatted subject
	 */
	public static function format_subjects( $data, $status, $order ){
		if( array_key_exists( $status, self::ywgc_emails() ) ){
			$placeholders = array(
				'{site_name}'	=> get_bloginfo(),
			);
		}else if(isset($order)){
			$placeholders = array(
				'{customer_name}'			=> $order->get_billing_first_name(),
				'{customer_full_name}'		=> self::get_customer_full_name( $order ),
				'{site_name}'				=> get_bloginfo(),
				'{order_id}'				=> $order->get_id(),
				'{order_number'				=> $order->get_order_number(),
				'{order_created_date}'		=> self::get_order_created_date( $order ),
				'{order_completed_date}'	=> self::get_order_completed_date( $order ),
				'{order_total}'				=> $order->get_total(),
				'{order_formatted_total}'	=> $order->get_formatted_order_total(),
				'{billing_first_name}'		=> $order->get_billing_first_name(),
				'{billing_last_name}'		=> $order->get_billing_last_name(),
				'{billing_last_name}'		=> $order->get_billing_last_name(),
				'{billing_company}' 		=> $order->get_billing_company(),
				'{billing_country}' 		=> $order->get_billing_country(),
				'{billing_address_1}' 		=> $order->get_billing_address_1(),
				'{billing_address_2}' 		=> $order->get_billing_address_2(),
				'{billing_city}' 			=> $order->get_billing_city(),
				'{billing_state}' 			=> $order->get_billing_state(),
				'{billing_postcode}' 		=> $order->get_billing_postcode(),
				'{billing_phone}' 			=> $order->get_billing_phone(),
				'{billing_email}' 			=> $order->get_billing_email(),
				'{shipping_first_name}' 	=> $order->get_shipping_first_name(),
				'{shipping_last_name}' 		=> $order->get_shipping_last_name(),
				'{shipping_company}' 		=> $order->get_shipping_company(),
				'{shipping_country}' 		=> $order->get_shipping_country(),
				'{shipping_address_1}' 		=> $order->get_shipping_address_1(),
				'{shipping_address_2}' 		=> $order->get_shipping_address_2(),
				'{shipping_city}' 			=> $order->get_shipping_city(),
				'{shipping_state}' 			=> $order->get_shipping_state(),
				'{shipping_postcode}' 		=> $order->get_shipping_postcode(),
				'{payment_method}'			=> $order->get_payment_method(),
			);
		}

		$data = __( $data, 'woocommerce-email-customizer-pro' );
		$data = self::add_dynamic_data($placeholders,$data);
		return $data;
	}

	public static function add_dynamic_data($placeholders, $data){
		$placeholder_arr = array();
		if( is_array($placeholders) ){
			foreach ($placeholders as $key => $value) {
				$count = 0;
				$data = str_replace( $key, '%s', $data, $count );

				if( $count >= 1 ){
					array_push( $placeholder_arr, $value );
				}
			}
		}
		if( !empty( $placeholder_arr ) ){
			$data = vsprintf( $data, $placeholder_arr );

		}
		return $data;
	}

	/**
	 * Customer full name
	 *
	 * @param object  $order WC Order
	 * @return string customer full name
	 */
	public static function get_customer_full_name( $order ){
		return $order->get_billing_first_name().' '.$order->get_billing_last_name();
	}

	/**
	 * Order completed date
	 *
	 * @param object  $order WC Order
	 * @return string date completed
	 */
	public static function get_order_completed_date( $order ){
		$order_date = '';
		if( isset($order) && $order->has_status( 'completed' ) ){
			$order_date = wc_format_datetime( $order->get_date_completed() );
		}
		return $order_date;
	}

	/**
	 * Order created date
	 *
	 * @param object  $order WC Order
	 * @return string date created
	 */
	public static function get_order_created_date( $order ){
		return wc_format_datetime($order->get_date_created());
	}

	/**
	 * If WPML active
	 *
	 * @return boolean active or not
	 */
	public static function is_wpml_active(){
		global $sitepress;
		return function_exists('icl_object_id') && is_object($sitepress);
	}

	/**
	 * Get wpml locale of language
	 *
	 * @param string  $lang_code language code
	 * @param boolean $lowercase convert to lowercase or not
	 * @return string locale
	 */
	public static function get_wpml_locale( $lang_code, $lowercase=false ){
		global $sitepress;
		$locale = $sitepress->get_locale($lang_code);
		return $lowercase ? strtolower( $locale ) : $locale;
	}

	/**
	 * Compatible plugin list
	 * 
	 * @return array $active active plugins
	 */
	public static function compatible_plugins(){
		$active = array();
		if(!function_exists('is_plugin_active')){
			include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
		}

		if( is_plugin_active( 'yith-woocommerce-gift-cards-premium/init.php' ) ){
			array_push( $active, 'yith-woocommerce-gift-cards-premium');
		}

		return $active;
	}

	/**
	 * Font family list
	 *
	 * @return array font family list
	 */
	public static function font_family_list(){
		return array(
			"helvetica" 	=> 	"'Helvetica Neue',Helvetica,Roboto,Arial,sans-serif",
        	"georgia" 		=> 	"Georgia, serif",
        	"times" 		=> 	"'Times New Roman', Times, serif",
        	"arial" 		=> 	"Arial, Helvetica, sans-serif",
        	"arial-black" 	=> 	"'Arial Black', Gadget, sans-serif",
        	"comic-sans" 	=> 	"'Comic Sans MS', cursive, sans-serif",
        	"impact" 		=> 	"Impact, Charcoal, sans-serif",
        	"tahoma"	 	=> 	"Tahoma, Geneva, sans-serif",
        	"trebuchet" 	=> 	"'Trebuchet MS', Helvetica, sans-serif",
        	"verdana" 		=>	"Verdana, Geneva, sans-serif",
		);
	}


	/**
	 * Global link styles to be used in email template
	 *
	 * @return string style
	 */
	public static function get_template_global_css( $type ){
		$css = '';
		if( $type == 'link-color' ){
			$link_color = '#1155cc';
			$link_color = apply_filters('thwec_template_link_color', sanitize_hex_color( $link_color ) );
			$css = is_null( $link_color ) ? '#1155cc' : $link_color;

		}else if( $type == 'link-decoration' ){
			$css = apply_filters('thwec_template_link_decoration', sanitize_text_field( 'underline' ) );
		}
		
		return $css;
	}

	/**
	 * Email styles for WooCommerce email hook content compatibility
	 *
	 * @return string email styles
	 */
	public static function wecm_email_styles(){
		$text_align  = is_rtl() ? "right" : "left";
		$margin_side = is_rtl() ? "left" : "right";

		$link_color = self::get_template_global_css('link-color');

		$styles = '';
		$styles .= '#tp_temp_builder #template_container,#tp_temp_builder #template_header,#tp_temp_builder #template_body,#tp_temp_builder #template_footer{width:100% !important;}';
		$styles .= '#tp_temp_builder #template_container{width:100% !important;border:0px none transparent !important;}';
		$styles .= '#tp_temp_builder #wrapper{padding:0;background-color:transparent;}';

		// Order table hook styles
		$styles .= '#tp_temp_builder table.td{font-size: 14px;line-height:150%;}';
		$styles .= '#tp_temp_builder table.td td,#tp_temp_builder table.td th{padding: 12px;font-size:inherit;line-height:inherit;}';

		// For order table item meta
		$styles .= '#tp_temp_builder ul.wc-item-meta{font-size:small;margin:1em 0 0;padding:0;list-style:none;}';
		$styles .= '#tp_temp_builder ul.wc-item-meta li{margin: 0.5em 0 0;padding:0;}';
		$styles .= '#tp_temp_builder ul.wc-item-meta li .wc-item-meta-label{float: ' . esc_attr( $text_align ) . ';margin-' . esc_attr( $margin_side ) . ': .25em;clear:both;}';
		$styles .= '#tp_temp_builder ul.wc-item-meta li p{margin: 0 0 16px;}';

		$styles .= '#tp_temp_builder div > table.td th, #tp_temp_builder div > table.td td{word-break: keep-all;}';
		
		// Top and bottom padding in mobile device ( wrapper with 70px gray color for desktop )
		if( apply_filters( 'thwec_mobile_compatibility_wrapper_padding', true ) ){
			$styles .= '@media only screen and (max-width:480px) {
	  			#thwec_template_wrapper .thwec-template-wrapper-column{ padding: 0px !important;} 
	  		}';
		}

		if( apply_filters( 'thwec_enable_global_link_color', true ) ){
			$styles .= '#tp_temp_builder  a.thwec-link,
                #tp_temp_builder  .thwec-block-text a,
                #tp_temp_builder .thwec-block-billing a,
                #tp_temp_builder .thwec-block-shipping a,
                #tp_temp_builder .thwec-block-customer a{
					color: '.$link_color.' !important;
				}';
		}

		if( apply_filters( 'thwec_enable_global_link_color_for_address', false) ){
			$styles .= 'address{color: '.$link_color.' !important;}';
		}

		//Pay with cod text
		$styles .= '#tp_temp_builder div > p{color:#636363;font-size:14px;}';

		//Address field font size.
		$styles .= '#tp_temp_builder .address{font-size:14px;line-height:150%;}';

		$styles .= '#tp_temp_builder .thwec-short-description{margin-top:10px;}';

		return $styles;
	}

	/**
	 * Check if doing ajax
	 *
	 * @return boolean doing ajax or not
	 */
	public static function is_ajax_query(){
		if( is_admin() && (defined( 'DOING_AJAX' ) && DOING_AJAX) ){
			return true;
		}
		return false;
	}

	/**
	 * Check if an email customizer action
	 */
	public static function thwec_actions(){
		$action = isset( $_POST['action'] ) ? sanitize_text_field( $_POST['action'] ) : '';
		if( !empty( $action) && in_array( $action, self::THWEC_ACTIONS ) ){
			return true;
		}
		return false;
	}

	/**
	 * Get instance of migrate action class
	 *
	 * @return object $migrate class instance
	 */
	public static function get_migrate_instance(){
		$migrate = null;
		$action = isset( $_GET['action'] ) ? sanitize_key( $_GET['action'] ) : false;

		$page = isset( $_GET['page'] ) && sanitize_text_field( $_GET['page'] ) == 'th_email_customizer_templates' ? true : false;

		$migrate_ajax = isset( $_POST['action'] ) ? str_replace( 'thwec_do_', '', sanitize_key( $_POST['action'] ) ) : false;

		if( ($action && $page ) || ( self::is_ajax_query() && $migrate_ajax ) ){
			$action = $migrate_ajax ? $migrate_ajax : $action;
		}
		
		if( $action && self::is_migrate_action( $action ) ){
			$action_class = 'THWEC_'.ucfirst( $action );
			$migrate = $action_class::instance();
		}
		
		return $migrate;
	}

	/**
	 * Check if migrating plugin settings
	 *
	 * @param string   $action migrating action
	 * @return boolean migrating or not
	 */
	public static function is_migrate_action( $action=false, $post=false ){
		if( $post ){
			$action = isset( $_POST['action'] ) ? str_replace( 'thwec_do_', '', sanitize_text_field( $_POST['action'] ) ) : false;
		}
		if( !$action ){
			$action = isset( $_GET['action'] ) ? sanitize_text_field( $_GET['action'] ) : '';
		}
		return in_array( $action, array( 'import', 'export' ) );
	}

	/**
	 * Random template name
	 *
	 * @return string $name template name
	 */
	public static function get_sample_template_name(){
        $name = 'template_'.date('Y_m_d_H_i_s', time()); // GMT timezone
        return $name;
    }

    /**
	 * Get plugin admin page url
	 *
	 * @param string  $tab tab key
	 * @param string  $section section key
	 * @return string $url url of admin page
	 */
    public static function get_admin_url($tab = false, $section = false, $action = false){
        $url = 'admin.php?page=th_email_customizer_pro';
        if($tab && !empty($tab)){
            $url .= '&tab='. $tab;
        }
        if($section && !empty($section)){
            $url .= '&section='. $section;
        }
        if($action && !empty($action)){
        	$url .= '&action='. $action;
        }
        return admin_url($url);
    }

    /**
	 * Json decode string
	 *
	 * @param string  $data string
	 * @param boolean $output_array decode to array or not. If true decoded to object
	 * @return decoded data on success, boolean false on failure
	 */
    public static function decode_data( $data, $output_array = false ){
    	$data = json_decode( $data, $output_array );
    	if( JSON_ERROR_NONE !== json_last_error() ){
    		return false;
    	}
    	return $data;
    }

    /**
	 * Validate boolean values
	 *
	 * @param any $boolean any value to check
	 * @return boolean boolean value of $boolean
	 */
    public static function validate_boolean( $boolean ){
    	return  filter_var( $boolean, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE );
    }

    /**
	 * Check if Order status manager plugin is active
	 *
	 * @return boolean active or not
	 */
    public static function is_order_status_manager_active(){
    	if( class_exists( 'WC_Order_Status_Manager' ) ){
    		return true;
    	}
    	return false;
    }
	
	/**
	 * Order status manager for WooCommerce custom emails
	 *
	 * @param array  $wc_email WC emails
	 * @param array $type emails
	 * @return array $emails emails
	 */
    public static function get_order_status_manager_emails( $wc_email, $emails ){
    	if( self::is_order_status_manager_email( $wc_email ) ){
			$emails[$wc_email->id] = $wc_email->title;
		}
		return $emails;
    }

    /**
	 * Checks whether the current email class is from WooCommerce order status manager
	 *
	 * @param array  $wc_email WC emails
	 * @return boolean WooCommerce order status manager or not
	 */
    public static function is_order_status_manager_email( $wc_email ){
    	if( strpos( get_class( $wc_email ), 'WC_Order_Status_Manager') !== false ){
			return true;
		}
		return false;
    }
	
	/**
	 * Order status manager for WooCommerce slug for an email status
	 *
	 * @param object $wc_email WC emails
	 * @return string $template_key template key
	 */  
    public static function get_order_status_manager_slug( $wc_email ){
    	$template_key = str_replace(" ", "-", strtolower( $wc_email->title ) );
		$template_key = 'order-status-email-'.$template_key;
    	return $template_key;
    }

    /**
	 * Get backup directory path or file path for managing imported file
	 *
	 * @param string $filename optional
	 *
	 * @return string filepath
	 */
  	public static function get_backup_path( $filename=false ){
		if( $filename ){
			return THWEC_CUSTOM_TEMPLATE_PATH.'backup/'.$filename;
		}
		return THWEC_CUSTOM_TEMPLATE_PATH.'backup';
	}

	/**
	 * Get preview directory path
	 *
	 * @return string directory path
	 */
    public static function get_template_preview_directory(){
    	return THWEC_CUSTOM_TEMPLATE_PATH.'preview';
    }

    /**
	 * Get template preview file path
	 *
	 * @return string path of the template file
	 */	
	public static function get_template_preview_path( $name, $type='html' ){
		return THWEC_CUSTOM_TEMPLATE_PATH.'preview/thwec-'.$type.'-preview-'.$name.'.php';
	}

	 /**
	 * Get preview style file path
	 *
	 * @return string path of the template file style
	 */	
	public static function get_style_preview_path( $name ){
		return THWEC_CUSTOM_TEMPLATE_PATH.'preview/thwec-preview-style-'.$name.'.css';
	}

    /**
	 * Check if preview page
	 *
	 * @return   boolean   preview page or not
	 */
	public static function is_preview(){
		if( isset( $_GET['preview'] ) ){
			return sanitize_text_field( urldecode( base64_decode( $_GET['preview'] ) ) );
		}
		return false;
	}

    /**
	 * Advanced var_dump function
	 *
	 * @param string $str string
	 */
	public static function dump( $str, $margin_left=false ){
		$style = "";
		if( is_int( $margin_left ) ){
			$style = 'style="margin-left:'.$margin_left.'px;"';
		}
		?>
		<pre <?php echo $style; ?>>
			<?php echo var_dump($str); ?>
		</pre>
		<?php
	}

	/**
	 * Get WooCommerce orders
	 *
	 * @return array orders
	 */
	public static function get_woo_orders(){
		$count = apply_filters( 'thwec_template_preview_order_count', 5 );
		$orders = new WP_Query(
			array(
				'post_type'      => 'shop_order',
				'post_status'    => array_keys( wc_get_order_statuses() ),
				'posts_per_page' => $count,
			)
		);
		$order_objects = [];
		if ( $orders->posts ) {
			foreach ( $orders->posts as $order ) {
				$order_objects[] = wc_get_order( $order->ID );
			}
		}
		return $order_objects;
	}

	/**
	 * Delete backup directory and files
	 *
	 */
	public static function delete_backup_directory(){
		$backup = self::get_backup_path();
		if( is_dir( $backup ) ){
			return self::delete_directory( $backup);
		}
		
	}

	/**
	 * Delete all the files in a directory and files in it
	 *
	 * @param $dir path of the folder to delete
	 */
	public static function delete_directory( $dir, $remove_parent=true ){
		$files = scandir( $dir ); // get all file names
		foreach( $files as $file ){ // iterate files
			if( $file != '.' && $file != '..' ){ //scandir() contains two values '.' & '..' 
				if( is_file( $dir.'/'.$file ) ){
					unlink( $dir.'/'.$file ); // delete file		  	
				}else if( is_dir( $dir.'/'.$file ) ){
					self::delete_directory( $dir.'/'.$file );
				}
			}
		}
		if( empty($dir) ){
			return true;
		}
		if( file_exists( $dir ) && $remove_parent ){
			return rmdir( $dir );
		}
		return false;
	}

	public static function clear_preview_directory(){
		$path = self::get_template_preview_directory();
		if( is_dir($path) ){
			return self::delete_directory( $path, false );
		}
		return false;
	}

	public static function whether_previewfile_exist( $html, $css ){
		$path = self::get_template_preview_directory();
		if( is_dir( $path ) && file_exists( $html) && file_exists($css) ){
			return true;
		}
		return false;
	}

	/**
	 * Custom email header for WooCommerce emails
	 *
	 */
	public static function thwec_email_header( $email_heading, $email ){
		?>
		<div id="wrapper" dir="<?php echo is_rtl() ? 'rtl' : 'ltr'; ?>">
			<table border="0" cellpadding="0" cellspacing="0" height="100%" width="100%">
				<tr>
					<td align="center" valign="top">
						<div id="template_header_image">
							<?php
							if ( $img = get_option( 'woocommerce_email_header_image' ) ) {
								echo '<p style="margin-top:0;"><img src="' . esc_url( $img ) . '" alt="' . get_bloginfo( 'name', 'display' ) . '" /></p>';
							}
							?>
						</div>
						<table border="0" cellpadding="0" cellspacing="0" width="600" id="template_container">
							<tr>
								<td align="center" valign="top">
									<!-- Header -->
									<table border="0" cellpadding="0" cellspacing="0" width="100%" id="template_header">
										<tr>
											<td id="header_wrapper">
												<h1><?php echo $email_heading; ?></h1>
											</td>
										</tr>
									</table>
									<!-- End Header -->
								</td>
							</tr>
						</table>
					</td>
				</tr>
			</table>
		</div>
		<?php
	}

	/**
	 * Custom email footer for WooCommerce emails
	 *
	 */
	public static function thwec_email_footer( $email ){
		?>
		<table width="100%">
			<tr>
				<td align="center" valign="top">
					<!-- Footer -->
					<table border="0" cellpadding="10" cellspacing="0" width="600" id="template_footer">
						<tr>
							<td valign="top">
								<table border="0" cellpadding="10" cellspacing="0" width="100%">
									<tr>
										<td colspan="2" valign="middle" id="credit">
											<?php echo wp_kses_post( wpautop( wptexturize( apply_filters( 'woocommerce_email_footer_text', get_option( 'woocommerce_email_footer_text' ) ) ) ) ); ?>
										</td>
									</tr>
								</table>
							</td>
						</tr>
					</table>
					<!-- End Footer -->
				</td>
			</tr>
		</table>
		<?php
	}

	/**
	 * Check if user has capability|| roles to do actions
	 *
	 * @return boolean capable or not
	 */
	public static function is_user_capable(){
		$capable = false;
		$user = wp_get_current_user();
		$allowed_roles = apply_filters('thwecmf_user_capabilities_override', array('editor', 'administrator') );
		if( array_intersect($allowed_roles, $user->roles ) ) {
   			$capable = true;
   		}else if( is_super_admin($user->ID ) ){
   			$capable = true;
   		}
   		return $capable;
	}

	/**
	 * Check WooCommerce version
	 *
	 * @param  boolean $version version
	 * @return boolean
	 */
	public static function woo_version_check( $version = '3.0' ) {
	  	if(function_exists( 'is_woocommerce_active' ) && is_woocommerce_active() ) {
			global $woocommerce;
			if( version_compare( $woocommerce->version, $version, ">=" ) ) {
		  		return true;
			}
	  	}
	  	return false;
	}

}

endif;