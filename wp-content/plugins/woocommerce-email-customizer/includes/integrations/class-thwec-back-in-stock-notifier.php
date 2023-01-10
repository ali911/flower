<?php
/**
 * The admin template settings page for managing the third party integrations.
 *
 * @link       https://themehigh.com
 * @since      3.6.0
 *
 * @package    woocommerce-email-customizer-pro
 * @subpackage woocommerce-email-customizer-pro/admin
 */
if(!defined('WPINC')){	die; }

if(!class_exists('THWEC_Back_In_Stock_Notifier')):

class THWEC_Back_In_Stock_Notifier{

	/**
	 * Main instance of the class
	 *
	 * @access   protected
	 * @var      $_instance    
	 */
	protected static $_instance = null;

	/**
	 * WC_Emails class instance
	 *
	 * @access   public
	 * @var      $woo_mailer  class instance
	 */
    public $woo_mailer = null;

    /**
	 * Manages the email customizer settings
	 *
	 * @access   private
	 * @var      $settings    array of settings for customizer
	 */
    private $settings = array();

     /**
	 * Determines whether the in stock email is assigned the default template
	 *
	 * @access   private
	 * @var      $default_instock_email    boolean default template or not
	 */
    private $default_instock_email = true;

     /**
	 * Determines whether the subscriber email is assigned the default template
	 *
	 * @access   private
	 * @var      $default_subscriber_email    boolean default template or not
	 */
    private $default_subscriber_email = true;

    /**
	 * The sample template settings of the back in stock notifier plugin
	 *
	 * @access   private
	 * @var      $default_subscriber_email    boolean default template or not
	 */
    private $sample_templates = array();

	/**
	 * Construct
	 */
	public function __construct() {
		$this->init_variables();
		$this->have_default_templates();
		$this->remove_default_hooks();
		add_filter('thwec_email_statuses', array($this, 'prepare_emails'), 10, 1);
		add_filter('thwec_default_email_subjects', array($this, 'default_email_subject'), 10, 1);
		add_filter('thwec_template_globals', array($this, 'add_template_global_variables'), 10, 1);
		add_filter('thwec_email_placeholders', array($this, 'prepare_placeholders'));
		add_filter('thwec_sample_templates', array($this, 'add_to_sample_templates'), 10, 2);
		add_filter('thwec_sample_template_listing', array($this, 'add_to_sample_template_listing'), 10, 1);

		// add_filter('thwec_get_script_data', array($this, 'prepare_script_data'));
		
		add_filter( 'cwginstock_message', array( $this, 'cwginstock_message' ), 100, 2 );
		add_filter( 'cwgsubscribe_message', array( $this, 'cwgsubscribe_message' ), 100, 2 );
		add_filter('cwginstock_subject', array($this, 'cwginstock_subject'), 10, 2);
		add_filter('cwgsubscribe_subject', array($this, 'cwgsubscribe_subject'), 10, 2);
	}

	private function init_variables(){
		$this->settings = THWEC_Utils::get_template_settings();
		$this->template_map = THWEC_Utils::get_template_map( $this->settings );
		$this->subjects = THWEC_Utils::get_template_subject( $this->settings );
		$this->sample_templates = $this->get_sample_templates();
	}
	
	/**
	 * Main THWEC_Back_In_Stock_Notifier Instance.
	 *
	 * Ensures only one instance of THWEC_Back_In_Stock_Notifier is loaded or can be loaded.
	 *
	 * @since 1.0
	 * @static
	 * @return THWEC_Back_In_Stock_Notifier Main instance
	 */
	public static function instance() {
		if(is_null(self::$_instance)){
			self::$_instance = new self();
		}
		return self::$_instance;
	}
	
	public function prepare_emails($emails){
		$instock_emails = array(
			'notifier_instock_mail'		=> 'Instock Email notification', 
			'notifier_subscribe_mail'	=> 'Subscriber new email'
		);
		return array_merge($emails,$instock_emails);
	}

	public function prepare_placeholders($email_content){
		$email_content = str_replace('{cwgisn_subscriber_name}', $this->getcwg_subscriber_name(), $email_content);
		$email_content = str_replace('{cwgisn_subscribed_product_name}', $this->getcwg_product_name(), $email_content);

		$email_content = str_replace('{cwgisn_subscribed_product_cart_link}', $this->getcwg_product_cart_url(), $email_content);
		// $email_content = str_replace('{yith_gift_card_name}', $this->get_ywgc_name(), $email_content);
		return $email_content;
	}

	public function prepare_script_data(){
		
	}

	public function action_remove_header_footer_instock(){
		if( !$this->default_instock_email ){
			$this->action_remove_header_footer();
		}
	}
	public function action_remove_header_footer_subscriber(){
		if( !$this->default_subscriber_email){
			$this->action_remove_header_footer();
		}
	}

	private function action_remove_header_footer(){
		$this->woo_mailer = WC_Emails::instance();
		remove_action( 'woocommerce_email_header', array( $this->woo_mailer, 'email_header' ) );
		remove_action( 'woocommerce_email_footer', array( $this->woo_mailer, 'email_footer' ) );
	}

	public function cwginstock_message($message, $subscriber_id){
		$template = isset($this->template_map['notifier_instock_mail']) ? $this->template_map['notifier_instock_mail'] : false;
		if( $template ){
			$template = $this->get_email_template_path($template, true);
			ob_start();
			include $template;
			return ob_get_clean();
		}
		return $message;
	}

	public function cwgsubscribe_message($message, $subscriber_id){
		$template = isset($this->template_map['notifier_subscribe_mail']) ? $this->template_map['notifier_subscribe_mail'] : false;
		if( $template ){
			$template = $this->get_email_template_path($template, true);
			ob_start();
			include $template;
			return ob_get_clean();
		}
		return $message;
	}

	public function cwginstock_subject($subject, $subscriber_id){
		$thwec_subject = isset($this->subjects['notifier_instock_mail']) ? $this->subjects['notifier_instock_mail'] : false;
		if( $thwec_subject ){
			$thwec_subject = $this->prepare_subject_placeholders($thwec_subject, $subscriber_id);
			return $thwec_subject;
		}
		return $subject;
	}

	public function cwgsubscribe_subject($subject, $subscriber_id){
		$thwec_subject = isset($this->subjects['notifier_subscribe_mail']) ? $this->subjects['notifier_subscribe_mail'] : false;
		if( $thwec_subject ){
			$thwec_subject = $this->prepare_subject_placeholders($thwec_subject, $subscriber_id);
			return $thwec_subject;
		}
		return $subject;
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
    		// $this->remove_default_hooks();
    	   	$tpath = $email_template_path;
    	}
    	return $tpath;
    }

    public function prepare_subject_placeholders($subject, $subscriber_id){
    	$cwgisn = new CWG_Instock_API();
    	$placeholders = array();
    	$placeholders['{site_name}'] = get_bloginfo();
    	$placeholders['{cwgisn_subscriber_name}'] = $cwgisn->get_subscriber_name($subscriber_id);
    	$placeholders['{cwgisn_subscribed_product_name}'] = $cwgisn->display_product_name($subscriber_id);
    	$subject = THWEC_Utils::add_dynamic_data($placeholders, $subject);
    	return $subject;
    }

    private function remove_default_hooks(){
    	//Send instock email individually from dashboard
    	add_action( 'admin_action_cwginstock-sendmail', array( $this, 'action_remove_header_footer_instock' ), 9 );

    	//Send instock email when product stock status changed to instock in edit product page
		add_action( 'cwginstock_notify_process', array( $this, 'action_remove_header_footer_instock' ), 9 );

		//send bluk instock email from dashboard
		add_action( 'cwginstocknotifier_handle_action_send_mail', array( $this, 'action_remove_header_footer_instock' ), 9 );

		//On subscribing an outofstock product
		add_action( 'cwginstock_after_insert_subscriber', array( $this, 'action_remove_header_footer_subscriber' ), 9 );
    }

    public function add_template_global_variables(){
    	$content = '<?php if( class_exists(\'CWG_Instock_API\') ): ';
    	$content .= '$CWG_OBJ = new CWG_Instock_API();';
    	$content .= 'endif; ?>';
    	return $content;
    }

    private function getcwg_subscriber_name(){
    	$content = '<?php if(isset($subscriber_id) && isset($CWG_OBJ)): ?>';
    	$content .= '<?php echo $CWG_OBJ->get_subscriber_name($subscriber_id); ?>';
    	$content .= '<?php endif; ?>';
    	return $content;
    }

    private function getcwg_product_name(){
		$content = '<?php if(isset($subscriber_id) && isset($CWG_OBJ)): ?>';
		$content .= '<?php echo $CWG_OBJ->display_product_name($subscriber_id); ?>';
		$content .= '<?php endif; ?>';
		return $content;
	}

	private function getcwg_product_cart_url(){
		$content = '<?php if(isset($subscriber_id) && isset($CWG_OBJ)): ?>';
		$content .= '<?php echo $CWG_OBJ->get_cart_link($subscriber_id); ?>';
		$content .= '<?php endif; ?>';
		return $content;
	}

	public function default_email_subject($subjects){
		$subjects['notifier_instock_mail'] = 'Product {cwgisn_subscribed_product_name} has back in stock';
		$subjects['notifier_subscribe_mail'] = 'You subscribed to {cwgisn_subscribed_product_name} at ThPro';
		return $subjects;
	}

	private function have_default_templates(){
		$notifier_instock_mail = isset($this->template_map['notifier_instock_mail']) ? $this->template_map['notifier_instock_mail'] : '';
		$notifier_subscribe_mail = isset($this->template_map['notifier_subscribe_mail']) ? $this->template_map['notifier_subscribe_mail'] : '';

		if( !empty($notifier_instock_mail) ){
			$this->default_instock_email = false;
		} 
		if( !empty($notifier_subscribe_mail) ){
			$this->default_subscriber_email=false;
		}
	}

	private function get_sample_templates(){
		$templates = $this->get_templates();
		$templates = THWEC_Utils::get_compatible_plugin_samples($templates);
		return $templates;
	}

	public function add_to_sample_templates($settings){
		if( isset($this->sample_templates['notifier_instock_mail'] ) ){
			$settings['thwec_samples']['notifier_instock_mail'] = $this->sample_templates['notifier_instock_mail'];
		}
		if( isset($this->sample_templates['notifier_subscribe_mail'] ) ){
			$settings['thwec_samples']['notifier_subscribe_mail'] = $this->sample_templates['notifier_subscribe_mail'];
		}
		return $settings;
	}

	public function add_to_sample_template_listing($settings){
		if( isset($this->sample_templates['notifier_instock_mail'] ) ){
			$settings['notifier_instock_mail'] = $this->sample_templates['notifier_instock_mail'];
		}
		if( isset($this->sample_templates['notifier_subscribe_mail'] ) ){
			$settings['notifier_subscribe_mail'] = $this->sample_templates['notifier_subscribe_mail'];
		}
		return $settings;
	}

	private function get_templates(){
		return 'YToxOntzOjk6InRlbXBsYXRlcyI7YToyOntzOjIxOiJub3RpZmllcl9pbnN0b2NrX21haWwiO2E6Mzp7czoxMjoiZGlzcGxheV9uYW1lIjtzOjI2OiJJbnN0b2NrIEVtYWlsIE5vdGlmaWNhdGlvbiI7czoxMzoidGVtcGxhdGVfZGF0YSI7czo0NDA4OiJ7ImNvbnRlbnRzIjpbeyJkYXRhX2lkIjoidGJfMTAwMSIsImRhdGFfdHlwZSI6InJvdyIsImRhdGFfbmFtZSI6Im9uZV9jb2x1bW4iLCJkYXRhX2NzcyI6eyJoZWlnaHQiOiIiLCJib3JkZXJfc3BhY2luZyI6IjBweCIsInBhZGRpbmdfdG9wIjoiMHB4IiwicGFkZGluZ19yaWdodCI6IjBweCIsInBhZGRpbmdfYm90dG9tIjoiMHB4IiwicGFkZGluZ19sZWZ0IjoiMHB4IiwibWFyZ2luX3RvcCI6IjBweCIsIm1hcmdpbl9yaWdodCI6ImF1dG8iLCJtYXJnaW5fYm90dG9tIjoiMHB4IiwibWFyZ2luX2xlZnQiOiJhdXRvIiwiYm9yZGVyX3dpZHRoX3RvcCI6IjBweCIsImJvcmRlcl93aWR0aF9yaWdodCI6IjBweCIsImJvcmRlcl93aWR0aF9ib3R0b20iOiIwcHgiLCJib3JkZXJfd2lkdGhfbGVmdCI6IjBweCIsImJvcmRlcl9zdHlsZSI6Im5vbmUiLCJib3JkZXJfY29sb3IiOiIiLCJ1cGxvYWRfYmdfdXJsIjoiIiwiYmdfY29sb3IiOiIiLCJiZ19wb3NpdGlvbiI6ImNlbnRlciIsImJnX3NpemUiOiIxMDAlIiwiYmdfcmVwZWF0Ijoibm8tcmVwZWF0In0sImRhdGFfY291bnQiOjEsImRhdGFfY29sdW1ucyI6WyJ0Yl8xMDAyIl0sImNoaWxkIjpbeyJkYXRhX2lkIjoidGJfMTAwMiIsImRhdGFfdHlwZSI6ImNvbHVtbiIsImRhdGFfbmFtZSI6Im9uZV9jb2x1bW5fb25lIiwiZGF0YV9jc3MiOnsid2lkdGgiOiIxMDAlIiwicGFkZGluZ190b3AiOiIwcHgiLCJwYWRkaW5nX3JpZ2h0IjoiMHB4IiwicGFkZGluZ19ib3R0b20iOiIwcHgiLCJwYWRkaW5nX2xlZnQiOiIwcHgiLCJ0ZXh0X2FsaWduIjoibGVmdCIsImJvcmRlcl93aWR0aF90b3AiOiIwcHgiLCJib3JkZXJfd2lkdGhfcmlnaHQiOiIwcHgiLCJib3JkZXJfd2lkdGhfYm90dG9tIjoiMHB4IiwiYm9yZGVyX3dpZHRoX2xlZnQiOiIwcHgiLCJib3JkZXJfc3R5bGUiOiJub25lIiwiYm9yZGVyX2NvbG9yIjoiIiwidXBsb2FkX2JnX3VybCI6IiIsImJnX2NvbG9yIjoiIiwiYmdfcG9zaXRpb24iOiJjZW50ZXIiLCJiZ19zaXplIjoiMTAwJSIsImJnX3JlcGVhdCI6Im5vLXJlcGVhdCIsInZlcnRpY2FsX2FsaWduIjoidG9wIn0sImNoaWxkIjpbeyJkYXRhX2lkIjoidGJfMTAwNSIsImRhdGFfdHlwZSI6ImVsZW1lbnQiLCJkYXRhX25hbWUiOiJ0ZXh0IiwiZGF0YV9jc3MiOnsiY29sb3IiOiIjZmZmZmZmIiwiYWxpZ24iOiJjZW50ZXIiLCJmb250X3NpemUiOiIzMHB4IiwibGluZV9oZWlnaHQiOiIxNTAlIiwiZm9udF93ZWlnaHQiOiIzMDAiLCJmb250X2ZhbWlseSI6ImhlbHZldGljYSIsImJnX2NvbG9yIjoiIzk2NTg4YSIsInVwbG9hZF9iZ191cmwiOiIiLCJiZ19zaXplIjoiMTAwJSIsImJnX3Bvc2l0aW9uIjoiY2VudGVyIiwiYmdfcmVwZWF0Ijoibm8tcmVwZWF0IiwiYm9yZGVyX3dpZHRoX3RvcCI6IjBweCIsImJvcmRlcl93aWR0aF9yaWdodCI6IjBweCIsImJvcmRlcl93aWR0aF9ib3R0b20iOiIwcHgiLCJib3JkZXJfd2lkdGhfbGVmdCI6IjBweCIsImJvcmRlcl9jb2xvciI6IiIsImJvcmRlcl9zdHlsZSI6Im5vbmUiLCJzaXplX3dpZHRoIjoiMTAwJSIsInNpemVfaGVpZ2h0IjoiIiwibWFyZ2luX3RvcCI6IjBweCIsIm1hcmdpbl9yaWdodCI6ImF1dG8iLCJtYXJnaW5fYm90dG9tIjoiMHB4IiwibWFyZ2luX2xlZnQiOiJhdXRvIiwicGFkZGluZ190b3AiOiI0OHB4IiwicGFkZGluZ19yaWdodCI6IjQ4cHgiLCJwYWRkaW5nX2JvdHRvbSI6IjQ4cHgiLCJwYWRkaW5nX2xlZnQiOiI0OHB4IiwidGV4dF9hbGlnbiI6ImxlZnQiLCJ0ZXh0YXJlYV9jb250ZW50IjoiIn0sImRhdGFfdGV4dCI6eyJ0ZXh0YXJlYV9jb250ZW50IjoiUHJvZHVjdCB7Y3dnaXNuX3N1YnNjcmliZWRfcHJvZHVjdF9uYW1lfSBoYXMgYmFjayBpbiBzdG9jayJ9fV19XX0seyJkYXRhX2lkIjoidGJfMTAwMyIsImRhdGFfdHlwZSI6InJvdyIsImRhdGFfbmFtZSI6Im9uZV9jb2x1bW4iLCJkYXRhX2NzcyI6eyJoZWlnaHQiOiIiLCJib3JkZXJfc3BhY2luZyI6IjBweCIsInBhZGRpbmdfdG9wIjoiMHB4IiwicGFkZGluZ19yaWdodCI6IjBweCIsInBhZGRpbmdfYm90dG9tIjoiMHB4IiwicGFkZGluZ19sZWZ0IjoiMHB4IiwibWFyZ2luX3RvcCI6IjBweCIsIm1hcmdpbl9yaWdodCI6ImF1dG8iLCJtYXJnaW5fYm90dG9tIjoiMHB4IiwibWFyZ2luX2xlZnQiOiJhdXRvIiwiYm9yZGVyX3dpZHRoX3RvcCI6IjBweCIsImJvcmRlcl93aWR0aF9yaWdodCI6IjBweCIsImJvcmRlcl93aWR0aF9ib3R0b20iOiIwcHgiLCJib3JkZXJfd2lkdGhfbGVmdCI6IjBweCIsImJvcmRlcl9zdHlsZSI6Im5vbmUiLCJib3JkZXJfY29sb3IiOiIiLCJ1cGxvYWRfYmdfdXJsIjoiIiwiYmdfY29sb3IiOiIiLCJiZ19wb3NpdGlvbiI6ImNlbnRlciIsImJnX3NpemUiOiIxMDAlIiwiYmdfcmVwZWF0Ijoibm8tcmVwZWF0In0sImRhdGFfY291bnQiOjEsImRhdGFfY29sdW1ucyI6WyJ0Yl8xMDA0Il0sImNoaWxkIjpbeyJkYXRhX2lkIjoidGJfMTAwNCIsImRhdGFfdHlwZSI6ImNvbHVtbiIsImRhdGFfbmFtZSI6Im9uZV9jb2x1bW5fb25lIiwiZGF0YV9jc3MiOnsid2lkdGgiOiIxMDAlIiwicGFkZGluZ190b3AiOiI0OHB4IiwicGFkZGluZ19yaWdodCI6IjQ4cHgiLCJwYWRkaW5nX2JvdHRvbSI6IjM1cHgiLCJwYWRkaW5nX2xlZnQiOiI0OHB4IiwidGV4dF9hbGlnbiI6ImNlbnRlciIsImJvcmRlcl93aWR0aF90b3AiOiIwcHgiLCJib3JkZXJfd2lkdGhfcmlnaHQiOiIwcHgiLCJib3JkZXJfd2lkdGhfYm90dG9tIjoiMHB4IiwiYm9yZGVyX3dpZHRoX2xlZnQiOiIwcHgiLCJib3JkZXJfc3R5bGUiOiJub25lIiwiYm9yZGVyX2NvbG9yIjoiIiwidXBsb2FkX2JnX3VybCI6IiIsImJnX2NvbG9yIjoiIiwiYmdfcG9zaXRpb24iOiJjZW50ZXIiLCJiZ19zaXplIjoiMTAwJSIsImJnX3JlcGVhdCI6Im5vLXJlcGVhdCIsInZlcnRpY2FsX2FsaWduIjoidG9wIn0sImNoaWxkIjpbeyJkYXRhX2lkIjoidGJfMTAwNiIsImRhdGFfdHlwZSI6ImVsZW1lbnQiLCJkYXRhX25hbWUiOiJ0ZXh0IiwiZGF0YV9jc3MiOnsiY29sb3IiOiIjNjM2MzYzIiwiYWxpZ24iOiJjZW50ZXIiLCJmb250X3NpemUiOiIxNHB4IiwibGluZV9oZWlnaHQiOiIxNTAlIiwiZm9udF93ZWlnaHQiOiJub3JtYWwiLCJmb250X2ZhbWlseSI6ImhlbHZldGljYSIsImJnX2NvbG9yIjoiIiwidXBsb2FkX2JnX3VybCI6IiIsImJnX3NpemUiOiIxMDAlIiwiYmdfcG9zaXRpb24iOiJjZW50ZXIiLCJiZ19yZXBlYXQiOiJuby1yZXBlYXQiLCJib3JkZXJfd2lkdGhfdG9wIjoiMHB4IiwiYm9yZGVyX3dpZHRoX3JpZ2h0IjoiMHB4IiwiYm9yZGVyX3dpZHRoX2JvdHRvbSI6IjBweCIsImJvcmRlcl93aWR0aF9sZWZ0IjoiMHB4IiwiYm9yZGVyX2NvbG9yIjoiIiwiYm9yZGVyX3N0eWxlIjoibm9uZSIsInNpemVfd2lkdGgiOiIxMDAlIiwic2l6ZV9oZWlnaHQiOiIiLCJtYXJnaW5fdG9wIjoiMHB4IiwibWFyZ2luX3JpZ2h0IjoiYXV0byIsIm1hcmdpbl9ib3R0b20iOiIwcHgiLCJtYXJnaW5fbGVmdCI6ImF1dG8iLCJwYWRkaW5nX3RvcCI6IjBweCIsInBhZGRpbmdfcmlnaHQiOiIwcHgiLCJwYWRkaW5nX2JvdHRvbSI6IjBweCIsInBhZGRpbmdfbGVmdCI6IjBweCIsInRleHRfYWxpZ24iOiJsZWZ0IiwidGV4dGFyZWFfY29udGVudCI6IiJ9LCJkYXRhX3RleHQiOnsidGV4dGFyZWFfY29udGVudCI6IkhlbGxvIHtjd2dpc25fc3Vic2NyaWJlcl9uYW1lfSxcblRoYW5rcyBmb3IgeW91ciBwYXRpZW5jZSBhbmQgZmluYWxseSB0aGUgd2FpdCBpcyBvdmVyIVxuWW91ciBTdWJzY3JpYmVkIFByb2R1Y3Qge2N3Z2lzbl9zdWJzY3JpYmVkX3Byb2R1Y3RfbmFtZX0gaXMgbm93IGJhY2sgaW4gc3RvY2shIFdlIG9ubHkgaGF2ZSBhIGxpbWl0ZWQgYW1vdW50IG9mIHN0b2NrLCBhbmQgdGhpcyBlbWFpbCBpcyBub3QgYSBndWFyYW50ZWUgeW91J2xsIGdldCBvbmUsIHNvIGh1cnJ5IHRvIGJlIG9uZSBvZiB0aGUgbHVja3kgc2hvcHBlcnMgd2hvIGRvXG5BZGQgdGhpcyBwcm9kdWN0IHtjd2dpc25fc3Vic2NyaWJlZF9wcm9kdWN0X25hbWV9IGRpcmVjdGx5IHRvIHlvdXIgY2FydCBcblxue2N3Z2lzbl9zdWJzY3JpYmVkX3Byb2R1Y3RfY2FydF9saW5rfSJ9fV19XX1dLCJsYXN0X2lkIjoxMDA2LCJidWlsZGVyIjp7ImRhdGFfaWQiOiJ0Yl90ZW1wX2J1aWxkZXIiLCJkYXRhX2NzcyI6eyJib3JkZXJfd2lkdGhfdG9wIjoiMXB4IiwiYm9yZGVyX3dpZHRoX3JpZ2h0IjoiMXB4IiwiYm9yZGVyX3dpZHRoX2JvdHRvbSI6IjFweCIsImJvcmRlcl93aWR0aF9sZWZ0IjoiMXB4IiwiYm9yZGVyX3N0eWxlIjoic29saWQiLCJib3JkZXJfY29sb3IiOiIjZjZmN2ZhIiwiYmdfY29sb3IiOiIjZmZmZmZmIiwidXBsb2FkX2JnX3VybCI6IiIsImJnX3Bvc2l0aW9uIjoiY2VudGVyIiwiYmdfc2l6ZSI6IjEwMCUiLCJiZ19yZXBlYXQiOiJuby1yZXBlYXQifX19IjtzOjE0OiJhZGRpdGlvbmFsX2NzcyI7czowOiIiO31zOjIzOiJub3RpZmllcl9zdWJzY3JpYmVfbWFpbCI7YTozOntzOjEyOiJkaXNwbGF5X25hbWUiO3M6MjA6IlN1YnNjcmliZXIgTmV3IEVtYWlsIjtzOjEzOiJ0ZW1wbGF0ZV9kYXRhIjtzOjQxNTI6InsiY29udGVudHMiOlt7ImRhdGFfaWQiOiJ0Yl8xMDAxIiwiZGF0YV90eXBlIjoicm93IiwiZGF0YV9uYW1lIjoib25lX2NvbHVtbiIsImRhdGFfY3NzIjp7ImhlaWdodCI6IiIsImJvcmRlcl9zcGFjaW5nIjoiMHB4IiwicGFkZGluZ190b3AiOiIwcHgiLCJwYWRkaW5nX3JpZ2h0IjoiMHB4IiwicGFkZGluZ19ib3R0b20iOiIwcHgiLCJwYWRkaW5nX2xlZnQiOiIwcHgiLCJtYXJnaW5fdG9wIjoiMHB4IiwibWFyZ2luX3JpZ2h0IjoiYXV0byIsIm1hcmdpbl9ib3R0b20iOiIwcHgiLCJtYXJnaW5fbGVmdCI6ImF1dG8iLCJib3JkZXJfd2lkdGhfdG9wIjoiMHB4IiwiYm9yZGVyX3dpZHRoX3JpZ2h0IjoiMHB4IiwiYm9yZGVyX3dpZHRoX2JvdHRvbSI6IjBweCIsImJvcmRlcl93aWR0aF9sZWZ0IjoiMHB4IiwiYm9yZGVyX3N0eWxlIjoibm9uZSIsImJvcmRlcl9jb2xvciI6IiIsInVwbG9hZF9iZ191cmwiOiIiLCJiZ19jb2xvciI6IiIsImJnX3Bvc2l0aW9uIjoiY2VudGVyIiwiYmdfc2l6ZSI6IjEwMCUiLCJiZ19yZXBlYXQiOiJuby1yZXBlYXQifSwiZGF0YV9jb3VudCI6MSwiZGF0YV9jb2x1bW5zIjpbInRiXzEwMDIiXSwiY2hpbGQiOlt7ImRhdGFfaWQiOiJ0Yl8xMDAyIiwiZGF0YV90eXBlIjoiY29sdW1uIiwiZGF0YV9uYW1lIjoib25lX2NvbHVtbl9vbmUiLCJkYXRhX2NzcyI6eyJ3aWR0aCI6IjEwMCUiLCJwYWRkaW5nX3RvcCI6IjBweCIsInBhZGRpbmdfcmlnaHQiOiIwcHgiLCJwYWRkaW5nX2JvdHRvbSI6IjBweCIsInBhZGRpbmdfbGVmdCI6IjBweCIsInRleHRfYWxpZ24iOiJjZW50ZXIiLCJib3JkZXJfd2lkdGhfdG9wIjoiMHB4IiwiYm9yZGVyX3dpZHRoX3JpZ2h0IjoiMHB4IiwiYm9yZGVyX3dpZHRoX2JvdHRvbSI6IjBweCIsImJvcmRlcl93aWR0aF9sZWZ0IjoiMHB4IiwiYm9yZGVyX3N0eWxlIjoibm9uZSIsImJvcmRlcl9jb2xvciI6IiIsInVwbG9hZF9iZ191cmwiOiIiLCJiZ19jb2xvciI6IiIsImJnX3Bvc2l0aW9uIjoiY2VudGVyIiwiYmdfc2l6ZSI6IjEwMCUiLCJiZ19yZXBlYXQiOiJuby1yZXBlYXQiLCJ2ZXJ0aWNhbF9hbGlnbiI6InRvcCJ9LCJjaGlsZCI6W3siZGF0YV9pZCI6InRiXzEwMDMiLCJkYXRhX3R5cGUiOiJlbGVtZW50IiwiZGF0YV9uYW1lIjoidGV4dCIsImRhdGFfY3NzIjp7ImNvbG9yIjoiI2ZmZmZmZiIsImFsaWduIjoiY2VudGVyIiwiZm9udF9zaXplIjoiMzBweCIsImxpbmVfaGVpZ2h0IjoiMTUwJSIsImZvbnRfd2VpZ2h0IjoiMzAwIiwiZm9udF9mYW1pbHkiOiJoZWx2ZXRpY2EiLCJiZ19jb2xvciI6IiM5NjU4OGEiLCJ1cGxvYWRfYmdfdXJsIjoiIiwiYmdfc2l6ZSI6IjEwMCUiLCJiZ19wb3NpdGlvbiI6ImNlbnRlciIsImJnX3JlcGVhdCI6Im5vLXJlcGVhdCIsImJvcmRlcl93aWR0aF90b3AiOiIwcHgiLCJib3JkZXJfd2lkdGhfcmlnaHQiOiIwcHgiLCJib3JkZXJfd2lkdGhfYm90dG9tIjoiMHB4IiwiYm9yZGVyX3dpZHRoX2xlZnQiOiIwcHgiLCJib3JkZXJfY29sb3IiOiIiLCJib3JkZXJfc3R5bGUiOiJub25lIiwic2l6ZV93aWR0aCI6IjEwMCUiLCJzaXplX2hlaWdodCI6IiIsIm1hcmdpbl90b3AiOiIwcHgiLCJtYXJnaW5fcmlnaHQiOiJhdXRvIiwibWFyZ2luX2JvdHRvbSI6IjBweCIsIm1hcmdpbl9sZWZ0IjoiYXV0byIsInBhZGRpbmdfdG9wIjoiNDhweCIsInBhZGRpbmdfcmlnaHQiOiI0OHB4IiwicGFkZGluZ19ib3R0b20iOiI0OHB4IiwicGFkZGluZ19sZWZ0IjoiNDhweCIsInRleHRfYWxpZ24iOiJsZWZ0IiwidGV4dGFyZWFfY29udGVudCI6IiJ9LCJkYXRhX3RleHQiOnsidGV4dGFyZWFfY29udGVudCI6IllvdSBzdWJzY3JpYmVkIHRvIHtjd2dpc25fc3Vic2NyaWJlZF9wcm9kdWN0X25hbWV9IGF0IHtzaXRlX25hbWV9In19XX1dfSx7ImRhdGFfaWQiOiJ0Yl8xMDA0IiwiZGF0YV90eXBlIjoicm93IiwiZGF0YV9uYW1lIjoib25lX2NvbHVtbiIsImRhdGFfY3NzIjp7ImhlaWdodCI6IiIsImJvcmRlcl9zcGFjaW5nIjoiMHB4IiwicGFkZGluZ190b3AiOiIwcHgiLCJwYWRkaW5nX3JpZ2h0IjoiMHB4IiwicGFkZGluZ19ib3R0b20iOiIwcHgiLCJwYWRkaW5nX2xlZnQiOiIwcHgiLCJtYXJnaW5fdG9wIjoiMHB4IiwibWFyZ2luX3JpZ2h0IjoiYXV0byIsIm1hcmdpbl9ib3R0b20iOiIwcHgiLCJtYXJnaW5fbGVmdCI6ImF1dG8iLCJib3JkZXJfd2lkdGhfdG9wIjoiMHB4IiwiYm9yZGVyX3dpZHRoX3JpZ2h0IjoiMHB4IiwiYm9yZGVyX3dpZHRoX2JvdHRvbSI6IjBweCIsImJvcmRlcl93aWR0aF9sZWZ0IjoiMHB4IiwiYm9yZGVyX3N0eWxlIjoibm9uZSIsImJvcmRlcl9jb2xvciI6IiIsInVwbG9hZF9iZ191cmwiOiIiLCJiZ19jb2xvciI6IiIsImJnX3Bvc2l0aW9uIjoiY2VudGVyIiwiYmdfc2l6ZSI6IjEwMCUiLCJiZ19yZXBlYXQiOiJuby1yZXBlYXQifSwiZGF0YV9jb3VudCI6MSwiZGF0YV9jb2x1bW5zIjpbInRiXzEwMDUiXSwiY2hpbGQiOlt7ImRhdGFfaWQiOiJ0Yl8xMDA1IiwiZGF0YV90eXBlIjoiY29sdW1uIiwiZGF0YV9uYW1lIjoib25lX2NvbHVtbl9vbmUiLCJkYXRhX2NzcyI6eyJ3aWR0aCI6IjEwMCUiLCJwYWRkaW5nX3RvcCI6IjQ4cHgiLCJwYWRkaW5nX3JpZ2h0IjoiNDhweCIsInBhZGRpbmdfYm90dG9tIjoiMzVweCIsInBhZGRpbmdfbGVmdCI6IjQ4cHgiLCJ0ZXh0X2FsaWduIjoibGVmdCIsImJvcmRlcl93aWR0aF90b3AiOiIwcHgiLCJib3JkZXJfd2lkdGhfcmlnaHQiOiIwcHgiLCJib3JkZXJfd2lkdGhfYm90dG9tIjoiMHB4IiwiYm9yZGVyX3dpZHRoX2xlZnQiOiIwcHgiLCJib3JkZXJfc3R5bGUiOiJub25lIiwiYm9yZGVyX2NvbG9yIjoiIiwidXBsb2FkX2JnX3VybCI6IiIsImJnX2NvbG9yIjoiI2ZmZmZmZiIsImJnX3Bvc2l0aW9uIjoiY2VudGVyIiwiYmdfc2l6ZSI6IjEwMCUiLCJiZ19yZXBlYXQiOiJuby1yZXBlYXQiLCJ2ZXJ0aWNhbF9hbGlnbiI6InRvcCJ9LCJjaGlsZCI6W3siZGF0YV9pZCI6InRiXzEwMDYiLCJkYXRhX3R5cGUiOiJlbGVtZW50IiwiZGF0YV9uYW1lIjoidGV4dCIsImRhdGFfY3NzIjp7ImNvbG9yIjoiIzYzNjM2MyIsImFsaWduIjoiY2VudGVyIiwiZm9udF9zaXplIjoiMTRweCIsImxpbmVfaGVpZ2h0IjoiMTUwJSIsImZvbnRfd2VpZ2h0Ijoibm9ybWFsIiwiZm9udF9mYW1pbHkiOiJoZWx2ZXRpY2EiLCJiZ19jb2xvciI6IiIsInVwbG9hZF9iZ191cmwiOiIiLCJiZ19zaXplIjoiMTAwJSIsImJnX3Bvc2l0aW9uIjoiY2VudGVyIiwiYmdfcmVwZWF0Ijoibm8tcmVwZWF0IiwiYm9yZGVyX3dpZHRoX3RvcCI6IjBweCIsImJvcmRlcl93aWR0aF9yaWdodCI6IjBweCIsImJvcmRlcl93aWR0aF9ib3R0b20iOiIwcHgiLCJib3JkZXJfd2lkdGhfbGVmdCI6IjBweCIsImJvcmRlcl9jb2xvciI6IiIsImJvcmRlcl9zdHlsZSI6Im5vbmUiLCJzaXplX3dpZHRoIjoiMTAwJSIsInNpemVfaGVpZ2h0IjoiIiwibWFyZ2luX3RvcCI6IjBweCIsIm1hcmdpbl9yaWdodCI6ImF1dG8iLCJtYXJnaW5fYm90dG9tIjoiMHB4IiwibWFyZ2luX2xlZnQiOiJhdXRvIiwicGFkZGluZ190b3AiOiIxNXB4IiwicGFkZGluZ19yaWdodCI6IjE1cHgiLCJwYWRkaW5nX2JvdHRvbSI6IjE1cHgiLCJwYWRkaW5nX2xlZnQiOiIxNXB4IiwidGV4dF9hbGlnbiI6ImxlZnQiLCJ0ZXh0YXJlYV9jb250ZW50IjoiIn0sImRhdGFfdGV4dCI6eyJ0ZXh0YXJlYV9jb250ZW50IjoiRGVhciB7Y3dnaXNuX3N1YnNjcmliZXJfbmFtZX0sXG5UaGFuayB5b3UgZm9yIHN1YnNjcmliaW5nIHRvIHRoZSAje2N3Z2lzbl9zdWJzY3JpYmVkX3Byb2R1Y3RfbmFtZX0uIFdlIHdpbGwgZW1haWwgeW91IG9uY2UgcHJvZHVjdCBiYWNrIGluIHN0b2NrXG4ifX1dfV19XSwibGFzdF9pZCI6MTAwNiwiYnVpbGRlciI6eyJkYXRhX2lkIjoidGJfdGVtcF9idWlsZGVyIiwiZGF0YV9jc3MiOnsiYm9yZGVyX3dpZHRoX3RvcCI6IjFweCIsImJvcmRlcl93aWR0aF9yaWdodCI6IjFweCIsImJvcmRlcl93aWR0aF9ib3R0b20iOiIxcHgiLCJib3JkZXJfd2lkdGhfbGVmdCI6IjFweCIsImJvcmRlcl9zdHlsZSI6InNvbGlkIiwiYm9yZGVyX2NvbG9yIjoiI2Y2ZjdmYSIsImJnX2NvbG9yIjoiI2ZmZmZmZiIsInVwbG9hZF9iZ191cmwiOiIiLCJiZ19wb3NpdGlvbiI6ImNlbnRlciIsImJnX3NpemUiOiIxMDAlIiwiYmdfcmVwZWF0Ijoibm8tcmVwZWF0In19fSI7czoxNDoiYWRkaXRpb25hbF9jc3MiO3M6MDoiIjt9fX0=';
	}

}

endif;

new THWEC_Back_In_Stock_Notifier();