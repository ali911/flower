<?php
/**
 * The admin-specific functionality of the plugin.
 *
 * @link       https://themehigh.com
 * @since      1.0.0
 *
 * @package    woocommerce-email-customizer-pro
 * @subpackage woocommerce-email-customizer-pro/admin
 */
if(!defined('WPINC')){	die; }

if(!class_exists('THWEC_Admin')):
 
class THWEC_Admin {
	/**
	 * Name of the plugin
	 *
	 * @access   private
	 * @var      $plugin_name    The string used to uniquely identify this plugin.
	 */
	private $plugin_name;

	/**
	 * Version of the plugin
	 *
	 * @access   private
	 * @var      $version    The string used to identify version of this plugin.
	 */
	private $version;

	/**
	 * The class instance responsible for template saving functionality
	 *
	 * @access   public
	 * @var      $general_instance    The general class instance
	 */
	public $general_instance = null;

	/**
	 * The class instance responsible for import export functionality
	 *
	 * @access   public
	 * @var      $migrate_instance    Import export class instance
	 */
	public $migrate_instance = null;

	/**
	 * The array of plugin pages that are required for loading styles and scripts
	 *
	 * @access   private
	 * @var      $plugin_pages    array of plugin pages
	 */
	private $plugin_pages;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @param      string    $plugin_name       The name of this plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {
		$this->plugin_name = $plugin_name;
		$this->version = $version;
		$this->plugin_pages = array(
			'toplevel_page_th_email_customizer_templates',
			'email-customizer_page_th_email_customizer_pro',
			'email-customizer_page_th_email_customizer_mapping',
			'email-customizer_page_th_email_customizer_react'
		);
	}

	/**
	 * Prepare instance of classes required for ajax requests
	 */
	public function prepare_ajax_queues(){
		if( THWEC_Utils::thwec_actions() && THWEC_Utils::is_ajax_query() ){
			$this->general_instance = THWEC_Admin_Settings_General::instance();
		}
		
		if( THWEC_Utils::is_migrate_action( false, true ) && is_null( $this->migrate_instance ) ){
			$this->migrate_instance = THWEC_Utils::get_migrate_instance();
		}
		
	}
	
	/**
	 * Common function to enqueue admin styles and scripts
	 *
	 * @param   string   $hook   current admin page
	 */
	public function enqueue_styles_and_scripts( $hook ) {
		if(!in_array($hook, $this->plugin_pages)){
			return;
		}

		$debug_mode = apply_filters('thwec_debug_mode', false);
		$suffix = $debug_mode ? '' : '.min';
		
		$this->enqueue_scripts( $hook, $suffix);
		$this->enqueue_styles($suffix);
		wp_enqueue_media();
	}

	/**
	 * enqueue admin styles
	 *
	 * @param   string   $suffix   load minified or unminified file
	 */
	private function enqueue_styles($suffix) {
		wp_enqueue_style('jquery-ui-style', '//code.jquery.com/ui/1.11.4/themes/smoothness/jquery-ui.css?ver=1.11.4');
		wp_enqueue_style('woocommerce_admin_styles', THWEC_WOO_ASSETS_URL.'css/admin.css');
		wp_enqueue_style('wp-color-picker');
		wp_enqueue_style('thwec-admin-style', THWEC_ASSETS_URL_ADMIN . 'css/thwec-admin'. $suffix .'.css', array(), $this->version);
		wp_enqueue_style('roboto-style','https://fonts.googleapis.com/css2?family=Roboto:wght@300;500;700&display=swap');
		wp_enqueue_style('wp-codemirror');
	}

	/**
	 * enqueue admin scripts
	 *
	 * @param string  $suffix  load minified or unminified file
	 */
	private function enqueue_scripts( $hook, $suffix ) {
		$script_var = array(
			'admin_url' => admin_url(),
            'ajaxurl'   => admin_url( 'admin-ajax.php' ),
            'admin_plugin_url' => THWEC_ASSETS_URL_ADMIN,
            'wpml_active'		=> THWEC_Utils::is_wpml_active(),
            'ywgc_active'		=> THWEC_Utils::is_ywgc_active(),
            'page_id' => isset( $_GET['page'] ) ? sanitize_text_field( $_GET['page'] ) : '',
            'initialize_builder' => wp_create_nonce( 'thwec-initialize-builder' ),
            'editing' => isset( $_POST['i_template_name'] ) ? 1 : (isset( $_POST['thwec_template_lang'] ) ? 1 : 0)
		);
		$template_filter = apply_filters('thwec_wpml_template_list_filter', true );
		$cm_settings['TestEditor'] = wp_enqueue_code_editor(array(
			'type' => 'text/css', 
			'gutters' => ['CodeMirror-lint-markers'],
			'lint' => true
			)
		);
		$deps = array('jquery', 'jquery-ui-core', 'jquery-ui-draggable', 'jquery-ui-droppable', 'jquery-ui-sortable','jquery-ui-resizable', 'jquery-ui-widget', 'jquery-ui-tabs','jquery-tiptip', 'woocommerce_admin', 'wc-enhanced-select', 'select2', 'wp-color-picker','select2');
		
		if( $hook === 'email-customizer_page_th_email_customizer_pro' ){
			wp_enqueue_script( 'thwec-admin-script', THWEC_ASSETS_URL_ADMIN . 'js/thwec-editor'. $suffix .'.js', ['wp-element', 'jquery'], $this->version, true );
			$yith_button_label = get_option ( 'ywgc_email_button_label', esc_html__( 'Apply your gift card code', 'yith-woocommerce-gift-cards' ) );
			$yith_button_label = empty( $yith_button_label ) ? esc_html__( 'Apply your gift card code', 'yith-woocommerce-gift-cards' ) : $yith_button_label;
			$shop_logo = get_option('ywgc_shop_logo_url');
			$header_image = get_option('ywgc_gift_card_header_url');
			$script_var = array_merge( $script_var, array(
				'add_css_feature'	=> true,
	            'cm_settings'		=> $cm_settings,
	            'save_temp_on_settings'	=> apply_filters('thwec_enable_template_save_on_settings_change', false),
	            'save_nonce'	=> wp_create_nonce( 'thwec-save-settings' ),
	            'preview_order' => wp_create_nonce( 'thwec-preview-order' ),
	            'ywgc_emails' => array_keys( THWEC_Utils::ywgc_emails() ),
	            'woo_orders' => $this->get_woo_orders(),
	            'woo_emails' => $this->get_woo_emails(),
	            'ywgc_ids' => $this->get_ywgc_ids(),
	            'template' => $this->get_template_details(),
	            'bloginfo' => get_bloginfo(),
	            'woo_currency_symbol' => get_woocommerce_currency_symbol(),
	            'builder_url' => THWEC_Utils::get_admin_url(),
	            'testmail_recepient' => apply_filters('thwecm_set_testmail_recepient', true) ? THWEC_Admin_Utils::get_logged_in_user_email() : "",
	            'allowed_tags' => apply_filters('thwec_set_allowed_tags_in_text', ['b', 'strong', 'u', 'i', 'a'])
	        ));

			$script_var['remove_unencoded_html'] = $this->should_remove_unencoded( $script_var );

	        if(THWEC_Utils::is_wpml_active() ){
        		$script_var['wpml_languages'] = icl_get_languages();
        		$script_var['wpml_lang_selected'] = isset( $_POST['thwec_template_lang'] ) && THWEC_Utils::is_wpml_active() ? sanitize_text_field( $_POST['thwec_template_lang'] ) : "";
        		$script_var['wpml_default_language'] = THWEC_Utils::get_wpml_locale( apply_filters( 'wpml_default_language', NULL ), true );
	        }
            if(THWEC_Utils::is_ywgc_active() ){
	            $script_var['ywgc'] = array(
	            	'display_qr' => get_option( 'ywgc_display_qr_code' , 'no' ),
	            	'description' => get_option( 'ywgc_description_template_email_text', esc_html__( "To use this gift card, you can either enter the code in the gift card field on the cart page or click on the following link to automatically get the discount.", 'yith-woocommerce-gift-cards' ) ),
	            	'expiration_message' => apply_filters ( 'yith_ywgc_gift_card_email_expiration_message', _x( 'This gift card code will be valid until %s ('.get_option( 'ywgc_plugin_date_format_option', 'yy-mm-dd' ).')', 'gift card expiration date', 'yith-woocommerce-gift-cards' ) ),
	            	'discount_button_label' => $yith_button_label,
	            	
	            	'shop_logo' => $shop_logo ? $shop_logo : YITH_YWGC_ASSETS_IMAGES_URL.'default-giftcard-main-image.jpg',
					'header_image' => $header_image ? $header_image : YITH_YWGC_ASSETS_IMAGES_URL.'default-giftcard-main-image.jpg',
	            	'logo_before_gift_card' => get_option( 'ywgc_shop_logo_on_gift_card_before', 'no' ),
	            	'logo_before_gift_card_alignment' => get_option( 'ywgc_shop_logo_before_alignment', 'center' ),
	            	'logo_after_gift_card' => get_option( 'ywgc_shop_logo_on_gift_card_after', 'no' ),
	            	'logo_after_gift_card_alignment' => get_option( 'ywgc_shop_logo_after_alignment', 'center' ),
	            );
	        }
		}else{
			wp_enqueue_script( 'thwec-admin-script', THWEC_ASSETS_URL_ADMIN . 'js/thwec-admin'. $suffix .'.js', $deps, $this->version, false );
			$script_var =  array_merge( $script_var, array(
	            'template_filter'		=> (int) $template_filter,
	            'export_nonce' 			=> wp_create_nonce( 'thwec-settings-export' ),
	            'import_nonce' 			=> wp_create_nonce( 'thwec-settings-import' ),
	            'upload_folder_info'	=>	THWEC_Utils::get_uploads_folder_info()
        	));
		}
		$script_data = apply_filters('thwec_get_script_data', array());
		$script_var = is_array($script_data) ? array_merge($script_data, $script_var) : $script_var;
		wp_localize_script('thwec-admin-script', 'thwec_var', $script_var);
	}

	private function should_remove_unencoded( $template ){
		$remove = false;
		if( isset( $template['template']['remove_unencoded'] ) && !empty($template['template']['remove_unencoded'] ) ) {
			$remove = $template['template']['remove_unencoded'];
		}
		return apply_filters('thwecmf_remove_template_json_html', $remove);
	}

	/**
	 * Collapse the wordpress sidebar menu
	 */
	public function collapse_admin_sidebar(){
		$page = isset( $_GET['page'] ) ? $_GET['page'] : false;
		if( $page && $page == 'th_email_customizer_pro' ){
			if( get_user_setting('mfold') != 'f' ){
				set_user_setting('mfold', 'f');
			}
		}else{
			set_user_setting('mfold', 'o');
		}
	}

	/**
	 * Collapse the wordpress sidebar menu
	 */
	public function prepare_preview(){
		$preview = THWEC_Utils::is_preview();
		$order_id = isset( $_GET['id'] ) ? sanitize_key( base64_decode( $_GET['id'] ) ) : false;
		$email = isset( $_GET['email'] ) ? sanitize_text_field( base64_decode( $_GET['email'] ) ) : false;
		$ywgc_email = isset( $_GET['ywgc_email'] ) && !empty( $_GET['ywgc_email'] ) ? sanitize_text_field( base64_decode( $_GET['ywgc_email'] ) ) : false;
		
		if( $preview ){
			$general = THWEC_Admin_Settings_General::instance();
			$content = $general->thwec_order_preview( $order_id, $email, $ywgc_email, $preview, false, true, true );
			$general->load_preview( $content );
			die;
		}
	}

	/**
	 * Add plugin menu to Wordpress sidebar menu
	 */
	public function admin_menu() {
		$manage_cap = THWEC_Utils::is_allowed_menu_cap( apply_filters('thwec_manage_plugin_menu_capability', 'manage_woocommerce') );
		$menu_pos = THWEC_Utils::is_a_menu_position( apply_filters( 'thwec_admin_menu_position', 56 ) );
		$this->screen_id = add_menu_page(  THWEC_i18n::t('Email Customizer'), THWEC_i18n::t('Email Customizer'), $manage_cap, 'th_email_customizer_templates', array($this, 'output_settings'), 'dashicons-admin-customizer', $menu_pos );
		$this->screen_id .= add_submenu_page('th_email_customizer_templates', THWEC_i18n::t('Templates'), THWEC_i18n::t('Templates'), $manage_cap, 'th_email_customizer_templates', array($this, 'output_settings'));
		$this->screen_id .= add_submenu_page('th_email_customizer_templates', THWEC_i18n::t('Email Mapping'), THWEC_i18n::t('Email Mapping'), $manage_cap, 'th_email_customizer_mapping', array($this, 'output_settings'));
		$this->screen_id .= add_submenu_page('th_email_customizer_templates', THWEC_i18n::t('Add New'), THWEC_i18n::t('Add New'), $manage_cap, 'th_email_customizer_pro', array($this, 'output_settings'));
		$this->screen_id .= add_submenu_page('th_email_customizer_templates', THWEC_i18n::t('Plugin License'), THWEC_i18n::t('Plugin License'), $manage_cap, 'th_email_customizer_license_settings', array($this, 'output_settings'));
	}
	
	/**
	 * Add screen ids
	 *
	 * @param  array $ids screen ids
	 * @return array $links screen ids
	 */	
	public function add_screen_id($ids){
		$ids[] = 'woocommerce_page_th_email_customizer_pro';
		$ids[] = strtolower( THWEC_i18n::t('WooCommerce') ) .'_page_th_email_customizer_pro';

		return $ids;
	}
	
	/**
	 * Add to plugin action links
	 *
	 * @param  array $links links to display
	 * @return array $links links to display
	 */	
	public function plugin_action_links($links) {
		$settings_link = '<a href="'.admin_url('admin.php?page=th_email_customizer_templates').'">'. __('Settings') .'</a>';
		array_unshift($links, $settings_link);
		return $links;
	}
	
	/**
	 * Add to plugin meta in plugin listings page
	 *
	 * @param  array $links links to display
	 * @param  string $file file path
	 * @return array $links links to display
	 */	
	public function plugin_row_meta( $links, $file ) {
		if(THWEC_BASE_NAME == $file) {
			$doc_link = esc_url('https://www.themehigh.com/help-guides/woocommerce-email-customizer/');
			$support_link = esc_url('https://www.themehigh.com/help-guides/');
				
			$row_meta = array(
				'docs' => '<a href="'.$doc_link.'" target="_blank" aria-label="'.THWEC_i18n::esc_attr__t('View plugin documentation').'">'.THWEC_i18n::esc_html__t('Docs').'</a>',
				'support' => '<a href="'.$support_link.'" target="_blank" aria-label="'. THWEC_i18n::esc_attr__t('Visit premium customer support' ) .'">'. THWEC_i18n::esc_html__t('Premium support') .'</a>',
			);

			return array_merge( $links, $row_meta );
		}
		return (array) $links;
	}

	/**
	 * Add custom class to body classes
	 *
	 * @param  string $classes classes
	 * @return string $classes classes
	 */	
	public function add_thwec_body_class( $classes ){
		$pages = array('th_email_customizer_templates', 'th_email_customizer_mapping', 'th_email_customizer_pro');
		$page = isset( $_GET['page'] ) ? sanitize_text_field( $_GET['page'] ) : false;
		if( $page && in_array( $page, $pages ) ){
			$page = str_replace('th_email_customizer_', "", $_GET['page'] );
			$page = $page == 'pro' ? 'builder' : $page;
			$classes .= ' thwec-page thwec-'.$page.'-page';
			$action = isset( $_GET['action'] ) ? sanitize_key( $_GET['action'] ) : false;
			if( $action && THWEC_Utils::is_migrate_action( sanitize_key( $action ) ) ){
				$classes .= ' thwec-migrate-page-'.$action;
			}
		}
		return $classes;
	}

	/**
	 * Disable admin notices in selected plugin pages
	 */	
	public function disable_admin_notices(){
		$page  = isset( $_GET['page'] ) ? esc_attr( $_GET['page'] ) : '';
		$action = isset( $_GET['action'] ) ? esc_attr( $_GET['action'] ) : '';
		$pages = array('th_email_customizer_pro','th_email_customizer_mapping');
		if(in_array($page, $pages) || THWEC_Utils::is_migrate_action( $action ) ){
			global $wp_filter;
      		if (is_user_admin() ) {
        		if (isset($wp_filter['user_admin_notices'])){
            		unset($wp_filter['user_admin_notices']);
        		}
      		} elseif(isset($wp_filter['admin_notices'])){
            	unset($wp_filter['admin_notices']);
      		}
      		if(isset($wp_filter['all_admin_notices'])){
        		unset($wp_filter['all_admin_notices']);
      		}
		}
	}

	/**
	 * Upgrade database if required
	 */	
	public function display_thwec_admin_notices() {
		if( $this->database_update_required() && apply_filters('thwec_database_upgrade', false ) ){
	 		$premium_settings = THWEC_Utils::get_template_settings();
			if( $premium_settings && is_array( $premium_settings ) ){
				if( !isset( $premium_settings[THWEC_Utils::get_template_samples_key()] ) || apply_filters('thwec_reset_sample_templates', false ) ){
					$premium_settings['thwec_samples'] = THWEC_Utils::get_sample_settings();
				}else if( ( isset( $premium_settings[THWEC_Utils::get_template_samples_key()] ) && empty( $premium_settings[THWEC_Utils::get_template_samples_key()] ) ) || apply_filters('thwec_reset_sample_templates', false ) ){
					$premium_settings['thwec_samples'] = THWEC_Utils::get_sample_settings();
				}
				$save = THWEC_Utils::save_template_settings( $premium_settings );
				if( $save ){
					update_option('thwec_version', THWEC_VERSION);
					?>
    				<div class="notice notice-success is-dismissible">
        		    	<p><?php _e( 'Email Customizer database upgrade successful!', 'thwec' ); ?></p>
        			</div>
    				<?php
				}
			}
    	}
	}

	/**
	 * Checks whether database update is required
	 *
	 * @return boolean update required or not
	 */	
	public function database_update_required(){
		// Version before 2.0.6 doesn't have a version option.
		// So older version updated to 2.0.6 or higher requires db upgrade
		$pre_version = get_option('thwec_version');
		if( !$pre_version || $pre_version < '3.0.0' ){ // 
			return true;
		}
		return apply_filters('thwec_force_update_db', false);
	}

	/**
	 * Render admin plugin pages
	 */
	public function output_settings(){
		$page  = isset( $_GET['page'] ) ? esc_attr( $_GET['page'] ) : 'th_email_customizer_pro';

		if($page === 'th_email_customizer_pro'){
			$template_settings = THWEC_Admin_Settings_Customizer::instance();	
			$template_settings->render_page();	

		}else if($page === 'th_email_customizer_advanced_settings'){
			$advanced_settings = THWEC_Admin_Settings_Advanced::instance();	
			$advanced_settings->render_page();

		}else if($page === 'th_email_customizer_mapping'){
			$advanced_settings = THWEC_Admin_Template_Mapping::instance();	
			$advanced_settings->render_page();

		}else if($page === 'th_email_customizer_license_settings'){
			$license_settings = THWEC_Admin_Settings_License::instance();	
			$license_settings->render_page();	

		}else{
			$template_settings = THWEC_Admin_Settings_Templates::instance();	
			$template_settings->render_page( $this->migrate_instance );	
		}
	}

	private function get_template_details(){
		$importer = THWEC_Builder_Importer::instance();
		return $importer->prepare_template_data( $_POST );
	}

	private function get_ywgc_ids(){
		$gift_cards = array();
		$ywgc_cards = array();
		if( defined( 'YWGC_CUSTOM_POST_TYPE_NAME' ) ){
			$gift_cards = get_posts([
		    	'post_type' => YWGC_CUSTOM_POST_TYPE_NAME,
		    	'post_status' => 'any',
		    	'numberposts' => -1,
			]);
		}
		if( is_array( $gift_cards ) ){
			foreach ($gift_cards as $key => $value) {
				$ywgc_cards[$value->ID] = $value->post_title;
			}
		}
		return $ywgc_cards;
	}

	private function get_woo_emails(){
		$woo_emails = [];
		$wc_emails = WC_Emails::instance();
		$wc_emails = isset( $wc_emails->emails ) ? $wc_emails->emails : false;
		if( $wc_emails ){
			foreach ($wc_emails as $wc_key => $wc_email) {
				if( !THWEC_Utils::is_compatible_email_status( $wc_email ) ){
					continue;
				}
				$woo_emails[$wc_key] = $wc_email->title;
				if( $wc_key === "WC_Email_Customer_Refunded_Order" ){
					$woo_emails["WC_Email_Customer_Partial_Refunded_Order"] = "Refunded order (Partial)";
				}
			}
			if( THWEC_Utils::is_ywgc_active() ){
				//WC_Emails has only two emails for YWGC
				foreach (THWEC_Utils::ywgc_emails() as $wc_key => $wc_email_title) {
					if( $wc_key == 'ywgc-email-delivered-gift-card' ){
						//WC_Email doesn't have ywgc-email-delivered-gift-card email
						continue;
					}
					$woo_emails[$wc_key] = $wc_email_title;
				}
			}
		}
		return $woo_emails;
	}

	private function get_woo_orders(){
		$woo_orders = [];
		$orders = THWEC_Utils::get_woo_orders();
		foreach ($orders as $key => $order) {
	    	$buyer = $this->get_buyer_info( $order );
	    	$order_id = $order->get_id();
	    	if( $buyer ){
				$user_string = sprintf( '(#%1$s) %2$s', $order_id, $buyer );
				$woo_orders[$order_id] = wp_kses_post( $user_string );
			}
		}
		return $woo_orders;
	}

	private function get_buyer_info( $order ){
		$buyer = false;
		if ( $order->get_billing_first_name() || $order->get_billing_last_name() ) {
			$buyer = trim( sprintf( _x( '%1$s %2$s', 'full name', 'woocommerce' ), $order->get_billing_first_name(), $order->get_billing_last_name() ) );
		} elseif ( $order->get_billing_company() ) {
			$buyer = trim( $order->get_billing_company() );
		} elseif ( $order->get_customer_id() ) {
			$user  = get_user_by( 'id', $order->get_customer_id() );
			$buyer = ucwords( $user->display_name );
		}
		return $buyer;
	}
}

endif;