<?php
/**
 * The public-facing functionality of the plugin.
 *
 * @link       https://themehigh.com
 * @since      1.0.0
 *
 * @package    woocommerce-email-customizer-pro
 * @subpackage woocommerce-email-customizer-pro/public
 */
if(!defined('WPINC')){	die; }

if(!class_exists('THWEC_Public')):
 
class THWEC_Public {
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
	 * Manages the current language set in WPML
	 *
	 * @access   private
	 * @var      $current_lang    WPML current language
	 */
	private $current_lang = null;
	
	/**
	 * Manages the WPML active status
	 *
	 * @access   private
	 * @var      $wpml_active    WPML active or not
	 */
	private $wpml_active = false;
	
	/**
	 * Manages the default language set in WPML 
	 *
	 * @access   private
	 * @var      $wpml_default_lang    WPML default language
	 */
	private $wpml_default_lang = '';
    
    /**
	 * Manages the subjects for all supported email notifications
	 *
	 * @access   private
	 * @var      $subjects    array of subjects
	 */
    public $subjects;
    
    /**
	 * Manages the email customizer settings
	 *
	 * @access   private
	 * @var      $settings    array of settings for customizer
	 */
    private $settings = array();
    
    /**
	 * Manages the data required for order status manager
	 *
	 * @access   private
	 * @var      $wc_status_manager    order status manager helper
	 */
    private $wc_status_manager = array();

    /**
	 * List of emails
	 *
	 * @access   public
	 * @var      $email_list    emails
	 */
    public $email_list = array();

    /**
	 * WC_Emails class instance
	 *
	 * @access   public
	 * @var      $woo_mailer  class instance
	 */
    public $woo_mailer = null;

    /**
	 * Manages the list of WooCommerce default admin emails
	 *
	 * @access   public
	 * @var      $woo_mailer  admin email list
	 */
    public $admin_emails = array();

    /**
	 * Construct
	 */
	public function __construct( $plugin_name, $version ) {
		$this->plugin_name = $plugin_name;
		$this->version = $version;
		add_action('after_setup_theme', array($this, 'define_public_hooks'));
		add_filter('wc_order_status_manager_order_status_email_replace_variables', array($this, 'prepare_status_name'), 999, 4);
	}

	/**
	 * Helper function to store the order status manager email status
	 *
	 * @param  string $template_name template name
	 * @param  array $current_lang current wpml language
	 * @return string template file
	 */	
	public function prepare_status_name( $find, $id, $type, $object ){
		if( !empty( $id ) ){
			$this->wc_status_manager['id'] = $id;
		}
		return $find;	
	}

	/**
	 * Enqueue styles and scripts
	 *
	 */	
	public function enqueue_styles_and_scripts() {
		global $wp_scripts;
		
		if(is_product()){
			$debug_mode = apply_filters('thwec_debug_mode', false);
			$suffix = $debug_mode ? '' : '.min';
			$jquery_version = isset($wp_scripts->registered['jquery-ui-core']->ver) ? $wp_scripts->registered['jquery-ui-core']->ver : '1.9.2';
		}
	}
	
	/**
	 * Define functionalities for the public end
	 *
	 */	
	public function define_public_hooks(){
		$wc_hook_priority = apply_filters('wc_get_template_priority', 10);
		add_filter('woocommerce_email_styles', array($this, 'th_woocommerce_email_styles') );
		add_filter( 'wc_get_template', array( $this, 'get_woo_template'), $wc_hook_priority, 5 );
		add_filter( 'woocommerce_email_attachments', array($this, 'thwec_woo_attachments'), 10, 4 );
		
		$this->settings = THWEC_Utils::get_template_settings();
		$this->template_map = THWEC_Utils::get_template_map( $this->settings );
		$this->render_subject_filters();
		$this->wpml_active = THWEC_Utils::is_wpml_active();
		if( $this->wpml_active ){
			$this->wpml_default_lang = THWEC_Utils::get_wpml_locale( apply_filters( 'wpml_default_language', NULL ), true );
		}
		$this->admin_emails = array('new_order', 'failed_order', 'cancelled_order');
		$this->email_list = THWEC_Utils::email_statuses();
		if( THWEC_Utils::is_ywgc_active() ){
			$this->email_list = array_merge( $this->email_list, THWEC_Utils::ywgc_emails() );
		}
		$this->woo_mailer = WC_Emails::instance();
	}

	/**
	 * Locate template file for the default wpml language
	 *
	 * @param  string $template_name template name
	 * @return string template file
	 */	
	public function get_default_langauge_template( $template_name ){
		$template = $template_name.'-'.$this->wpml_default_lang;
		$path = $this->get_email_template_path($template);
		return $path;

	}

	/**
	 * Helper function to locate the template for email notification
	 *
	 * @param  string $template_name template name
	 * @param  array $current_lang current wpml language
	 * @return string template file
	 */	
	public function get_thwec_template_file( $template_name, $current_lang ){
		if( $this->wpml_active ){
			if( $this->wpml_default_lang == $current_lang ){
				$custom_path = $this->get_default_langauge_template( $template_name );
				if( $custom_path ){
					return $custom_path;
				}
			}else{
				$lang_template = $template_name.'-'.$current_lang;
				$custom_path = $this->get_email_template_path($lang_template);
				if( $custom_path ){
					return $custom_path;
				}else{
					$custom_path = $this->get_default_langauge_template( $template_name );
					if( $custom_path ){
						return $custom_path;
					}
				}
			}
		}
			
		$lang_template = $template_name.'-'.$current_lang;
		$custom_path = $this->get_email_template_path($lang_template);
		if( $custom_path ){
			return $custom_path;
		}
		$custom_path = $this->get_email_template_path( $template_name );
		return $custom_path;
	}

	/**
	 * Get the email status keys
	 *
	 * @param  array $map email template map
	 * @return array email status keys
	 */	
	public function get_emails_list( $map ){
		return is_array( $map ) && !empty( $map ) ? array_keys( $map ) : array();
	}

	/**
	 * Locate template file for the email notification
	 *
	 * @param  string $template template
	 * @param  array $template_name template name
	 * @param  string $template_path path of template
	 * @return string template file
	 */	
	
	public function get_woo_template( $template, $template_name, $args, $template_path, $default_path ){
		$template_key_order_status = '';
		$template_map = THWEC_Utils::get_template_map();
		$current_lang = strtolower(get_locale());
		$email_list = $this->get_emails_list( $template_map );

		if( isset( $_GET['page'] ) && $_GET['page'] == sanitize_text_field( 'th_email_customizer_pro') && isset( $_GET['preview'] ) ){
			return $template;
		}
		
		$template_id = isset($args['email']) && isset($args['email']->id) && !empty($args['email']->id) ? $args['email']->id : false;
		
		$template_key = !isset( $args['gift_card'] ) ? str_replace('_', '-', $template_id) : $template_id;
		if( $template_id && is_array( $template_map ) ){ 
			
			$template_key = in_array($template_id, $this->admin_emails) ? 'admin-'.$template_key : $template_key;

		    if( $this->should_locate_template( $template_key, $template_id, $template_name, $email_list, isset( $args['gift_card'] ) ) ){

		    	$locate = $this->locate_template_file( $template_key, $template_map, $current_lang );
	    		if( $locate ){
	    			return $locate;
	    		}else {
	    			$locate = $this->locate_template_file( $template_id, $template_map, $current_lang );
	    			if( $locate ){
	    				return $locate;
	    			}
	    		}
	    		$this->manage_woocommerce_hooks( false );
		    }
		}
		return $template;
	}

	public function should_locate_template( $template_key, $template_id, $template_name, $email_list, $ywgc_email ){

		if($template_name === "emails/email-order-details.php" || $template_name === "emails/email-downloads.php"){
			return false;
		}
		
		$template_name = str_replace( array('emails/', '.php'), array('',''), $template_name );
		
		if( $this->if_thwecm_email( $template_key, $template_name, $ywgc_email ) && in_array( $template_key, $email_list ) ){
			return true;

		}else if( $this->if_thwecm_email( $template_id, $template_name, $ywgc_email ) && in_array( $template_id, $email_list ) ){
			return true;

		}
		return false;
	}

	public function if_thwecm_email( $key, $name, $ywgc_email ){
		if( $key === "customer-partially-refunded-order" ){
			$key = $name;
		}
	    if( $name === 'customer-order-status-email' || $name === 'admin-order-status-email' || $key === $name ){
	        return true;
	    }else if( $ywgc_email && str_replace('ywgc-email-', '', $key) === $name ) {
	        return true;
	    }
	    return false;
	}

	/**
	 * Locate template file for the email notification
	 *
	 * @param  string $template_key template name key
	 * @param  array $template_map template settings array
	 * @param  string $current_lang current wpml language
	 * @return string template path found or boolean if not
	 */	
	public function locate_template_file( $template_key, $template_map, $current_lang ){
		$custom_path = false;
		if( array_key_exists($template_key, $template_map) ){
    		$template_name_new = $template_map[$template_key];
    		if( $template_name_new != '' ){
        		$custom_path = $this->get_thwec_template_file( $template_name_new, $current_lang );
    			if($custom_path){
    				return $custom_path;
    			}
    		}		
    	}
    	return $custom_path;
	}

	/**
	 * Additional styles for email templates
	 *
	 * Default styles include styles based on an element id #body_content. That element is not used in our template. So use those styles that require #body_element here.
	 *
	 *Styles added here are only for content that woocommerce render directly Or the woocommerce functions that are used in template.
	 *
	 * @param  string $subject default email subject
	 * @return string template path found or boolean if not
	 */	
	public function th_woocommerce_email_styles($buffer){
		$css = '';
		$styles = $this->wecmf_template_css_compatibility(); 
		$styles .= THWEC_Utils::wecm_email_styles();
		$styles.= apply_filters('thwec_woo_css_override', $css);
		return $buffer.$styles;
	}

	/**
	 * Free version compatible styles
	 *
	 * Free version templates that are not edited in the premium version (but assigned to email) atleast once, require free version styles.
	 *
	 * @return string styles
	 */	
	public function wecmf_template_css_compatibility(){
		$styles = '#tpf_t_builder #template_container,#tpf_t_builder #template_header,#tpf_t_builder #template_body,#tpf_t_builder #template_footer{width:100% !important;}';
		$styles.= '#tpf_t_builder #template_container{width:100% !important;border:0px none transparent !important;}';
		$styles .= '#tpf_t_builder #body_content > table:first-child > tbody > tr > td{padding:15px 0px !important;}'; //To remove the padding after header when woocommerce header hook used in template (48px 48px 0px) 
		$styles.= '#tpf_t_builder #wrapper{padding:0;background-color:transparent;}';
		$styles.= '#tpf_t_builder .thwec-block-text-holder a{color: #1155cc !important;}';
		$styles.= '#tpf_t_builder .thwecmf-columns p{color:#636363;font-size:14px;}';
		$styles.= '#tpf_t_builder .thwecmf-columns .td .td{padding:12px;}';
		$styles.= '#tpf_t_builder .thwecmf-columns .address{font-size:14px;}';
		return $styles;
	}

	/**
	 * Template path for the email notification
	 *
	 * @param  string $t_name template name
	 * @return string template path found or boolean if not
	 */	
	public function get_email_template_path($t_name){
    	$tpath = false;
    	$email_template_path = THWEC_CUSTOM_TEMPLATE_PATH.$t_name.'.php';
    	if(file_exists($email_template_path)){
    		$this->manage_woocommerce_hooks(true);
    	   	$tpath = $email_template_path;
    	}
    	return $tpath;
    }

    /**
	 * Attachments for WooCommerce emails
	 *
	 */	
    public function thwec_woo_attachments($attachments, $email_id, $order, $email){
    	$email_attachments = isset($this->settings[THWEC_Utils::get_email_attachment_key()]) ? $this->settings[THWEC_Utils::get_email_attachment_key()] : array();
    	$email_attachment = isset($email_attachments[$email_id]) ? $email_attachments[$email_id] : false;
    	if( is_array( $email_attachment ) ){
    		foreach ($email_attachment as $index => $attachment) {
    			if( isset($attachment['file_url']) ){
    				$attachments[] = $attachment['file_url'];
    			}		
    		}
    	}
    	return $attachments;
    }

    /**
	 * Manage subjects for supported email notifications
	 *
	 */	
    public function render_subject_filters(){

    	$this->subjects = THWEC_Utils::get_template_subject( $this->settings );
    	$order_status = THWEC_Utils::get_compatibility( $this->settings );

    	add_filter('woocommerce_email_subject_new_order', array($this, 'thwec_email_subject_new_order'), 1, 2);
    	add_filter('woocommerce_email_subject_customer_processing_order', array($this, 'thwec_email_subject_processing'), 1, 2);
    	add_filter('woocommerce_email_subject_customer_completed_order', array($this, 'thwec_email_subject_completed'), 1, 2);
    	add_filter('woocommerce_email_subject_customer_invoice', array($this, 'thwec_email_subject_invoice'), 1, 2);
    	add_filter('woocommerce_email_subject_customer_note', array($this, 'thwec_email_subject_customer_note'), 1, 2);
    	add_filter('woocommerce_email_subject_customer_new_account', array($this, 'thwec_email_subject_new_account'), 1, 2);
    	add_filter('woocommerce_email_subject_customer_on_hold_order', array($this, 'thwec_email_subject_on_hold'), 1, 2);
    	add_filter('woocommerce_email_subject_cancelled_order', array($this, 'thwec_email_subject_cancelled'), 1, 2);
    	if( THWEC_Utils::woo_version_check('3.5.0') ){
    		add_filter('woocommerce_email_subject_customer_refunded_order', array($this, 'thwec_email_subject_refunded_with_partial'), 1, 3);
    	}else{
    		add_filter('woocommerce_email_subject_customer_refunded_order', array($this, 'thwec_email_subject_refunded'), 1, 2);
    	}
    	add_filter('woocommerce_email_subject_failed_order', array($this, 'thwec_email_subject_failed'), 1, 2);
    	add_filter('woocommerce_email_subject_customer_reset_password', array($this, 'thwec_email_subject_reset_password'), 1, 2);

    	if( in_array( 'yith-woocommerce-gift-cards-premium', THWEC_Utils::compatible_plugins() ) ){
    		add_filter('woocommerce_email_subject_ywgc-email-delivered-gift-card', array($this, 'thwec_email_subject_ywgc_delivered'), 1, 2);
    		add_filter('woocommerce_email_subject_ywgc-email-notify-customer', array($this, 'thwec_email_subject_ywgc_notify'), 1, 2);
    		add_filter('woocommerce_email_subject_ywgc-email-send-gift-card', array($this, 'thwec_email_subject_ywgc_send'), 1, 2);
    	}

    	if( isset( $order_status['wc-order-status-manager'] ) && is_array( $order_status['wc-order-status-manager'] ) ){
    		foreach ($order_status['wc-order-status-manager'] as $key => $value) {
    			if( isset( $this->subjects[$value] ) && !empty( $this->subjects[$value] ) ){
    				add_filter('woocommerce_email_subject_'.$value, array($this, 'thwec_wc_order_status_manager'), 1, 2);
    			}
    		}
    	}
    }

    /**
	 * Subject for WooCommerce order status manager email notifications
	 *
	 * @param  string $subject default email subject
	 * @param  object $order Order
	 * @return string modified subject
	 */	
    public function thwec_wc_order_status_manager( $subject, $order ){
    	$id = isset( $this->wc_status_manager['id'] ) ? $this->wc_status_manager['id'] : false;
    	if( $id && isset( $this->subjects[$id] ) ){
    		$subject = THWEC_Utils::format_subjects( $this->subjects[$id], '', $order );
    	}
    	return $subject;
    }

    /**
	 * Override WooCommerce email header and footer hooks
	 *
	 * @param  $found whether the template is from plugin. default template if false.
	 */	
    public function manage_woocommerce_hooks( $found ){
		if( $found ){
			$this->execute_email_action_hooks( 'woocommerce_email_header', 'email_header', $this->woo_mailer, false );
			$this->execute_email_action_hooks( 'woocommerce_email_footer', 'email_footer', $this->woo_mailer, false );
			$this->execute_email_action_hooks( 'woocommerce_email_header', 'thwec_email_header', 'THWEC_Utils', true );
			$this->execute_email_action_hooks( 'woocommerce_email_footer', 'thwec_email_footer', 'THWEC_Utils', true );

		}else{
			$this->execute_email_action_hooks( 'woocommerce_email_header', 'email_header', $this->woo_mailer, true );
			$this->execute_email_action_hooks( 'woocommerce_email_footer', 'email_footer', $this->woo_mailer, true );
			$this->execute_email_action_hooks( 'woocommerce_email_header', 'thwec_email_header', 'THWEC_Utils', false );
			$this->execute_email_action_hooks( 'woocommerce_email_footer', 'thwec_email_footer', 'THWEC_Utils', false );
		}
	}

	/**
	 * Add or remove callback to action hook.
	 *
	 * @param  $action name of the action hook
	 * @param  $callback callback function for the action
	 * @param  $obj Email class instance / current class object
	 * @param  $add whether to add or remove the hook callback
	 */	
	private function execute_email_action_hooks( $action, $callback, $obj, $add ){

		if( $add ){
			if( is_object($obj ) ){
				remove_action( $action, array( $obj, $callback ) );
				add_action( $action, array( $obj, $callback ), 10, 2);
			}else{
				remove_action( $action, 'THWEC_Utils::'.$callback );
				add_action( $action, 'THWEC_Utils::'.$callback, 10, 2 );
			}
			
		}

		if( !$add ){
			if( is_object( $obj ) ){
				remove_action( $action, array( $obj, $callback ) );
			}else{
				remove_action( $action, 'THWEC_Utils::'.$callback );
			}
		}
	}

    /**
	 * Subject for new order email notification
	 *
	 * @param  string $subject default email subject
	 * @param  object $order Order
	 * @return string modified subject
	 */	
    public function thwec_email_subject_new_order( $subject, $order ){
    	return $this->format_email_subject( $subject, 'admin-new-order', $order );
    }
    
    /**
	 * Subject for processing order email notification
	 *
	 * @param  string $subject default email subject
	 * @param  object $order Order
	 * @return string modified subject
	 */	
    public function thwec_email_subject_processing( $subject, $order ){
		return $this->format_email_subject( $subject, 'customer-processing-order', $order );
    }
	
	/**
	 * Subject for completed order email notification
	 *
	 * @param  string $subject default email subject
	 * @param  object $order Order
	 * @return string modified subject
	 */	
	public function thwec_email_subject_completed( $subject, $order ){
		return $this->format_email_subject( $subject, 'customer-completed-order', $order );
	}

	/**
	 * Subject for invoice email notification
	 *
	 * @param  string $subject default email subject
	 * @param  object $order Order
	 * @return string modified subject
	 */	
	public function thwec_email_subject_invoice( $subject, $order ){
		return $this->format_email_subject( $subject, 'customer-invoice', $order );
	}
	
	/**
	 * Subject for customer note email notification
	 *
	 * @param  string $subject default email subject
	 * @param  object $order Order
	 * @return string modified subject
	 */	
	public function thwec_email_subject_customer_note( $subject, $order ){
		return $this->format_email_subject( $subject, 'customer-note', $order );
	}
	
	/**
	 * Subject for new account email notification
	 *
	 * @param  string $subject default email subject
	 * @param  object $order Order
	 * @return string modified subject
	 */	
	public function thwec_email_subject_new_account( $subject, $order ){
		return $this->format_email_subject( $subject, 'customer-new-account', $order );
	}
	
	/**
	 * Subject for on hold email notification
	 *
	 * @param  string $subject default email subject
	 * @param  object $order Order
	 * @return string modified subject
	 */	
	public function thwec_email_subject_on_hold( $subject, $order ){
		return $this->format_email_subject( $subject, 'customer-on-hold-order', $order );
	}
	
	/**
	 * Subject for cancelled order email notification
	 *
	 * @param  string $subject default email subject
	 * @param  object $order Order
	 * @return string modified subject
	 */	
	public function thwec_email_subject_cancelled( $subject, $order ){
		return $this->format_email_subject( $subject, 'admin-cancelled-order', $order );
	}
	
	/**
	 * Subject for partially and fully refunded order email notification
	 *
	 * @param  string $subject default email subject
	 * @param  object $order Order
	 * @return string modified subject
	 */	
	public function thwec_email_subject_refunded_with_partial( $subject, $order, $email ){
		if( isset($email->id) && $email->id === "customer_partially_refunded_order" ){
			return $this->format_email_subject( $subject, 'customer-partially-refunded-order', $order );
		}
		return $this->format_email_subject( $subject, 'customer-refunded-order', $order );
	}

	/**
	 * Subject for refunded order email notification
	 *
	 * @param  string $subject default email subject
	 * @param  object $order Order
	 * @return string modified subject
	 */	
	public function thwec_email_subject_refunded( $subject, $order ){
		return $this->format_email_subject( $subject, 'customer-refunded-order', $order );
	}
	
	/**
	 * Subject for failed order email notification
	 *
	 * @param  string $subject default email subject
	 * @param  object $order Order
	 * @return string modified subject
	 */	
	public function thwec_email_subject_failed( $subject, $order ){
		return $this->format_email_subject( $subject, 'admin-failed-order', $order );
	}
	
	/**
	 * Subject for reset password email notification
	 *
	 * @param  string $subject default email subject
	 * @param  object $order Order
	 * @return string modified subject
	 */	
	public function thwec_email_subject_reset_password( $subject, $order ){
		return $this->format_email_subject( $subject, 'customer-reset-password', $order );
	}

	/**
	 * Subject for YITH Gift Card delivered notification
	 *
	 * @param  string $subject default email subject
	 * @param  object $order Order
	 * @return string modified subject
	 */	
	public function thwec_email_subject_ywgc_delivered($subject, $order){
		return $this->format_email_subject( $subject, 'ywgc-email-delivered-gift-card', $order );
	}

	/**
	 * Subject for YITH Gift Card customer notification
	 *
	 * @param  string $subject default email subject
	 * @param  object $order Order
	 * @return string modified subject
	 */	
	public function thwec_email_subject_ywgc_notify($subject, $order){
		return $this->format_email_subject( $subject, 'ywgc-email-notify-customer', $order );
	}

	/**
	 * Subject for send YITH Gift Card email
	 *
	 * @param  string $subject default email subject
	 * @param  object $order Order
	 * @return string modified subject
	 */	
	public function thwec_email_subject_ywgc_send($subject, $order){
		return $this->format_email_subject( $subject, 'ywgc-email-send-gift-card', $order );
	}

	/**
	 * Replace placeholders in email subjects with dynamic data
	 *
	 * @param  string $subject default email subject
	 * @param  string $status email status
	 * @param  object $order Order
	 * @return string $subject modified subject
	 */	
	public function format_email_subject( $subject, $status, $order ){
		if( isset( $this->subjects[$status] ) && !empty( $this->subjects[$status] ) ){
			$subject = THWEC_Utils::format_subjects( $this->subjects[$status], $status, $order );
		}
		return $subject;
	}
}
endif;