<?php
/**
 * The admin general settings page functionality of the plugin.
 *
 * @link       https://themehigh.com
 * @since      1.0.0
 *
 * @package    woocommerce-email-customizer-pro
 * @subpackage woocommerce-email-customizer-pro/admin
 */

use Pelago\Emogrifier\CssInliner;
use Pelago\Emogrifier\HtmlProcessor\CssToAttributeConverter;
use Pelago\Emogrifier\HtmlProcessor\HtmlPruner;

if(!defined('WPINC')){	die; }

if(!class_exists('THWEC_Admin_Settings_General')):

class THWEC_Admin_Settings_General {
	/**
	 * Main instance of the class
	 *
	 * @access   protected
	 * @var      $_instance    
	 */
	protected static $_instance = null;

	/**
	 * Manages the WooCommerce billing and shipping methods in email templates
	 *
	 * @access   private
	 * @var      $woo_method_variables    Woo billing and shipping methods helper
	 */
	private $woo_method_variables = array();

	/**
	 * Manages the checkout field editor (themehigh compatibility) shortcode
	 *
	 * @access   private
	 * @var      $wcfe_pattern    shortcode for checkout field editor
	 */
	private $wcfe_pattern = '';

	/**
	 * Manages the custom hook added via template builder
	 *
	 * @access   private
	 * @var      $wecm_custom_hook    Custom hook shortcode
	 */
	private $wecm_custom_hook = '';

	/**
	 * Manages the order table helper contents
	 *
	 * @access   private
	 * @var      $wecm_order_table_helper    order table helper
	 */
	private $wecm_order_table_helper = '';

	/**
	 * Manages the order table title link section content
	 *
	 * @access   private
	 * @var      $wecm_order_table_head    order table title link section content
	 */
	private $wecm_order_table_head = '';

	/**
	 * Manages the order table item content
	 *
	 * @access   private
	 * @var      $wecm_order_table_head    order table item content
	 */
	private $wecm_order_item = '';

	/**
	 * Manages the email wrapper styles
	 *
	 * @access   private
	 * @var      $temp_wrapper_styles    email wrapper styles
	 */
	private $temp_wrapper_styles = '';

	/**
	 * Manages the account url related placeholders
	 *
	 * @access   private
	 * @var      $link_pkaceholders    account url related placeholders
	 */
	private $link_pkaceholders = array();

	/**
	 * Manages the template name used for displaying 
	 *
	 * @access   private
	 * @var      $display_name    template name used for displaying 
	 */
	private $display_name = '';

	/**
	 * Manages the email template file name
	 *
	 * @access   private
	 * @var      $file_name    email template file name
	 */
	private $file_name = '';

	/**
	 * Manages the WPML language in which template is created/saved
	 *
	 * @access   private
	 * @var      $template_lang    WPML template language
	 */
	private $template_lang = null;

	/**
	 * Stores the default WPML language configured in the WPML
	 *
	 * @access   private
	 * @var      $default_lang    default WPML language
	 */
	private $default_lang = null;

	/**
	 * Manages settings update if required
	 *
	 * @access   private
	 * @var      $update_template   if settings update required
	 */
	private $update_template = false;

	/**
	 * Manages YITH gift card helper placeholders and shortcodes
	 *
	 * @access   private
	 * @var      $wecm_ywgc    YITH gift card helper
	 */
	private $wecm_ywgc = null;

	/**
	 * Stores if the YITH gift card plugin is active
	 *
	 * @access   private
	 * @var      $ywgc_active    activation status
	 */
	private $ywgc_active = false;

	/**
	 * Stores the name of the preview email class
	 *
	 * @access   private
	 * @var      $preview_template   email to preview
	 */
	private $preview_template = false;

	/**
	 * Stores the name of the preview template files
	 *
	 * @access   private
	 * @var      $preview_template   template to preview
	 */
	private $preview_files = array();

	/**
	 * Stores the WooCommerce account related emails
	 *
	 * @access   private
	 * @var      $account_emails   account emails
	 */
	private $account_emails = array();

	/**
	 * Stores the WooCommerce refunded email status labels
	 *
	 * @access   private
	 * @var      $refunded_emails   refunded email statuses
	 */
	private $refunded_emails = array();

	/**
     * Construct
     */
	public function __construct() {
		add_action('wp_ajax_thwec_save_email_template', array($this,'save_email_template'));
		add_action('wp_ajax_thwec_send_test_mail', array($this,'send_test_mail'));
		add_action('wp_ajax_thwec_preview_template', array($this,'thwec_preview_template'));
		add_action('wp_ajax_thwec_initialize_builder', array( $this, 'thwec_initialize_builder') );
		
		$this->init_constants();
	}

	/**
	 * Main THWEC_Admin_Settings_General Instance.
	 *
	 * Ensures only one instance of THWEC_Admin_Settings_General is loaded or can be loaded.
	 *
	 * @since 1.0.0
	 * @static
	 * @return THWEC_Admin_Settings_Templates Main instance
	 */
	public static function instance() {
		if(is_null(self::$_instance)){
			self::$_instance = new self();
		}
		return self::$_instance;
	}
	
	/**
     * Initialize variables
     */
	public function init_constants(){
		$this->woo_method_variables = array(
			'billing_first_name',
			'billing_last_name',
			'billing_company',
			'billing_country',
			'billing_address_1',
			'billing_address_2',
			'billing_city',
			'billing_state',
			'billing_postcode',
			'billing_phone',
			'billing_email',
			'shipping_first_name',
			'shipping_last_name',
			'shipping_company',
			'shipping_country',
			'shipping_address_1',
			'shipping_address_2',
			'shipping_city',
			'shipping_state',
			'shipping_postcode'
		);
		$this->wcfe_pattern = '\[(\[?)(WCFE)(?![\w-])([^\]\/]*(?:\/(?!\])[^\]\/]*)*?)(?:(\/)\]|\](?:([^\[]*+(?:\[(?!\/\2\])[^\[]*+)*+)\[\/\2\])?)(\]?)'; 

		$this->wecm_custom_hook = '\[(\[?)(WECM_CUSTOM_HOOK)(?![\w-])([^\]\/]*(?:\/(?!\])[^\]\/]*)*?)(?:(\/)\]|\](?:([^\[]*+(?:\[(?!\/\2\])[^\[]*+)*+)\[\/\2\])?)(\]?)'; 
	
		$this->wecm_order_table_helper = '\[(\[?)(WECM_ORDER_T_HELPER)(?![\w-])([^\]\/]*(?:\/(?!\])[^\]\/]*)*?)(?:(\/)\]|\](?:([^\[]*+(?:\[(?!\/\2\])[^\[]*+)*+)\[\/\2\])?)(\]?)';
		
		$this->wecm_order_table_head = '\[(\[?)(WECM_ORDER_T_HEAD)(?![\w-])([^\]\/]*(?:\/(?!\])[^\]\/]*)*?)(?:(\/)\]|\](?:([^\[]*+(?:\[(?!\/\2\])[^\[]*+)*+)\[\/\2\])?)(\]?)';

		$this->wecm_order_table_td = '\[(\[?)(WECM_ORDER_TD_CSS)(?![\w-])([^\]\/]*(?:\/(?!\])[^\]\/]*)*?)(?:(\/)\]|\](?:([^\[]*+(?:\[(?!\/\2\])[^\[]*+)*+)\[\/\2\])?)(\]?)';
		
		$this->wecm_order_item = '\[(\[?)(WECM_ORDER_ITEM)(?![\w-])([^\]\/]*(?:\/(?!\])[^\]\/]*)*?)(?:(\/)\]|\](?:([^\[]*+(?:\[(?!\/\2\])[^\[]*+)*+)\[\/\2\])?)(\]?)';

		$this->temp_wrapper_styles = array('bg' => '#f7f7f7', 'padding' => '70px 0');

		$this->link_pkaceholders = array( '{account_area_url}', '{account_order_url}');

		$this->wecm_ywgc = '\[(\[?)(WECM_YWGC)(?![\w-])([^\]\/]*(?:\/(?!\])[^\]\/]*)*?)(?:(\/)\]|\](?:([^\[]*+(?:\[(?!\/\2\])[^\[]*+)*+)\[\/\2\])?)(\]?)';

		$this->ywgc_active = in_array( 'yith-woocommerce-gift-cards-premium', THWEC_Utils::compatible_plugins() );
		$this->account_emails = array( 'WC_Email_Customer_New_Account', 'WC_Email_Customer_Reset_Password' );
		$this->refunded_emails = array( 'WC_Email_Customer_Partial_Refunded_Order', 'WC_Email_Customer_Refunded_Order' );
	}

	/**
     * Render page
     */
	public function render_page(){
		$this->render_content();
	}

	/**
     * Get the regex for shortcodes used
     */
    public function get_th_shortcode_atts_regex() {
		return '/([\w-]+)\s*=\s*"([^"]*)"(?:\s|$)|([\w-]+)\s*=\s*\'([^\']*)\'(?:\s|$)|([\w-]+)\s*=\s*([^\s\'"]+)(?:\s|$)|"([^"]*)"(?:\s|$)|\'([^\']*)\'(?:\s|$)|(\S+)(?:\s|$)/';
	}

	/**
     * Initializing template builder
     */
	public function thwec_initialize_builder(){
		check_ajax_referer( 'thwec-initialize-builder', 'security' );
		if( THWEC_Utils::thwec_actions() ){
			$this->reset_preview();
			THWEC_Utils::delete_backup_directory();
		}
	}

	private function php8_comaptibiltiy_css( $styles ){
		$layout_css = " .thwec-block-one-column > tbody > tr > td{
				width: 100%;				
			}
			.thwec-block-two-column > tbody > tr > td{
				width: 50%;				
			}

			.thwec-block-three-column >tbody > tr > td{
                width: 33%;             
            }

            .thwec-block-four-column >tbody > tr > td{
                width: 25%;             
            }";
         return $layout_css.$styles;
	}

	/**
     * Template preview 
     */
	public function thwec_preview_template(){
		check_ajax_referer( 'thwec-preview-order', 'security' );
		$task = isset( $_POST['task'] ) ? sanitize_text_field( $_POST['task'] ) : false;

		$order_id = isset( $_POST['order_id'] ) ? absint( $_POST['order_id'] ) : false;
		$email = isset( $_POST['email_status'] ) ? sanitize_text_field( $_POST['email_status'] ) : false;
		$gift_card = isset( $_POST['gift_card'] ) ? absint( $_POST['gift_card'] ) : false;

		$content_plain = isset( $_POST['content_plain'] ) ? wp_kses_post( stripslashes( $_POST['content_plain'] ) ) : false;
		$content_html = isset( $_POST['content_html'] ) ? wp_kses_post( stripslashes( $_POST['content_html'] ) ) : false;
		$content_css = isset( $_POST['content_css'] ) ? wp_kses_post( stripslashes( $_POST['content_css'] ) ) : false;
		$content_css = $this->php8_comaptibiltiy_css( $content_css );
		$name = isset( $_POST['file'] ) ? sanitize_text_field( stripslashes( $_POST['file'] ) ) : false;
		$recreate_preview = isset($_POST['file_update']) ? THWEC_Utils::validate_boolean($_POST['file_update']) : true;
		if( $task == 'reset_preview' ){
			$this->reset_preview();

		}else if( $content_html && $content_css && $name && $task == 'create_preview' ){
			$this->thwec_create_preview( $content_html, $content_css, $name, $recreate_preview );
		}
	}

	/**
	 * Revert preview changes back to display template builder
	 *
	 * @return boolean preview directory deleted or not
	 */
	public function reset_preview(){
		$backup_dir = THWEC_Utils::get_template_preview_directory();
		if( file_exists( $backup_dir ) && is_dir( $backup_dir ) ){
			return $this->delete_preview_directory( $backup_dir );
		}
	}

	/**
	 * Delete preview directory
	 *
	 * @param string $preview_dir preview directory path
	 * @return boolean directory deleted or not
	 */
	public function delete_preview_directory( $preview_dir ){
		return THWEC_Utils::delete_directory( $preview_dir );
	}

	/**
	 * Render content when preview fails
	 *
	 */
	public function render_preview_failed(){
		?>
		<table border="0" cellpadding="0" cellspacing="0" height="100%" width="100%" id="thwec_template_wrapper">
			<tr>
				<td align="center" valign="top" style="padding: 70px 0px; background-color: #f7f7f7;">
					<div style="width: 600px;background-color: white;padding: 20px;border-radius: 10px;">
						<h3>Template failed to preview</h3>
						<p>Please previewing again</p>
					</div>
				</td>
			</tr>
		</table>
		<?php
	}


	/**
	 *  Preview html contents
	 *
	 */
	public function load_preview( $content ){
		if( !$content ){
			$this->render_preview_failed();
			return;
		}
		?>
		<html>
			<head>
				<title>Preview - Email Customizer for WooCommerce (Themehigh)</title>
				<style>
					body{
						margin: 0;
					}
				</style>
			</head>
			<body>
				<?php echo $content; ?>
			</body>
		</html>
		<?php
	}

	/**
	 * Set empty email recipient
	 *
	 * @param $recipient email recipient
	 */
	public function no_recipient( $recipient ){
		$recipient = '';
		return $recipient;
	}

	/**
	 * Check whether email sent to admin or customer
	 *
	 * @param object $email_class WC_Email object
	 * @return string $email_type admin or customer email
	 */
	public function thwec_preview_email_type( $email_class ){
		if( THWEC_Utils::is_order_status_manager_email( $email_class) ){
			$email_type = $email_class->type;

		}else if( in_array( $email_class->id, THWEC_Utils::THWEC_EMAIL_INDEX ) ){
			$email_type = in_array( $email_class->id, array( 'new_order', 'cancelled_order', 'failed_order') ) ? 'admin' : 'customer';
		}else{
			$email_type = 'customer';
		}
		return $email_type;
	}

	/**
     * Get Email status class object
	 *
	 * @param string $emails Order ID to preview
	 * @param object email status class object
     */
	public function get_email_class( $emails, $index ){
		$index = $index === "WC_Email_Customer_Partial_Refunded_Order" ? "WC_Email_Customer_Refunded_Order" : $index;
		$emails = $emails->get_emails();
		return isset( $emails[$index] ) ? $emails[$index] : false;	 
	}

	public function manage_woocommerce_hooks( $email ){
		remove_action( 'woocommerce_email_header', [$email, 'email_header'] );
		remove_action( 'woocommerce_email_footer', [$email, 'email_footer'] );
		add_action( 'woocommerce_email_header', 'THWEC_Utils::thwec_email_header', 10, 2 );
		add_action( 'woocommerce_email_footer', 'THWEC_Utils::thwec_email_footer', 10, 2 );
	}

	/**
     * Template preview
	 *
	 * @param string $order_id Order ID to preview
	 * @param string $email_index WC_Email class name
	 * @param string $gift_card_id Yith Gift card id
	 * @param string $name file name to be previewed
	 * @param boolean $return choose between return or echo the content
	 * @param boolean $test_mail whether for test mail or not
     */
	public function thwec_order_preview($order_id, $email_index, $gift_card_id, $content, $styles, $return = false, $no_recipient ){
		$emails = WC_Emails::instance();
		$template_type = $order_id ? 'html' : 'plain';
		// Ensure gateways are loaded in case they need to insert data into the emails.
		$email_class = $this->get_email_class( $emails, $email_index );
		if( $email_class ){
			$yith_email = strpos( get_class( $email_class ), 'YITH_YWGC_Email' ) !== false;
			if( $no_recipient ){
				$email_class_id = $email_index === "WC_Email_Customer_Partial_Refunded_Order" ? "customer_partially_refunded_order" : $email_class->id;
				add_filter( 'woocommerce_email_recipient_' . $email_class_id, array( $this, 'no_recipient' ) );
			}
			if( in_array( $email_index, $this->account_emails ) ){
				//Account related email
				$customer = THWEC_Admin_Utils::get_logged_in_user();
				if( $customer ){
					$template_type = 'html';
					$customer_id = $customer->ID;
					$customer_login = $customer->user_login;
					$email_class->trigger( $customer_id, '', false );

					$email_args = array(
						'user_login'         => $customer_login,
						'user_pass'          => '',
						'blogname'           => wp_specialchars_decode( get_option( 'blogname' ), ENT_QUOTES ),
						'password_generated' => false,
						'sent_to_admin'      => false,
					);
				}
			}else if( $yith_email ){
				$template_type = 'html';
				$gift_card = new YWGC_Gift_Card_Premium( array( 'ID' => $gift_card_id ) );
				$email_class->trigger( $gift_card_id, 'recipient' );
				$email_args = array(
					'gift_card'         => $gift_card,
					'introductory_text' => $email_class->introductory_text,
					'email_type'        => 'html',
					'sent_to_admin'     => false,
					'email'             => $gift_card,
	                'case'              => 'recipient',
				);
			}else{
				//Order related email
				$order = wc_get_order( $order_id );
				$email_type = $this->thwec_preview_email_type( $email_class );
				if( !in_array( $email_index, $this->refunded_emails ) ){
					$email_class->object = $order;
				}				
				
				if( in_array( $email_index, $this->refunded_emails ) ){
					$is_partial = $email_index === "WC_Email_Customer_Partial_Refunded_Order" ? true : false;
					$email_class->trigger( $order_id, $is_partial );
				}else{
					$email_class->trigger( false, $order );
				}
				
				$email_args = array(
					'order'              => $order,
					'sent_to_admin'      => $email_type == 'admin' ? true : false,
				);
			}

			$this->manage_woocommerce_hooks( $emails );

			$args = array_merge( $email_args, array(
				'email_heading'      => $email_class->get_heading(),
				'additional_content' => $email_class->get_additional_content(),
				'plain_text'         => false,
				'email'              => $email_class,
			) );

			extract( $args );

			if( !$styles ){ //Preview - $content is the name of file while previewing
				$file = THWEC_Utils::get_template_preview_path( $content, $template_type );
				$css = THWEC_Utils::get_style_preview_path( $content );
				
				if( file_exists($file) && file_exists($css) ){
					$css = file_get_contents($css);
					$css = $this->yith_css( $yith_email ).$css;
					if( isset( $_POST['test_mail_data'] ) ){
						if( apply_filters( 'thwec_mobile_compatibility_wrapper_padding', true ) ){
							$css .= '@media only screen and (max-width:480px) {
					  			#thwec_template_wrapper .thwec-template-wrapper-column{ padding: 0px !important;} 
					  		}';
						}
					}
					ob_start();
					include_once( $file );
					$content = ob_get_clean();
					$content = $this->style_inline( $content, $css, true );
				}
			}

			if( $no_recipient ){
				remove_filter( 'woocommerce_email_recipient_' . $email_class->id, array( $this, 'no_recipient' ) );
			}

			if( !empty( $content ) && !is_null( $content ) ){
				if( $return && $styles ){
					ob_start();
					echo $content;
					return ob_get_clean();

				}else if( $return ){
					return $content;
				}
			}else if( $return ){
				return false;
			}
			
			wp_die();
		}
	}

	public function live_test_mail(){
		$order_id = isset( $_POST['order_id'] ) ? absint( $_POST['order_id'] ) : false;
		$email_status = isset( $_POST['email_status'] ) ? sanitize_text_field( $_POST['email_status'] ) : false;
		$ywgc_code = isset( $_POST['ywgc_code'] ) ? sanitize_text_field( $_POST['ywgc_code'] ) : false;
		$content = isset( $_POST['template'] ) ? stripslashes( $_POST['template'] ) : false;
		$styles = isset( $_POST['styles'] ) ? stripslashes( $_POST['styles'] ) : false;
		$styles = $this->php8_comaptibiltiy_css( $styles );
		$file = isset( $_POST['file'] ) ? sanitize_text_field( stripslashes( $_POST['file'] ) ) : false;
		$file_update = isset($_POST['file_update']) ? THWEC_Utils::validate_boolean($_POST['file_update']) : true;
		if( !$file_update ){
			$content = $this->thwec_order_preview($order_id, $email_status, $ywgc_code, $file, false, true, true );
		}else{
			$content = $this->thwec_create_preview( $content, $styles, $file, $file_update, false );
			$content = $this->thwec_order_preview($order_id, $email_status, $ywgc_code, $file, false, true, true );
		}
		
		$send_mail = $this->send_mail( $content );
		return $send_mail ? 'success' : 'failure';
	}

	public function yith_css( $yith_email ){
		ob_start();
		if( $yith_email ){
			wc_get_template ( 'emails/style.css',
			'',
			'',
			YITH_YWGC_TEMPLATES_DIR );
		}
		return ob_get_clean();
	}

	/**
     * create preview files
     *
	 * @param string $html temporary template file with replaced content for order preview
	 * @return boolean $saved file created or not
     */
	public function thwec_create_preview( $html, $css, $file_name, $create_preview, $ajax=true ){
		$html_file = false;
		$css_file = false;

		$html_path = THWEC_Utils::get_template_preview_path( $file_name);
		$style_path = THWEC_Utils::get_style_preview_path( $file_name );

		if( !$create_preview && THWEC_Utils::whether_previewfile_exist( $html_path, $style_path ) ){
			wp_send_json( array( 'response' => $html_path && $style_path, 'file' => $file_name, 'status' => "exists" ) );
		}
		THWEC_Utils::clear_preview_directory();
		$directory = $this->create_preview_directory();

		ob_start();
		wc_get_template( 'emails/email-styles.php' );
		$woo_css = apply_filters( 'woocommerce_email_styles', ob_get_clean(), $this );

		$css = $woo_css.$css;
		if( $directory ){
			$html = $this->prepare_email_content_wrapper($html);
			$html = $this->html_wrapper( $html);
			$html = $this->insert_dynamic_data($html, true );
			$html_file = $this->generate_template_file($html, $html_path);
			$css_file = $this->generate_template_file( trim( $css ), $style_path);
		}
		if( $ajax ){
			return wp_send_json( array( 'response' => $html_file && $css_file, 'file' => $file_name ) );
		}else{
			return $html_file && $css_file;
		}
	}

	/**
	 * Create folder for storing preview files
	 *
	 */
	private function create_preview_directory(){
		if( ! is_dir( THWEC_Utils::get_template_preview_directory() ) ){
			return wp_mkdir_p(THWEC_Utils::get_template_preview_directory());
		}
		return is_dir( THWEC_Utils::get_template_preview_directory() );
	}

	/**
	 * Delete the template preview file 
	 *
	 * @return boolean $remove template file removed or not
	 */	
	public function remove_preview(){
		$remove = false;
		$file = THWEC_Utils::get_template_preview_path();
		if( file_exists( $file ) ){
			$remove = unlink( $file );
		}
		return $remove;
	}

	/**
	 * Generate the template file
	 *
	 * @param  string $content contents
	 * @param  string $path file path
	 * @param  string $css css styles
	 * @return boolean $saved file created or not
	 */	
	public function generate_template_file($content, $path, $css=false){
		if( ! is_dir(THWEC_Utils::get_template_directory()) ){
			wp_mkdir_p(THWEC_Utils::get_template_directory());
		}
		$saved = false;
		$myfile_template = fopen($path, "w") or die("Unable to open file!");
		if(false !== $myfile_template){
			fwrite($myfile_template, $content);
			fclose($myfile_template);
			$saved = true;
		}
		return $saved;
	}

	/**
	 * convert to inline styles
	 *
	 * @param  string $content contents
	 * @param  string $css css styles
	 * @return string $content updated content
	 */	
	public function style_inline( $content, $css, $preview=false ) {
		$supports_emogrifier = class_exists( 'DOMDocument' );
		if( THWEC_Utils::woo_version_check('6.5.0') ){
			$css_inliner_class = CssInliner::class;
			if ( $supports_emogrifier && class_exists( $css_inliner_class ) ) {
				try {
					$css_inliner = CssInliner::fromHtml( $content )->inlineCss( $css );

					do_action( 'woocommerce_emogrifier', $css_inliner, $this );
					$dom_document = $css_inliner->getDomDocument();
					HtmlPruner::fromDomDocument( $dom_document )->removeElementsWithDisplayNone();
					$content = CssToAttributeConverter::fromDomDocument( $dom_document )
						->convertCssToVisualAttributes()
						->render();
					$content = htmlspecialchars_decode($content);
				} catch ( Exception $e ) {
					$logger = wc_get_logger();
					$logger->error( $e->getMessage(), array( 'source' => 'emogrifier' ) );
				}
			} else {
				$content = '<style type="text/css">' . $css . '</style>' . $content;
			}

		}else{
			$emogrifier_support = $supports_emogrifier && version_compare( PHP_VERSION, '5.5', '>=' );
			if ( $content && $css && $emogrifier_support) {
				$emogrifier_class = THWEC_Admin_Utils::woo_emogrifier_version_check() ? '\\Pelago\\Emogrifier' : 'Emogrifier';
				if ( ! class_exists( $emogrifier_class ) && file_exists(WP_PLUGIN_DIR.'/woocommerce/includes/libraries/class-emogrifier.php')) {
					require_once(WP_PLUGIN_DIR.'/woocommerce/includes/libraries/class-emogrifier.php');
				}
				try {
					$emogrifier = new $emogrifier_class( $content, $css );
					if( $preview ){
						$content    = $emogrifier->emogrifyBodyContent();
					}else{
						$content    = $emogrifier->emogrify();
					}
					$content    = htmlspecialchars_decode($content);
				} catch ( Exception $e ) {
					error_log($e);
				}
			}
		}
		return $content;
	}

	/**
	 * Generate the template details
	 *
	 * @param  string $key wpml language or not
	 * @return array $meta name and path of file
	 */
	public function get_template_meta( $key ){
		$meta = array();
		$lang = false;
		if( $key == 'wpml-default' ){
			$file = $this->def_lang_template_name();

		}else if( $key == 'wpml-lang' ){
			$file = $this->get_wpml_template_name();

		}else if( $key == 'default' ){
			$file = $this->file_name;
		}
		$meta['name'] = $file;
		$meta['path'] = THWEC_CUSTOM_TEMPLATE_PATH.$file.'.php';
		return $meta;
	}

	/**
	 * Checks if the current template is a wpml template in default language
	 *
	 * @return boolean default wpml language template or not
	 */	
	public function is_default_lang_template(){
		if( $this->template_lang == $this->default_lang ){
			return true;
		}
		return false;
	}

	/**
	 * Get the wpml template name of the current template
	 *
	 * @return string wpml template name
	 */	
	public function get_wpml_template_name(){
		return $this->file_name.'-'.$this->template_lang;
	}

	/**
	 * Get the template name in default language
	 *
	 * @param  string $key wpml language or not
	 * @return array $meta name and path of file
	 */	
	public function def_lang_template_name(){
		return $this->file_name.'-'.$this->default_lang;
	}

	/**
	 * Check if the current template has template in default language
	 *
	 * @return boolean template in default language exists or not
	 */	
	public function has_template_in_def_lang( $settings ){
		$def_lang_template = $this->def_lang_template_name();
		return array_key_exists( $def_lang_template, $settings['templates'] );
	}

	/**
	 * Prepare email wrapper styles
	 *
	 * @param  array $css email wrapper styles
	 * @return array $css email wrapper styles
	 */	
	public function sanitize_wrapper_styles( $css ){
		$bg_color = isset( $css['bg'] ) ? sanitize_hex_color( $css['bg'] ) : $this->temp_wrapper_styles['bg'];
		$css['bg'] = is_null( $bg_color ) ? $this->temp_wrapper_styles['bg'] : $bg_color;
		return $css;
	}

	/**
	 * Prepare email wrapper for the content
	 *
	 * @param  string $content email content
	 * @return string $content updated email content
	 */	
	public function prepare_email_content_wrapper($content){
		$wrap_css_arr = apply_filters('thwec_template_wrapper_style_override', $this->temp_wrapper_styles);
		$wrap_css_arr = $this->sanitize_wrapper_styles( $wrap_css_arr );
		$wrap_css = 'background-color:'.$wrap_css_arr['bg'].';'.'padding:'.$wrap_css_arr['padding'].';';
		$wrapper = '<table border="0" cellpadding="0" cellspacing="0" height="100%" width="100%" id="thwec_template_wrapper">';
		$wrapper .= '<tr>';
		$wrapper .= '<td align="center" class="thwec-template-wrapper-column" valign="top" style="'.$wrap_css.'">';
		$wrapper .= '<div id="thwec_template_container">';
		$wrapper .= $content;
		$wrapper .= '</div>';
		$wrapper .= '</td>';
		$wrapper .= '</tr>';
		$wrapper .= '</table>';									
		return $wrapper;
	}

	/**
	 * Prepare HTML head, body wrappers for the content
	 *
	 * @param  string $content email content
	 * @return string $content updated content
	 */	
	public function html_wrapper( $content, $html=true ){
		$rtl = is_rtl() ? 'rightmargin="0"' : 'leftmargin="0"';
		$charset = '';
		$wrapper = '<html '.get_language_attributes().'>';
		$wrapper .= '<head>';
		$wrapper .= '<meta http-equiv="Content-Type" content="text/html; charset='.get_bloginfo( 'charset' ).'" />';
		$wrapper .= do_action( 'thwec_template_meta_tags', true ); //true for test emails
		$wrapper .= '<title>'.get_bloginfo( 'name', 'display' ).'</title>';
		$wrapper .= '</head>';
		$wrapper .= '<body '.$rtl.' marginwidth="0" topmargin="0" marginheight="0" offset="0">';
		$wrapper .= '<?php do_action(\'thwec_before_contents\'); ?>';
		if( $html ){
			$wrapper .= '<?php if( !isset( $order ) && isset( $gift_card->order_id ) ){
				$order = wc_get_order( $gift_card->order_id );
			} ?>';
			$wrapper .= '<?php if( isset( $order ) && is_a( $order, \'WC_Order_Refund\' ) ){
				$order = wc_get_order( $order->get_parent_id() );
			} ?>';
			$wrapper .= apply_filters('thwec_template_globals', $wrapper);
		}
		$wrapper .= $content;
		$wrapper .= '</body>';
		$wrapper .= '</html>';
		return $wrapper;
	}

	/**
	 * Send the test email
	 *
	 * @param  string $content email content
	 * @return boolean $send_mail mail sent or not
	 */	
	public function send_mail( $content ){
		$to = $this->get_to_address();
		$subject = "[".get_bloginfo('name')."] Test Email";
		$from_email = $this->get_from_address();
		$headers = $this->setup_test_mail_variables( $from_email );
		
		add_filter( 'wp_mail_from', array( $this, 'get_from_address' ) );
		add_filter( 'wp_mail_from_name', array( $this, 'get_from_name' ) );
		add_filter( 'wp_mail_content_type', array( $this, 'get_content_type' ) );
		
		$send_mail = wp_mail( $to, $subject, $content, $headers );

		remove_filter( 'wp_mail_from', array( $this, 'get_from_address' ) );
		remove_filter( 'wp_mail_from_name', array( $this, 'get_from_name' ) );
		remove_filter( 'wp_mail_content_type', array( $this, 'get_content_type' ) );
		return $send_mail;
	}

	/**
	 * Set the email from name
	 *
	 * @return string blogname
	 */	
	public function get_from_name() {
		return get_bloginfo('name');
	}

	/**
	 * Set the email to address
	 *
	 * @return string to email
	 */	
	public function get_to_address() {
		if( isset( $_POST['email_id'] ) && !empty( $_POST['email_id'] ) ){
			return sanitize_email( $_POST['email_id'] );
		}
	}

	/**
	 * Set the email from address
	 *
	 * @return string from email
	 */	
	public function get_from_address() {
		return apply_filters('thwec_testmail_from_address', get_option('admin_email'));
	}

	/**
	 * Set the email content type
	 *
	 * @return string text or html email
	 */	
	public function get_content_type(){
		return 'text/html';
	}

	/**
	 * Set the email header
	 *
	 * @param  string $from_email from email address
	 * @return array $headers email headers
	 */	
	public function setup_test_mail_variables( $from_email ){
		$headers  = "MIME-Version: 1.0" . "\r\n";
		$headers .= "Content-type: text/html; charset=".get_bloginfo('charset')."" . "\r\n";
		$headers .= "From: ".get_bloginfo()." <".$from_email.">" . "\r\n";
		return $headers;
	}

	/*-------------------------------------------------------------------------------------------
	**------------------------------ TEMPLATE FILES AND SETTINGS --------------------------------
	**------------------------------------------------------------------------------------------*/

	/**
	 * Save the email template or send test email
	 */	
	public function save_email_template(){
		if(isset($_POST['template_render_data'])){
			if( !$this->if_template_exists() ){
				$this->initiate_save_template( $_POST );
			}
		}
		wp_send_json("failure");
	}

	/**
	 * Prepare the test email
	 * @param  array $posted posted test email data
	 */	
	public function send_test_mail(){
		$response = "failure";
		if( isset( $_POST['template'] ) ){
			$response = $this->live_test_mail();
		}
		wp_send_json($response);
	}

	/**
	 * Check if the template exists
	 *
	 * @return boolean template exists or not
	 */
	public function if_template_exists(){
		$template_status = isset( $_POST['template_status'] ) ? sanitize_text_field( $_POST['template_status'] ) : 'new';
		$overwrite = isset( $_POST['overwrite'] ) ? THWEC_Utils::validate_boolean( $_POST['overwrite'] ) : false;
		if( $template_status == 'new' && !$overwrite ){
			$tname = isset( $_POST['template_name'] ) ? THWEC_Admin_Utils::prepare_template_file_name( sanitize_text_field( $_POST['template_name'] ) ) : '';
			$this->template_settings = THWEC_Utils::get_template_settings();
			$wpml_templates = isset( $this->template_settings['thwec_wpml_map'] ) ? $this->template_settings['thwec_wpml_map'] : array();
			$non_wpml_templates = isset( $this->template_settings['templates'] ) ? array_keys( $this->template_settings['templates'] ) : array();
			if( in_array( $tname, $non_wpml_templates ) ){
				wp_send_json('template-exist');

			}else if( in_array( $tname, $wpml_templates ) ){
				$lang = isset( $_POST['template_lang'] ) ? sanitize_text_field( $_POST['template_lang'] ) : false;
				if( $lang && array_key_exists( $tname.'-'.$lang, $wpml_templates ) ){
					wp_send_json('template-exist');
				}
			}
		}
		
		return false;
	}

	/**
	 * Setup template variables
	 */
	public function setup_template_variables( $posted ){
		$this->template_lang = isset( $posted['template_lang'] ) ? sanitize_text_field( $posted['template_lang'] ): '';
		if( THWEC_Utils::is_wpml_active() ){
			$this->default_lang = THWEC_Utils::get_wpml_locale( apply_filters( 'wpml_default_language', NULL ), true );
		}
		$this->display_name = isset( $posted['template_name'] ) ? sanitize_text_field( $posted['template_name'] ) : '';
		$this->file_name = isset( $posted['template_key'] ) && !empty( $posted['template_key'] ) ? sanitize_text_field( $posted['template_key'] ) : THWEC_Admin_Utils::prepare_template_file_name( $this->display_name );
	}

	/**
	 * Save the email template
	 *
	 * @param  array $posted posted values
	 */	
	public function initiate_save_template( $posted ){
		$save_meta = $save_files = true;
		$overwrite = isset( $_POST['overwrite'] ) && THWEC_Utils::validate_boolean( $_POST['overwrite'] );
		$this->setup_template_variables( $posted );
		$tag = THWEC_Utils::is_wpml_active() ? 'wpml-lang' : 'default';
		$template_meta = $this->get_template_meta( $tag );
		$template_name = isset( $template_meta['name'] ) ? $template_meta['name'] : false;
		$template_path = isset( $template_meta['path'] ) ? $template_meta['path'] : false;

		if( $template_name && $template_path ){
			//Save template files
			$file_exist = file_exists($template_path) ? true : false;
			$save_files = $this->save_template_files( $posted, $template_path );

			if($save_files ){
				//Save template meta data in DB
				$save_meta = $this->save_settings( $template_name, $posted );
			}

			if( $this->update_template ){
				$this->remove_non_wpml_template();
			}
		}
		
		$status = $save_files && $save_meta ? 'success' : 'failure';
		if( $status == 'failure' ){
			// Check if existing template is overwrited whithout any changes
			if( $overwrite && $save_files ){
				wp_send_json( 'overwrite-success' );
			}
			//Missing template file created
			$status = !$file_exist && $save_files ? 'created-missing' : $status;
		}
		wp_send_json( $status );
	}

	/**
	 * Remove the non wpml template when saving the same template created while WPML was inactive
	 *
	 * @return boolean if template removed and saved
	 */	
	public function remove_non_wpml_template(){
		$delete_file = $delete_data = false;
		$file = THWEC_CUSTOM_TEMPLATE_PATH.$this->file_name.'.php';
		if( file_exists( $file ) ){
			$delete_file = unlink( $file );
		}
		$settings = THWEC_Utils::get_template_settings();
		if( isset( $settings['templates'][$this->file_name] ) ){
			unset($settings['templates'][$this->file_name]);
		}
		$saved = THWEC_Utils::save_template_settings( $settings );
		return $delete_file && $saved;
	}

	/**
	 * Save the template file
	 *
	 * @param  array $posted posted values
	 * @param  string $path file path
	 * @return boolean $saved template file created or not
	 */	
	public function save_template_files( $posted, $path ){
		$content = isset($posted['template_render_data']) ? stripslashes($posted['template_render_data']) : '';
		$css = isset($posted['template_render_css']) ? stripslashes($posted['template_render_css']) : '';
		$css = $this->php8_comaptibiltiy_css( $css );
		$additional_css = isset($posted['template_add_css']) ? sanitize_textarea_field($posted['template_add_css']) : '';
		$css = $css.$additional_css;
		$content = $this->prepare_email_content_wrapper($content);
		$content = $this->html_wrapper( $content);
		$content = $this->style_inline( $content, $css );
		$content = $this->insert_dynamic_data($content);
		$saved = $this->generate_template_file($content, $path);
		return $saved;
	}

	/**
	 * Save the template settings
	 *
	 * @param  string $template_name template file name
	 * @param  array $posted posted values
	 * @return boolean $result settings saved or not
	 */	
	public function save_settings( $template_name, $posted ){
		$settings = THWEC_Utils::get_template_settings();
		$settings = $this->prepare_settings( $template_name, $posted, $settings);
		$result = THWEC_Utils::save_template_settings($settings);
		return $result;
	}

	/**
	 * Prepare individual template settings
	 *
	 * @param  string $template_name template file name
	 * @param  array $posted posted values
	 * @param  array $settings template settings
	 * @return array $settings templates settings
	 */
	public function prepare_settings( $template_name, $posted, $settings ){
		if( THWEC_Utils::is_wpml_active() ){
			//Create new template for translated template
			if( $this->is_default_lang_template() ){
				$data = $this->prepare_template_meta_data( $template_name, $posted );
				$this->update_template = $template_name;
			}else{
				if( ! $this->has_template_in_def_lang( $settings ) ){
					// If user first creates a template in secondary language, template data is created for default language inorder to prevent further issues.
					$def_lang_data = $this->prepare_template_meta_data( false, $posted, true, $this->default_lang );
					$settings['templates'][$this->def_lang_template_name()] = $def_lang_data;
					$settings = $this->update_wpml_template_map( $this->def_lang_template_name(), $settings);
					$this->prepare_template_file_in_def_lang();
					$this->update_template = $template_name;
				}
				$data = $this->prepare_template_meta_data( $template_name, $posted );
			}
			$settings = $this->update_wpml_template_map( $template_name, $settings);
		}else{
			$data = $this->prepare_template_meta_data( $template_name, $posted, false );
		}

		$settings['templates'][$template_name] = $data;
		return $settings;
	}

	/**
	 * Prepare template file in default WPML language
	 *
	 */
	public function prepare_template_file_in_def_lang(){
		$template_meta = $this->get_template_meta( 'wpml-default' );
		$template_name = isset( $template_meta['name'] ) ? $template_meta['name'] : false;
		$template_path = isset( $template_meta['path'] ) ? $template_meta['path'] : false;
		$this->save_template_files( $_POST, $template_path );
	}

	/**
	 * get the template meta data
	 *
	 * @param  string $template_name template file name
	 * @param  array $posted posted values
	 * @param  boolean $wpml_lang if wpml template 
	 * @param  boolean $lang_code wpml language code  
	 * @return array $data template meta data
	 */
	public function prepare_template_meta_data( $template_name, $posted, $wpml_lang=true, $lang_code=false ){
		$file_name = $template_name ? $template_name.'.php' : $this->def_lang_template_name().'.php';
		$json = isset($posted['template_json_tree']) ? stripslashes($posted['template_json_tree']) : '';
		$css = isset($posted['template_add_css']) ? sanitize_textarea_field($posted['template_add_css']) : '';
		$data = array();
		$data['file_name'] = $file_name;
		$data['display_name'] = $this->display_name;
		$data['template_data'] = $json;
		$data['additional_css'] = $css;
		$data['version'] = THWEC_VERSION;
		$data['time'] = date("Y-m-d H:i:s"); 
		if( $wpml_lang  ){
			$data['base'] = $this->file_name;
			$data['lang'] = $lang_code ? $lang_code : $this->template_lang;
		}
		return $data;
	}

	/**
	 * update template map
	 *
	 * @param  string $template_name template file name
	 * @param  array $settings template settings
	 * @return array $settings template settings
	 */
	public function update_wpml_template_map( $template_name, $settings ){
		$wpml_map = isset( $settings[THWEC_Utils::wpml_map_key()] ) ? $settings[THWEC_Utils::wpml_map_key()] : array();
		$wpml_map[$template_name] = $this->file_name;
		$settings[THWEC_Utils::wpml_map_key()] = $wpml_map;
		return $settings;
	}

	/*-------------------------------------------------------------------------------------------
	**------------------------------ DYNAMIC DATA AND PLACEHOLDERS-------------------------------
	**------------------------------------------------------------------------------------------*/

	/**
	 * Replace template contents
	 *
	 * @param  string $modified_data template content
	 * @param  boolean $preview if previewing
	 * @return string $modified_data template content
	 */
	public function insert_dynamic_data($modified_data, $preview=false ){
		
		/*-----------------------Placeholder Replacements ------------------------------*/
		$modified_data = $this->replace_thwec_placeholder_data( $modified_data, $preview );
		/*-----------------------Address Replacements ------------------------------*/
		$modified_data = str_replace('<span>{billing_address}</span>', $this->billing_data( $preview ), $modified_data);
		$modified_data = str_replace('<span>{thwec_before_shipping_address}</span>', $this->shipping_data_additional(true), $modified_data);
		$modified_data = str_replace('<span>{thwec_after_shipping_address}</span>', $this->shipping_data_additional(false), $modified_data);
		$modified_data = str_replace('<span>{shipping_address}</span>', $this->shipping_data(), $modified_data);
		$modified_data = str_replace('<span>{customer_address}</span>', $this->customer_data($preview), $modified_data);
		$modified_data = str_replace('<span>{before_customer_table}</span>', $this->add_order_head(), $modified_data);
		$modified_data = str_replace('<span>{after_customer_table}</span>', $this->add_order_foot(), $modified_data);
		$modified_data = str_replace('<span>{before_shipping_table}</span>', $this->add_order_head(), $modified_data);
		$modified_data = str_replace('<span>{after_shipping_table}</span>', $this->add_order_foot(), $modified_data);
		$modified_data = str_replace('<span>{before_billing_table}</span>', $this->add_order_head(), $modified_data);
		$modified_data = str_replace('<span>{after_billing_table}</span>', $this->add_order_foot(), $modified_data);
		$modified_data = str_replace('<span>{before_order_table}</span>', $this->add_order_head(), $modified_data);
		$modified_data = str_replace('<span>{after_order_table}</span>', $this->add_order_foot(), $modified_data);
		
		/*-----------------------Order Table Replacements ------------------------------*/

		$modified_data = str_replace('<span class="loop_start_before_order_table"></span>', $this->order_table_before_loop(), $modified_data); //woocommerce_email_before_order_table 
		$modified_data = str_replace('<span class="loop_end_after_order_table"></span>', $this->order_table_after_loop(), $modified_data); //woocommerce_email_before_order_table 
		$modified_data = str_replace('<span class="woocommerce_email_before_order_table"></span>', $this->order_table_before_hook(), $modified_data); //woocommerce_email_before_order_table 
		$modified_data = str_replace('{Order_Product}', $this->order_table_header_product(), $modified_data); //first row content
		$modified_data = str_replace('{Order_Quantity}', $this->order_table_header_qty(), $modified_data); //first row content
		$modified_data = str_replace('{Order_Price}', $this->order_table_header_price(), $modified_data);//first row content
		$modified_data = str_replace('<tr class="item-loop-start"></tr>', $this->order_table_item_loop_start(), $modified_data); // product display loop start
		$modified_data = str_replace('woocommerce_order_item_class-filter1', $this->order_table_class_filter(), $modified_data); // woocommerce filter as class for a <td>
		$modified_data = str_replace('{order_items}', $this->order_table_items(), $modified_data); // Code to display  items without image
		$modified_data = str_replace('{order_items_img}', $this->order_table_items(true), $modified_data); // Code to display  items along with image
		$modified_data = str_replace('{order_items_qty}', $this->order_table_items_qty(), $modified_data);// Code to display  item quantity
		$modified_data = str_replace('{order_items_price}', $this->order_table_items_price(), $modified_data); // Code to display  item price
		$modified_data = str_replace('<tr class="item-loop-end"></tr>',$this->order_table_item_loop_end(), $modified_data);  // product display loop end
		$modified_data = str_replace('<tr class="order-total-loop-start"></tr>', $this->order_table_total_loop_start(), $modified_data); //totals display loop start
		$modified_data = str_replace('{total_label}', $this->order_table_total_labels(), $modified_data); // Code to display <tfoot> total labels
		$modified_data = str_replace('{total_value}', $this->order_table_total_values(), $modified_data); // Code to display <tfoot> total values
		// $modified_data = str_replace('<tr class="order-total-loop-end"></tr>', $this->order_table_total_loop_end(), $modified_data); // totals display loop start

		/*----------------------- Woocommerce Email Hooks ------------------------------*/
		$modified_data = $this->replace_woocommerce_hooks_contents($modified_data);
		
		/*---------------- Checkout fields in email at any position -----------------*/ 
		$modified_data = $this->thwec_shortcode_callbacks($modified_data);

		$modified_data = str_replace('<span>{before_ywgc_block}</span>', $this->ywgc_before(), $modified_data);
		$modified_data = str_replace('<span>{after_ywgc_block}</span>', $this->ywgc_after(), $modified_data);
		return $modified_data;
	}

	/**
	 * Replace template content with placeholders
	 *
	 * @param  string $modified_data template content
	 * @return string $modified_data template content
	 */
	public function replace_thwec_placeholder_data($modified_data, $preview){
		$modified_data = str_replace('{customer_name}', $this->get_customer_name(), $modified_data);
		$modified_data = str_replace('{customer_full_name}', $this->get_customer_full_name(), $modified_data);
		$modified_data = str_replace('{user_email}', $this->get_user_email(), $modified_data);
		//Site Related
		$modified_data = str_replace('{site_url}', $this->get_site_url(), $modified_data);
		$modified_data = str_replace('{site_name}', $this->get_site_name(), $modified_data);

		// Order Related
		$modified_data = str_replace('{order_id}', $this->get_order_id(), $modified_data);
		$modified_data = str_replace('{order_number}', $this->get_order_number(), $modified_data); //Filtered order id by 3rd party plugins
		$modified_data = str_replace('{order_url}', $this->get_order_url(), $modified_data);
		$modified_data = str_replace('{order_completed_date}', $this->get_order_completed_date(), $modified_data);
		$modified_data = str_replace('{order_created_date}', $this->get_order_created_date(), $modified_data);
		
		$modified_data = str_replace('{order_total}', $this->get_order_total(), $modified_data);
		$modified_data = str_replace('{order_subtotal}', $this->get_order_subtotal(), $modified_data);
		$modified_data = str_replace('{order_formatted_subtotal}', $this->get_order_subtotal_with_currency(), $modified_data);

		$modified_data = str_replace('{order_formatted_total}', $this->get_order_formatted_total(), $modified_data);

		//Billing
		$modified_data = str_replace('{billing_first_name}', $this->get_default_woocommerce_method('billing_first_name', true), $modified_data);
		$modified_data = str_replace('{billing_last_name}', $this->get_default_woocommerce_method('billing_last_name', true), $modified_data);
		$modified_data = str_replace('{billing_company}', $this->get_default_woocommerce_method('billing_company', true), $modified_data);
		$modified_data = str_replace('{billing_country}', $this->get_default_woocommerce_method('billing_country', true), $modified_data);
		$modified_data = str_replace('{billing_address_1}', $this->get_default_woocommerce_method('billing_address_1', true), $modified_data);
		$modified_data = str_replace('{billing_address_2}', $this->get_default_woocommerce_method('billing_address_2', true), $modified_data);
		$modified_data = str_replace('{billing_city}', $this->get_default_woocommerce_method('billing_city', true), $modified_data);
		$modified_data = str_replace('{billing_state}', $this->get_default_woocommerce_method('billing_state', true), $modified_data);
		$modified_data = str_replace('{billing_postcode}', $this->get_default_woocommerce_method('billing_postcode', true), $modified_data);
		$modified_data = str_replace('{billing_phone}', $this->get_default_woocommerce_method('billing_phone', true), $modified_data);
		$modified_data = str_replace('{billing_email}', $this->get_default_woocommerce_method('billing_email', true), $modified_data);
		
		// Shipping
		$modified_data = str_replace('{shipping_method}', $this->get_shipping_method(), $modified_data);
		$modified_data = str_replace('{shipping_first_name}', $this->get_default_woocommerce_method('shipping_first_name', true), $modified_data);
		$modified_data = str_replace('{shipping_last_name}', $this->get_default_woocommerce_method('shipping_last_name', true), $modified_data);
		$modified_data = str_replace('{shipping_company}', $this->get_default_woocommerce_method('shipping_company', true), $modified_data);
		$modified_data = str_replace('{shipping_country}', $this->get_default_woocommerce_method('shipping_country', true), $modified_data);
		$modified_data = str_replace('{shipping_address_1}', $this->get_default_woocommerce_method('shipping_address_1', true), $modified_data);
		$modified_data = str_replace('{shipping_address_2}', $this->get_default_woocommerce_method('shipping_address_2', true), $modified_data);
		$modified_data = str_replace('{shipping_city}', $this->get_default_woocommerce_method('shipping_city', true), $modified_data);
		$modified_data = str_replace('{shipping_state}', $this->get_default_woocommerce_method('shipping_state', true), $modified_data);
		$modified_data = str_replace('{shipping_postcode}', $this->get_default_woocommerce_method('shipping_postcode', true), $modified_data);
		
		//Misc
		$modified_data = str_replace('{checkout_payment_url}', $this->get_order_checkout_payment_url(), $modified_data);
		$modified_data = str_replace('{payment_method}', $this->get_order_payment_method(), $modified_data);
		$modified_data = str_replace('{customer_note}', $this->get_customer_note( $preview ), $modified_data);
		$modified_data = str_replace('{customer_note_plain_text}', $this->get_customer_note_plain_text( $preview ), $modified_data);

		//Account Related
		$modified_data = str_replace('{user_login}', $this->get_user_login(), $modified_data);
		$modified_data = str_replace('{user_pass}', $this->get_user_pass(), $modified_data);
		$modified_data = str_replace('{account_area_url}', $this->get_account_area_url(), $modified_data);
		$modified_data = str_replace('{account_order_url}', $this->get_account_order_url(), $modified_data);
		$modified_data = str_replace('{reset_password_url}', $this->get_reset_password_url($preview), $modified_data);
		$modified_data = str_replace('{set_password_url}', $this->get_new_password_url($preview), $modified_data);
		
		//Deprecated placholders - Will be removed in version 3.7.0
		$modified_data = str_replace('{th_customer_name}', $this->get_customer_name(), $modified_data);
		$modified_data = str_replace('{th_billing_phone}', $this->get_default_woocommerce_method('billing_phone', true), $modified_data);
		$modified_data = str_replace('{th_order_id}', $this->get_order_id(), $modified_data);
		$modified_data = str_replace('{th_order_url}', $this->get_order_url(), $modified_data);
		$modified_data = str_replace('{th_billing_email}', $this->get_default_woocommerce_method('billing_email', true), $modified_data);
		$modified_data = str_replace('{th_site_url}', $this->get_site_url(), $modified_data);
		$modified_data = str_replace('{th_site_name}', $this->get_site_name(), $modified_data);
		$modified_data = str_replace('{th_order_completed_date}', $this->get_order_completed_date(), $modified_data);
		$modified_data = str_replace('{th_order_created_date}', $this->get_order_created_date(), $modified_data);
		$modified_data = str_replace('{th_checkout_payment_url}', $this->get_order_checkout_payment_url(), $modified_data);
		$modified_data = str_replace('{th_payment_method}', $this->get_order_payment_method(), $modified_data);
		$modified_data = str_replace('{th_customer_note}', $this->get_customer_note( $preview ), $modified_data);
		$modified_data = str_replace('{th_user_login}', $this->get_user_login(), $modified_data);
		$modified_data = str_replace('{th_user_pass}', $this->get_user_pass(), $modified_data);
		$modified_data = str_replace('{th_account_area_url}', $this->get_account_area_url(), $modified_data);
		$modified_data = str_replace('{th_reset_password_url}', $this->get_reset_password_url($preview), $modified_data);

		if( $this->ywgc_active ){
			$modified_data = str_replace('{yith_gift_card_name}', $this->get_ywgc_name(), $modified_data);
			$modified_data = str_replace('{yith_gift_card_price}', $this->get_ywgc_price(), $modified_data);
			$modified_data = str_replace('{yith_gift_card_message}', $this->get_ywgc_message(), $modified_data);
			$modified_data = str_replace('{yith_gift_card_description}', $this->get_ywgc_description(), $modified_data);
			$modified_data = str_replace('{yith_gift_card_sender_name}', $this->get_ywgc_sender_name(), $modified_data);
			$modified_data = str_replace('{yith_gift_card_recipient_name}', $this->get_ywgc_recipient_name(), $modified_data);
			$modified_data = str_replace('{yith_gift_card_code}', $this->get_ywgc_number(), $modified_data);
			$modified_data = str_replace('{yith_gift_card_recipient_email}', $this->get_ywgc_recipient_email(), $modified_data);
			$modified_data = str_replace('{yith_gift_card_discount_button_text}', $this->get_ywgc_discount_button_text(), $modified_data);
			$modified_data = str_replace('{yith_gift_card_discount_url}', $this->get_ywgc_discount_button_url(), $modified_data);
			$modified_data = str_replace('{yith_gift_card_discount_href}', 'href="'.$this->get_ywgc_discount_button_url().'"', $modified_data);

			$modified_data = str_replace('<span>yith_gift_card_discount_button_before</span>', $this->get_ywgc_discount_button_helper(), $modified_data);
			$modified_data = str_replace('<span>yith_gift_card_discount_button_after</span>', $this->get_ywgc_discount_button_helper( true ), $modified_data);
		}

		return apply_filters('thwec_email_placeholders', $modified_data);;
	}

	/**
	 * YITH Discount button text
	 *
	 * @param  boolean $tag php tag necessary or not
	 * @return string $content button content
	 */
	public function get_ywgc_discount_button_text( $tag=true ){
		$content = 'empty( $email_button_label_get_option ) ? esc_html__(\'Apply your gift card code\', \'yith-woocommerce-gift-cards\' ) : $email_button_label_get_option';
		if( $tag ){
			$content = '<?php echo '.$content.'; ?>';
		}
		return $content;
	}

	/**
	 * YITH gift card button url
	 */
	public function get_ywgc_discount_button_url(){
		return '<?php echo apply_filters ( \'yith_ywgc_email_automatic_cart_discount_url\', $apply_discount_url,$args, $gift_card ); ?>';
	}

	/**
	 * YITH Discount button link
	 *
	 * @param  string $styles inline styles
	 * @return string content
	 */
	public function get_ywgc_discount_button_link( $styles ){
		return '<a href="'.$this->get_ywgc_discount_button_url().'" target="_blank" class="thwec-ygc-button-link" style="text-decoration:none;'.$styles.'"><?php echo strtoupper('.$this->get_ywgc_discount_button_text( false ).'); ?></a>';
	}

	/**
	 * YITH gift card discount button helper
	 *
	 * @param  boolean $after closing if condition or not
	 * @return string $content content
	 */
	public function get_ywgc_discount_button_helper( $after = false ){
		$content = '';
		if( $after ){
			$content .= '<?php endif; ?>';
		}else{
			$content .= '<?php if ( isset( $gift_card ) && $gift_card instanceof YWGC_Gift_Card_Premium && "no" != get_option ( "ywgc_auto_discount_button_activation", \'yes\' )  && ! $gift_card->product_as_present ): ';
			$content .= '$email_button_label_get_option = get_option ( \'ywgc_email_button_label\', esc_html__( \'Apply your gift card code\', \'yith-woocommerce-gift-cards\' ) );';
			$content .= '$shop_page_url = apply_filters( \'yith_ywgc_shop_page_url\', get_permalink ( wc_get_page_id ( \'shop\' ) ) ? get_permalink ( wc_get_page_id ( \'shop\' ) ) : site_url () );';
			$content .= '$args = array();';

            $content .= 'if ( get_option ( \'ywgc_auto_discount\', \'yes\' ) != \'no\' ){
                    $args = array(
                        YWGC_ACTION_ADD_DISCOUNT_TO_CART => $gift_card->gift_card_number,
                        YWGC_ACTION_VERIFY_CODE          => YITH_YWGC ()->hash_gift_card ( $gift_card ),
                    );
                }';

            $content .= 'if ( get_option ( \'ywgc_redirected_page\', \'home_page\' ) ){
                    $apply_discount_url = esc_url ( add_query_arg ( $args, get_page_link( get_option ( \'ywgc_redirected_page\', \'home_page\' ) ) ) );
                }
                else{
                    $apply_discount_url = esc_url ( add_query_arg ( $args, $shop_page_url ) );
                } ?>';
		}
		return $content;
	}

	/**
	 * YITH gift card sender name
	 */
	public function get_ywgc_sender_name(){
		$content = '<?php ';
		$content .= 'if( isset( $gift_card ) && $gift_card instanceof YWGC_Gift_Card_Premium ){ ';
		$content .= 'echo $gift_card->sender_name; ';
		$content .= '} ?>';
		return $content;
	}

	/**
	 * YITH gift card recipient name
	 */
	public function get_ywgc_recipient_name(){
		$content = '<?php ';
		$content .= 'if( isset( $gift_card ) && $gift_card instanceof YWGC_Gift_Card_Premium ){ ';
		$content .= 'echo $gift_card->recipient_name; ';
		$content .= '} ?>';
		return $content;
	}

	/**
	 * YITH gift card number
	 */
	public function get_ywgc_number(){
		$content = '<?php ';
		$content .= 'if( isset( $gift_card ) && $gift_card instanceof YWGC_Gift_Card_Premium ){ ';
		$content .= 'echo $gift_card->gift_card_number; ';
		$content .= '} ?>';
		return $content;
	}

	/**
	 * YITH gift card recipient email
	 */
	public function get_ywgc_recipient_email(){
		$content = '<?php ';
		$content .= 'if( isset( $gift_card ) && $gift_card instanceof YWGC_Gift_Card_Premium ){ ';
		$content .= 'echo $gift_card->recipient; ';
		$content .= '} ?>';
		return $content;
	}

	/**
	 * YITH gift card expiry row
	 *
	 * @param  string $style inline styles
	 * @param  string $title inline styles
	 * @param  string $title inline styles
	 * @return string $content content
	 */
	public function get_ywgc_code_row( $style, $title, $code ){
		$content ='<tr>
				<td class="ywgc-card-code-column" <?php echo get_option( \'ywgc_display_qr_code\' , \'no\') == \'yes\' ? \'style="width:73%;'.$style.'"\' : \'colspan="2" style="'.$style.'"\'; ?> >
					<span class="ywgc-card-code-title" style="'.$title.'">
					<?php echo apply_filters(\'ywgc_preview_code_title\', esc_html__( "Gift card code:", \'yith-woocommerce-gift-cards\' ) ); ?>
					</span>
					<br>
					<br>
					<span class="ywgc-card-code" style="'.$code.'">
						<?php echo isset( $gift_card ) && $gift_card instanceof YWGC_Gift_Card_Premium ? $gift_card->gift_card_number : ""; ?>
					</span>
				</td>
				<?php if ( get_option( \'ywgc_display_qr_code\' , \'no\' ) == \'yes\' ): ?>
			        <td class="ywgc-card-qr-code" style="width: 27%;'.$style.'">
			        	<?php if( isset( $gift_card ) && $gift_card instanceof YWGC_Gift_Card_Premium ):
				        	$shop_page_url = apply_filters( \'yith_ywgc_shop_page_url_qr\', get_permalink ( wc_get_page_id ( \'shop\' ) ) ? get_permalink ( wc_get_page_id ( \'shop\' ) ) : site_url () );
							$apply_discount_url = $shop_page_url . \'?ywcgc-add-discount=\' . $gift_card->gift_card_number . \'%26ywcgc-verify-code=\' . YITH_YWGC ()->hash_gift_card ( $gift_card ); ?>
				        	<img class="ywgc-card-qr-code-image" src="https://chart.googleapis.com/chart?chs=120x120&cht=qr&chl=<?php echo $apply_discount_url ; ?>" />
				        <?php endif; ?>
			        </td>
			    <?php endif; ?>
			</tr>';
		return $content;
	}

	/**
	 * YITH gift card expiry row
	 *
	 * @param  string $styles inline styles
	 * @return string $content content
	 */
	public function get_ywgc_expiry_row($styles){
		$content = '<?php if( isset( $gift_card ) && $gift_card instanceof YWGC_Gift_Card_Premium ) : ';
		$content .= '$date_format = apply_filters(\'yith_wcgc_date_format\',\'Y-m-d\');';
		$content .= '$expiration_date = !is_numeric($gift_card->expiration) ? strtotime( $gift_card->expiration ) : $gift_card->expiration;';
		$content .= 'if ( get_option( \'ywgc_display_expiration_date\', \'no\' ) == "yes" && $gift_card->expiration ) : ';
        $content .= '$expiration_message = apply_filters ( \'yith_ywgc_gift_card_email_expiration_message\',
            sprintf ( _x ( \'This gift card code will be valid until %s (%s)\', \'gift card expiration date\', \'yith-woocommerce-gift-cards\' ),date_i18n ( $date_format, $expiration_date ) , get_option( \'ywgc_plugin_date_format_option\', \'yy-mm-dd\' )), $gift_card, \'email\'); ?>';
        $content .= '<tr>
            <td colspan="2" style="'.$styles.'" class="ywgc-expiration-message"><?php echo $expiration_message; ?></td></tr>';
        $content .= '<?php endif; endif; ?>';
        return $content;
	}

	/**
	 * YITH gift card description row
	 *
	 * @param  string $styles inline styles
	 * @return string $content content
	 */
	public function get_ywgc_description_row($styles){
		$content = '<?php ';
		$content .= 'if( isset( $gift_card ) && $gift_card instanceof YWGC_Gift_Card_Premium ) : ';
		$content .= 'if ( get_option( \'ywgc_display_description_template\', \'no\' ) == "yes" ) : ?>';
		$content .= '<tr>';
        $content .= '<td colspan="2" class="ywgc-card-description ywgc-description-template-email-message" style="'.$styles.'"><?php echo get_option( \'ywgc_description_template_email_text\', esc_html__( "To use this gift card, you can either enter the code in the gift card field on the cart page or click on the following link to automatically get the discount.", \'yith-woocommerce-gift-cards\' ) ); ?></td>';
        $content .= '</tr>';
        $content .= '<?php endif; endif; ?>';
        return $content;
	}

	/**
	 * YITH gift card description
	 */
	public function get_ywgc_description(){
		$content = '<?php if( isset( $gift_card ) && $gift_card instanceof YWGC_Gift_Card_Premium ) : ';
		$content .= 'if ( get_option( \'ywgc_display_description_template\', \'no\' ) == "yes" ) : ';
        $content .= 'echo get_option( \'ywgc_description_template_email_text\', esc_html__( "To use this gift card, you can either enter the code in the gift card field on the cart page or click on the following link to automatically get the discount.", \'yith-woocommerce-gift-cards\' ) ); ';
        $content .= 'endif; endif; ?>';
        return $content;
	}

	/**
	 * YITH gift card message row
	 */
	public function get_ywgc_message_row($styles){
		$content = '<?php ';
		$content .= 'if( isset( $gift_card ) && $gift_card instanceof YWGC_Gift_Card_Premium ) :';
		$content .= '$message = $gift_card->message;';
		$content .= 'if($message): ?>';
		$content .= '<tr><td class="ywgc-card-message" colspan="2" style="'.$styles.'">';
		$content .= '<?php echo nl2br(str_replace( "\\\","",$message )); ?>';
		$content .= '</td></tr>';
		$content .= '<?php endif; endif; ?>';
		return $content;
	}

	/**
	 * YITH gift card message
	 */
	public function get_ywgc_message(){
		$content = '<?php ';
		$content .= '$message = isset( $gift_card ) && $gift_card instanceof YWGC_Gift_Card_Premium ? $gift_card->message : ""; ';
		$content .= 'if($message): ';
		$content .= 'echo nl2br(str_replace( "\\\","",$message )); ';
		$content .= 'endif; ?>';
		return $content;
	}

	/**
	 * YITH gift card name
	 */
	public function get_ywgc_name(){
		$content = '<?php if( isset( $gift_card ) && $gift_card instanceof YWGC_Gift_Card_Premium ) : ';
		$content .= '$product_id = isset($gift_card->product_id) ? $gift_card->product_id : \'\'; ';
		$content .= '$product = wc_get_product( $product_id ); ';
		$content .= '$product_name_text = is_object( $product ) && $product instanceof WC_Product_Gift_Card && $gift_card->product_as_present != 1 ? $product->get_name() : esc_html__( "Gift card", \'yith-woocommerce-gift-cards\' ); ';
	    $content .= 'echo apply_filters( \'yith_wcgc_template_product_name_text\', $product_name_text . \' \' . esc_html__( "on", \'yith-woocommerce-gift-cards\' )  . \' \' . get_bloginfo( \'name\' ) , $gift_card, "email", $product_id ); ';
	    $content .= 'endif; ?>';
	    return $content;
	}

	/**
	 * YITH gift card price row
	 *
	 * @param  string $styles inline styles
	 * @return string $content content
	 */
	public function get_ywgc_name_price_row( $styles ){
		$content = '<?php if( isset( $gift_card ) && $gift_card instanceof YWGC_Gift_Card_Premium ) : ';
		$content .= '$amount = $gift_card->total_amount; ';
		$content .= '$formatted_price = apply_filters ( \'yith_ywgc_gift_card_template_amount\', wc_price ( $amount ), $gift_card, $amount ); ';
		$content .= '$product_id = isset($gift_card->product_id) ? $gift_card->product_id : \'\'; ?>';
		$content .= '<tr>';
		$content .= '<?php $ywgc_display_price = get_option( \'ywgc_display_price\', \'yes\' ); ?>';
	    $content .= '<td style="'.$styles.'" class="ywgc-card-product-name" <?php echo apply_filters( \'ywgc_display_price_template\', true, $formatted_price, $gift_card, "email" ) && \'yes\' === $ywgc_display_price ? "" : \'colspan="2"\'; ?>>
            <?php
            $product = wc_get_product( $product_id );
            $product_name_text =  is_object( $product ) && $product instanceof WC_Product_Gift_Card && $gift_card->product_as_present != 1 ? $product->get_name() : esc_html__( "Gift card", \'yith-woocommerce-gift-cards\' );
            echo apply_filters( \'yith_wcgc_template_product_name_text\', $product_name_text . \' \' . esc_html__( "on", \'yith-woocommerce-gift-cards\' )  . \' \' . get_bloginfo( \'name\' ) , $gift_card, "email", $product_id ); ?>
        	</td>
        	<?php if ( apply_filters( \'ywgc_display_price_template\', true, $formatted_price, $gift_card, "email" ) && \'yes\' === $ywgc_display_price ) : ?>
            <td class="ywgc-card-amount" valign="bottom" style="'.$styles.'">
                <?php echo apply_filters( \'yith_wcgc_template_formatted_price\', $formatted_price, $gift_card, "email" ); ?>
            </td>
        <?php endif;';
        $content .= 'endif; ?>';
        return $content;
	}

	/**
	 * YITH gift card price
	 */
	public function get_ywgc_price(){
		$content = '<?php if( isset( $gift_card ) && $gift_card instanceof YWGC_Gift_Card_Premium ) : ';
		$content .= '$amount = $gift_card->total_amount; ';
		$content .= '$formatted_price = apply_filters ( \'yith_ywgc_gift_card_template_amount\', wc_price ( $amount ), $gift_card, $amount ); ';
		$content .= 'if ( apply_filters( \'ywgc_display_price_template\', true, $formatted_price, $gift_card, "email" ) && \'yes\' === get_option( \'ywgc_display_price\', \'yes\' ) ) : 
                echo apply_filters( \'yith_wcgc_template_formatted_price\', $formatted_price, $gift_card, "email" ); 
        endif; endif; ?>';
        return $content;
	}

	/**
	 * YITH gift card header
	 *
	 * @param  string $styles unformatted styles
	 * @param  string $col_styles inline styles
	 * @return string $content content
	 */
	public function ywgc_header_css( $styles, $col_styles ){
		$logo_width = '';
		$logo_height ='';
		$img_width = '';
		$img_height = '';
		$styles = explode(";", $styles);
		if( is_array( $styles ) ){
			foreach ($styles as $key => $style) {
				$data = explode(":", $style);
				if( isset( $data[0] ) ){
					if( $data[0] == 'logo_width' ){
						$logo_width = $data['1'];

					}else if( $data[0] == 'logo_height' ){
						$logo_height = $data['1'];

					}else if( $data[0] == 'img_width' ){
						$img_width = $data['1'];

					}else if( $data[0] == 'img_height' ){
						$img_height = $data['1'];

					}
				}
			}
		}
				
		$content = '<?php if( isset( $gift_card ) && $gift_card instanceof YWGC_Gift_Card_Premium ) {';

		$content .= '$company_logo_url =  ( "yes" == get_option ( "ywgc_shop_logo_on_gift_card", \'no\' ) ) ? get_option ( "ywgc_shop_logo_url", YITH_YWGC_ASSETS_IMAGES_URL . \'default-giftcard-main-image.png\' ) : \'\';';
		$content .= '$ywgc_instance = YITH_WooCommerce_Gift_Cards_Premium::get_instance();';
		$content .= '$header_image_url = $ywgc_instance->get_header_image( $gift_card );';
		$content .= '$default_header_image_url = $ywgc_instance->get_default_header_image();';
		$content .= '$amount = $gift_card->total_amount;';
		$content .= '$formatted_price = apply_filters ( \'yith_ywgc_gift_card_template_amount\', wc_price ( $amount ), $gift_card, $amount );';

		$content .= 'if ( isset( $header_image_url ) ){
				if ( strpos( $header_image_url, \'-yith_wc_gift_card_premium_separator_ywgc_template_design-\') !== false ) {
					$array_header_image_url = explode( "-yith_wc_gift_card_premium_separator_ywgc_template_design-", $header_image_url );
					$header_image_url = $array_header_image_url[\'1\'];
				}
			}';

		$content .= 'if ( isset( $company_logo_url ) && $company_logo_url && get_option( \'ywgc_shop_logo_on_gift_card_before\', \'no\' ) == \'yes\' ) {?>
					<tr>
						<td colspan="2" class="ywgc-logo" style="'.$col_styles.'" align="<?php echo get_option( \'ywgc_shop_logo_before_alignment\', \'center\' ) ?>">
								<img class="ywgc-logo-shop-image" src="<?php echo apply_filters( \'ywgc_custom_company_logo_url\', $company_logo_url ); ?>" alt="<?php _e( "The shop logo for the gift card", \'yith-woocommerce-gift-cards\' ); ?>"
                         title="<?php _e( "The shop logo for the gift card", \'yith-woocommerce-gift-cards\' ); ?>" width="'.$logo_width.'" height="'.$logo_height.'">
						</td>
					</tr>
				<?php } ?>';

		$content .= '<?php if ( $header_image_url = apply_filters( \'ywgc_custom_header_image_url\', preg_replace(\'/^https(?=:\/\/)/i\',\'http\',$header_image_url) ) ){

			        // This add the default gift card image when the image is lost
			        if ( substr($header_image_url, -strlen(\'/\'))=== \'/\' )
			            $header_image_url = $default_header_image_url;

			        ?>
					<tr>
						<td colspan="2" class="ywgc-image" style="'.$col_styles.'">
							<img src="<?php echo $header_image_url; ?>" class="ywgc-main-image" width="'.$img_width.'" height="'.$img_height.'" style="width:'.$img_width.'px;height:'.$img_height.'px;" alt="<?php _e( "Gift card image", \'yith-woocommerce-gift-cards\' ); ?>"
                         title="<?php _e( "Gift card image", \'yith-woocommerce-gift-cards\' ); ?>">
						</td>
					</tr>
				<?php } ?>';

		$content .= '<?php if ( isset( $company_logo_url ) && $company_logo_url && get_option( \'ywgc_shop_logo_on_gift_card_after\', \'no\' ) == \'yes\' ) { ?>
					<tr>
						<td colspan="2" class="ywgc-logo" style="'.$col_styles.'" align="<?php echo get_option( \'ywgc_shop_logo_after_alignment\', \'center\' ) ?>">
							<img class="ywgc-logo-shop-image" src="<?php echo apply_filters( \'ywgc_custom_company_logo_url\', $company_logo_url ); ?>" alt="<?php _e( "The shop logo for the gift card", \'yith-woocommerce-gift-cards\' ); ?>"
                         title="<?php _e( "The shop logo for the gift card", \'yith-woocommerce-gift-cards\' ); ?>" width="'.$logo_width.'" height="'.$logo_height.'">
						</td>
					</tr>
				<?php } ?>';

		$content .= '<?php } ?>';
		return $content;
	}

	/**
	 * Replace WooCommerce hook contents
	 *
	 * @param  string $modified_data content
	 * @param  string $mail_type test-mail or not
	 * @return string $modified_data content
	 */
	public function replace_woocommerce_hooks_contents($modified_data,$mail_type=false){
		$modified_data = str_replace('<p class="hook-code">{email_header_hook}</p>', $this->thwec_email_hooks('{email_header_hook}'), $modified_data);
		$modified_data = str_replace('<p class="hook-code">{email_order_details_hook}</p>', $this->thwec_email_hooks('{email_order_details_hook}'), $modified_data);
		$modified_data = str_replace('<p class="hook-code">{before_order_table_hook}</p>', $this->thwec_email_hooks('{before_order_table_hook}'), $modified_data);
		$modified_data = str_replace('<p class="hook-code">{after_order_table_hook}</p>', $this->thwec_email_hooks('{after_order_table_hook}'), $modified_data);
		$modified_data = str_replace('<p class="hook-code">{order_meta_hook}</p>', $this->thwec_email_hooks('{order_meta_hook}'), $modified_data);
		$modified_data = str_replace('<p class="hook-code">{customer_details_hook}</p>', $this->thwec_email_hooks('{customer_details_hook}'), $modified_data);
		$modified_data = str_replace('<p class="hook-code">{email_footer_hook}</p>', $this->thwec_email_hooks('{email_footer_hook}'), $modified_data);
		$modified_data = str_replace('<p class="hook-code">{downloadable_product_table}</p>',$this->downloadable_product_table($mail_type), $modified_data);
		return $modified_data;
	}

	/**
	 * shortcode callback functions
	 *
	 * @param  string $modified_data content
	 * @param  string $shortcode_show show shortcode
	 * @return string $modified_data content
	 */
	public function thwec_shortcode_callbacks($modified_data,$shortcode_show=true){
		if($shortcode_show){
			$modified_data = preg_replace_callback("/$this->wcfe_pattern/", array($this, "special_wcfe_meta_functions"),$modified_data);
		}
		$modified_data = preg_replace_callback("/$this->wecm_custom_hook/", array($this, "special_wecm_custom_hook_functions"),$modified_data);
		$modified_data = preg_replace_callback("/$this->wecm_order_table_helper/", array($this, "special_wecm_order_table_helper_functions"),$modified_data);
		$modified_data = preg_replace_callback("/$this->wecm_order_table_head/", array($this, "special_wecm_order_table_head_functions"),$modified_data);
		$modified_data = preg_replace_callback("/$this->wecm_order_table_td/", array($this, "special_wecm_order_table_td_functions"),$modified_data);
		$modified_data = preg_replace_callback("/$this->wecm_order_item/", array($this, "wecm_order_item_functions"),$modified_data);

		if( $this->ywgc_active ){
			$modified_data = preg_replace_callback("/$this->wecm_ywgc/", array($this, "wecm_ywgc_functions"), $modified_data);
		}

		return $modified_data;
	}

	/**
	 * Get the shortcode attributes
	 *
	 * @param  array $occurances occurances
	 * @return array $atts attributes
	 */
	private function get_shortcode_atts( $occurances ){
		$atts = array();
		if ( $occurances[1] == '[' && $occurances[6] == ']' ) {
			return substr($occurances[0], 1, -1);
		}
		$sec_pattern = $this->get_th_shortcode_atts_regex();
		$content = preg_replace("/[\x{00a0}\x{200b}]+/u", " ", $occurances[3]);
		if ( preg_match_all($sec_pattern, $content, $match, PREG_SET_ORDER) ) {
			foreach ($match as $m) {
				if (!empty($m[1]))
					$atts[strtolower($m[1])] = stripcslashes($m[2]);
				elseif (!empty($m[3]))
					$atts[strtolower($m[3])] = stripcslashes($m[4]);
				elseif (!empty($m[5]))
					$atts[strtolower($m[5])] = stripcslashes($m[6]);
				elseif (isset($m[7]) && strlen($m[7]))
					$atts[] = stripcslashes($m[7]);
				elseif (isset($m[8]) && strlen($m[8]))
					$atts[] = stripcslashes($m[8]);
				elseif (isset($m[9]))
					$atts[] = stripcslashes($m[9]);
			}
		}
		return $atts;
	}

	/**
	 * Get YITH compatibility shortcode attributes and content
	 *
	 * @param  array $occurances occurances
	 * @return string $replace_html content
	 */
	public function wecm_ywgc_functions( $occurances ){
		$atts = $this->get_shortcode_atts($occurances);
		$replace_html = '';
		if( !empty( $atts ) ){
			$feature = isset( $atts['name'] ) && !empty( $atts['name'] ) ? $atts['name'] : false;
			$ywgc_style1 = isset($atts['style1']) && !empty($atts['style1']) ? $atts['style1'] : false;
			$ywgc_style2 = isset($atts['style2']) && !empty($atts['style2']) ? $atts['style2'] : '';
			$ywgc_style3 = isset($atts['style3']) && !empty($atts['style3']) ? $atts['style3'] : '';
			if( $feature ){
				if( $feature == 'header' ){
					$replace_html = $this->ywgc_header_css($ywgc_style1,$ywgc_style2);

				}else if( $feature == 'name-price' ){
					$replace_html = $this->get_ywgc_name_price_row($ywgc_style1);

				}else if( $feature == 'message' ){
					$replace_html = $this->get_ywgc_message_row($ywgc_style1);

				}else if( $feature == 'description' ){
					$replace_html = $this->get_ywgc_description_row($ywgc_style1);
				
				}else if( $feature == 'code' ){
					$replace_html = $this->get_ywgc_code_row($ywgc_style1, $ywgc_style2, $ywgc_style3);

				}else if( $feature == 'expiry' ){
					$replace_html = $this->get_ywgc_expiry_row($ywgc_style1);

				}else if( $feature == 'discount-button-link'){
					$replace_html = $this->get_ywgc_discount_button_link($ywgc_style2);

				}
			}
		}
		return $replace_html;
		
	}

	/**
	 * Get order table column shortcode attributes and contents
	 *
	 * @param  array $occurances occurances
	 * @return string $replace_html content
	 */
	public function special_wecm_order_table_td_functions( $occurances ){
		$atts = $this->get_shortcode_atts($occurances);
		$replace_html = '';
		
		if($atts){
			$text = isset($atts['styles']) && !empty($atts['styles']) ? $atts['styles'] : false;
			$font_css = isset($atts['font_styles']) && !empty($atts['font_styles']) ? $atts['font_styles'] : false;
			$replace_html = $text ? $this->order_table_additional_td_css( $text, $font_css ) : "";
		}
		return $replace_html;
	}

	/**
	 * Get order table item attributes and contents
	 *
	 * @param  array $occurances occurances
	 * @return string $replace_html content
	 */
	public function wecm_order_item_functions( $occurances ){
		$atts = $this->get_shortcode_atts($occurances);
		$replace_html = '';
		if($atts){
			$image = isset($atts['image']) ? $atts['image'] === "on" : false;
			$sku = isset($atts['sku']) ? $atts['sku'] === "on" : false;
			$short_description = isset($atts['short_description']) ? $atts['short_description'] === "on" : false;
			$description_size = isset($atts['description_size']) ? $atts['description_size'] : '13px';
			$image_size = isset($atts['image_size']) ? explode('|', $atts['image_size']) : '';
			$image_width = isset( $image_size[0] ) ? $image_size[0] : '32';
			$image_height = isset( $image_size[1] ) ? $image_size[1] : '32';
			if($image){
				$replace_html .= '<?php $show_image = true;';
				$replace_html .= '$image_size = apply_filters("thwec_product_image_size", array('.$image_width.','. $image_height.')); ?>';
			}else{
				$replace_html .= '<?php $show_image = false; ?>';
			}
			if( $sku ){
				$replace_html .= '<?php $show_sku = apply_filters( "thwec_show_order_table_sku", '.$sku.', $item_id, $item, $order, $plain_text ); ?>';
			}else{
				$replace_html .= '<?php $show_sku = false; ?>';
			}
				$replace_html .= '
				<?php
				// Show title/image etc
				if ( $show_image ) {
					echo apply_filters( \'woocommerce_order_item_thumbnail\', \'<div style="margin-bottom: 5px"><img src="\' . ( $product->get_image_id() ? current( wp_get_attachment_image_src( $product->get_image_id(), \'thumbnail\' ) ) : wc_placeholder_img_src() ) . \'" alt="\' . esc_attr__( \'Product image\', \'woocommerce\' ) . \'" height="\' . esc_attr( $image_size[1] ) .\'" width="\' . esc_attr( $image_size[0] ) . \'" style="vertical-align:middle; margin-\' . ( is_rtl() ? \'left\' : \'right\' ) . \': 10px;" /></div>\', $item );
				}

				// Product name
				echo apply_filters( \'woocommerce_order_item_name\', $item->get_name(), $item, false );

				// SKU
				if ( $show_sku && is_object( $product ) && $product->get_sku() ) {
					echo \' (#\' . $product->get_sku() . \')\';
				} ?>';

				if( $short_description ){
 					$replace_html .= ' <?php if ( is_object( $product ) && $product->get_short_description() ) {
					echo \'<div class="thwec-short-description" style="font-size:'.$description_size.';">\'.$product->get_short_description().\'</div>\'; } ?>';
				}

				// allow other plugins to add additional product information here
				$replace_html .= '<?php do_action( \'woocommerce_order_item_meta_start\', $item_id, $item, $order, $plain_text );

				wc_display_item_meta( $item );

				// allow other plugins to add additional product information here
				do_action( \'woocommerce_order_item_meta_end\', $item_id, $item, $order, $plain_text );

			?>';
		}
		return $replace_html;
	}

	/**
	 * Get Order table head shortcode attributes and content
	 *
	 * @param  array $occurances occurances
	 * @return string $replace_html content
	 */
	public function special_wecm_order_table_head_functions($occurances){
		$atts = $this->get_shortcode_atts($occurances);
		$version = THWEC_Admin_Utils::woo_version_check() ? true : false;
		$replace_html = '';
		if($atts){
			$text = isset($atts['text']) && !empty($atts['text']) ? $atts['text'] : false;
			$replace_html = $text ? $this->order_table_head($text) : "";
		}
		return $replace_html;
	}

	public function special_wecm_order_table_helper_functions($occurances){
		$atts = $this->get_shortcode_atts($occurances);
		if($atts){
			$id = isset($atts['id']) && !empty($atts['id']) ? $atts['id'] : false;
			$labels = isset($atts['labels']) && !empty($atts['labels']) ? $atts['labels'] : false;
			$labels = json_decode($labels, true );
			$product_column_label = isset( $labels['product_column_label'] ) ? $labels['product_column_label'] : '';
			$quantity_column_label = isset( $labels['quantity_column_label'] ) ? $labels['quantity_column_label'] : '';
			$price_column_label = isset( $labels['price_column_label'] ) ? $labels['price_column_label'] : '';
			$cart_subtotal = isset( $labels['subtotal_row_label'] ) ? $labels['subtotal_row_label'] : '';
			$shipping = isset( $labels['shipping_row_label'] ) ? $labels['shipping_row_label'] : '';
			$payment_method = isset( $labels['payment_row_label'] ) ? $labels['payment_row_label'] : '';
			$order_total = isset( $labels['total_row_label'] ) ? $labels['total_row_label'] : '';
			
			$replace_html = '<?php $wecm_order_table_labels = array(
				"product_column_label" => "'.$product_column_label.'",
				"quantity_column_label" => "'.$quantity_column_label.'",
				"price_column_label" => "'.$price_column_label.'",
				"cart_subtotal" => "'.$cart_subtotal.'",
				"shipping" => "'.$shipping.'",
				"payment_method" => "'.$payment_method.'",
				"order_total" => "'.$order_total.'",
			); ?>';
		}
		return $replace_html;
	}

	/**
	 * Get checkout field editor themehigh shortcode attributes and content
	 *
	 * @param  array $occurances occurances
	 * @return string $replace_html content
	 */
	public function special_wcfe_meta_functions($occurances){
		$atts = $this->get_shortcode_atts($occurances);
		$version = THWEC_Admin_Utils::woo_version_check() ? true : false;
		$replace_html = '';
		if($atts){
			$replace_html .= $this->set_order_checkout_fields($atts,$version);
			if($replace_html !==''){
				$content_bf = '<?php if(isset($order) && !empty($order)){';
				$content_bf.= '$order_id = $order->get_id();'; 
				$content_bf.= 'if(!empty($order_id)){'; 
				$content_af = '} } ?>';
				$replace_html = $content_bf.$replace_html.$content_af;
			}
		}
		return $replace_html;
	}

	/**
	 * Get Custom hook shortcode attributes and content
	 *
	 * @param  array $occurances occurances
	 * @return string $replace_html content
	 */
	public function special_wecm_custom_hook_functions($occurances){
		$atts = $this->get_shortcode_atts($occurances);
		$version = THWEC_Admin_Utils::woo_version_check() ? true : false;
		$replace_html = '';
		if($atts){
			$replace_html .= $this->set_wecm_custom_hooks($atts,$version);

		}
		return $replace_html;
	}

	/**
	 * Prepare custom hook
	 *
	 * @param  array $hook_data hook details
	 * @param  string $version WooCommerce version
	 * @return string $html content
	 */
	public function set_wecm_custom_hooks($hook_data,$version){
		$html = '';
		$fname = isset($hook_data['name']) && !empty($hook_data['name']) ? $hook_data['name'] : false;
		if($fname){
			$html = '<?php $obj = isset( $order ) && is_a( $order, "WC_Order" ) ? $order : null;';
			$html .= 'do_action( \''.$fname.'\', $obj, $email ); ?>'; 
		}
		return $html;
	}

	/**
	 * Checkout field editor themehigh replace placeholder
	 *
	 * @param  array $wcfe_data checkout field name and label
	 * @param  string $version WooCommerce version
	 * @return string $html content
	 */
	public function set_order_checkout_fields($wcfe_data,$version){
		$html='';
		$flabel = '';
		$email_visible = '';
		$fvisibility = '';
		$fname = isset($wcfe_data['name']) && !empty($wcfe_data['name']) ? $wcfe_data['name'] : false;
		$flabel = isset($wcfe_data['label']) && !empty($wcfe_data['label']) ? '<b>'.trim($wcfe_data['label'],'"').'</b> : ' : '' ;
		$fvisibility = isset($wcfe_data['visibility']) && !empty($wcfe_data['visibility'])? trim($wcfe_data['visibility'],'"') : '' ;

		if($fname){
			if(in_array($fname, $this->woo_method_variables)){
				$html .= '$field_name = '.$this->get_default_woocommerce_method($fname);
			}else{
				if($version){
					$html .= '$field_name = get_post_meta($order->get_id(),\''.$fname.'\',true);';
				}else{
					$html .= '$field_name = get_post_meta($order->id,\''.$fname.'\',true);';
				}
				$html.= '$json_value = json_decode($field_name,true);';
				$html.= 'if(isset($json_value["name"]) && !empty($json_value["name"]) && isset($json_value["url"])){';
				$html.= '$field_name = "<a class=\"thwec-link\" href=\'".$json_value[\'url\']."\'>".$json_value[\'name\']."</a>";';
				$html.= '}else if( is_array( $json_value ) ){';
				$html.= '$files = [];';
				$html.= 'foreach ($json_value as $jkey => $jvalue) {';
				$html.= 'if(isset($jvalue["name"]) && !empty($jvalue["name"]) && isset($jvalue["url"])){';
				$html.= 'array_push( $files, "<a class=\"thwec-link\" href=\'".$jvalue[\'url\']."\'>".$jvalue[\'name\']."</a>" );';
				$html.= '} }';
				$html.= '$field_name = implode( ",", $files);';
				$html .= '}';
			}
			if($fvisibility == 'admin'){
				$email_visible = ' && $sent_to_admin';
			}else if($fvisibility == 'customer'){
				$email_visible = ' && !$sent_to_admin';
			}
			$html .= 'if(!empty($field_name)'.$email_visible.'){';
			$html .= '$field_html = "'.$flabel.'".$field_name;';
			$html .= 'echo $field_html;';
			$html .= '}';
		}
		return $html;
	}

	//DYNAMIC DATA CALLBACK FUNCTIONS
	/**
	 * WooCommerce billing shipping fields
	 *
	 * @param  string $f_name field name
	 * @param  boolean $wrap php tag necessary or not
	 * @return string $method content
	 */
	public function get_default_woocommerce_method($f_name, $wrap=false){
		$method = '';
		switch ($f_name) {
			case 'billing_first_name':
				$method = '$order->get_billing_first_name();';
				break;
			case 'billing_last_name':
				$method = '$order->get_billing_last_name();';
				break;
			case 'billing_company':
				$method = '$order->get_billing_company();';
				break;
			case 'billing_country':
				$method = '$order->get_billing_country();';
				break;
			case 'billing_address_1':
				$method = '$order->get_billing_address_1();';
				break;
			case 'billing_address_2':
				$method = '$order->get_billing_address_2();';
				break;
			case 'billing_city':
				$method = '$order->get_billing_city();';
				break;
			case 'billing_state':
				$method = '$order->get_billing_state();';
				break;
			case 'billing_postcode':
				$method = '$order->get_billing_postcode();';
				break;
			case 'billing_phone':
				$method = '$order->get_billing_phone();';
				break;
			case 'billing_email':
				$method = '$order->get_billing_email();';
				break;
			case 'shipping_first_name':
				$method = '$order->get_shipping_first_name();';
				break;
			case 'shipping_last_name':
				$method = '$order->get_shipping_last_name();';
				break;
			case 'shipping_company':
				$method = '$order->get_shipping_company();';
				break;
			case 'shipping_country':
				$method = '$order->get_shipping_country();';
				break;
			case 'shipping_address_1':
				$method = '$order->get_shipping_address_1();';
				break;
			case 'shipping_address_2':
				$method = '$order->get_shipping_address_2();';
				break;
			case 'shipping_city':
				$method = '$order->get_shipping_city();';
				break;
			case 'shipping_state':
				$method = '$order->get_shipping_state();';
				break;
			case 'shipping_postcode':
				$method = '$order->get_shipping_postcode();';
				break;
			default:
				$method='';
				break;
		}
		if( $wrap && !empty( $method ) ){
			$check = str_replace( ';', '', $method);
			$method = '<?php if( isset( $order ) && '.$check.' ){ echo '.$method.'} ?>';
		}
		return $method;
	}

	/**
	 * Order id
	 */
	public function get_order_id(){
		$order_id = '<?php if(isset($order)) : ?>';
		$order_id.= '<?php echo $order->get_id();?>';
		$order_id.= '<?php endif; ?>';
		return $order_id;
	}

	/**
	 * Order number
	 */
	public function get_order_number(){
		$order_id = '<?php if(isset($order)) : ?>';
		$order_id.= '<?php echo $order->get_order_number();?>';
		$order_id.= '<?php endif; ?>';
		return $order_id;
	}

	/**
	 * Order url
	 */
	public function get_order_url(){
		$order_url = '<?php if(isset($order) && $order->get_user()) : ?>';
		$order_url.= '<?php echo $order->get_view_order_url(); ?>';
		$order_url.= '<?php endif; ?>';
		return $order_url;
	}

	/**
	 * Customer name
	 */
	public function get_customer_name(){
		$customer_name = '<?php if(isset($order)) : ?>';
		$customer_name.= '<?php echo $order->get_billing_first_name(); ?>';
		$customer_name.= '<?php elseif ( isset( $user_login ) ) : ?>';
		$customer_name.= '<?php $user = get_user_by(\'login\', $user_login ); echo $user->first_name; ?>';
		$customer_name.= '<?php endif; ?>';
		return $customer_name;
	}

	/**
	 * Customer full name
	 */
	public function get_customer_full_name(){
		$customer_name = '<?php if(isset($order)) : ?>';
		$customer_name.= '<?php echo $order->get_billing_first_name().\' \'.$order->get_billing_last_name(); ?>';
		$customer_name.= '<?php elseif ( isset( $user_login ) ) : ?>';
		$customer_name.= '<?php $user = get_user_by(\'login\', $user_login ); echo $user->first_name.\' \'.$user->last_name; ?>';
		$customer_name.= '<?php endif; ?>';
		return $customer_name;
	}

	/**
	 * User email
	 */
	public function get_user_email(){
		$user_email = '<?php $user = isset( $user_login ) ? get_user_by(\'login\', $user_login ) : false; ?>';
		$user_email.= '<?php echo $user && isset($user->user_email) ? $user->user_email : "";  ?>';
		return $user_email;
	}

	/**
	 * Billing email
	 */
	public function get_billing_email(){
		$billing_email = '<?php if ( isset($order) && $order->get_billing_email() ) : ?>';
		$billing_email.= '<?php echo esc_html( $order->get_billing_email() ); ?>';
		$billing_email.= '<?php endif; ?>';
		return $billing_email;
	}

	/**
	 * Site url
	 */
	public function get_site_url(){
		$site_url = '<?php echo "<a class=\"thwec-link\" href=\"'.get_site_url().'\">'.get_bloginfo().'</a>"; ?>';
		return $site_url;
	}

	/**
	 * Site name
	 */
	public function get_site_name(){
		$site_name = '<?php echo get_bloginfo();?>';
		return $site_name;
	}

	/**
	 * Order completed date
	 */
	public function get_order_completed_date(){
		$order_date = '<?php if(isset($order) && $order->has_status( \'completed\' )):?>';
		$order_date.= '<?php echo wc_format_datetime($order->get_date_completed()); ?>';
		$order_date.= '<?php endif; ?>';
		return $order_date;
	}

	/**
	 * Order created date
	 */
	public function get_order_created_date(){
		$order_date = '<?php if(isset($order)) : ?>';
		$order_date.= '<?php echo wc_format_datetime($order->get_date_created()); ?>';
		$order_date.= '<?php endif; ?>';
		return $order_date;
	}

	/**
	 * Order total
	 */
	public function get_order_total(){
		$order_total = '<?php if(isset($order)) : ?>';
		$order_total.= '<?php echo $order->get_total(); ?>';
		$order_total.= '<?php endif; ?>';
		return $order_total;
	}

	/**
	 * Order formatted total
	 */
	public function get_order_formatted_total(){
		$order_ftotal = '<?php if(isset($order)) : ?>';
		$order_ftotal.= '<?php echo $order->get_formatted_order_total(); ?>';
		$order_ftotal.= '<?php endif; ?>';
		return $order_ftotal;
	}

	/**
	 * Order subtotal
	 */
	public function get_order_subtotal(){
		$order_stotal = '<?php if(isset($order)) : ?>';
		$order_stotal.= '<?php echo $order->get_subtotal(); ?>';
		$order_stotal.= '<?php endif; ?>';
		return $order_stotal;
	}

	/**
	 * Order subtotal with currency
	 */
	public function get_order_subtotal_with_currency(){
		$order_stotal = '<?php if(isset($order)) : ?>';
		$order_stotal.= '<?php echo $order->get_subtotal_to_display(); ?>';
		$order_stotal.= '<?php endif; ?>';
		return $order_stotal;
	}

	/**
	 * Shipping method
	 */
	public function get_shipping_method(){
		$shipping = '<?php if(isset($order)) : ?>';
		$shipping.= '<?php echo $order->get_shipping_method(); ?>';
		$shipping.= '<?php endif; ?>';
		return $shipping;
	}

	/**
	 * Checkout payment url
	 */
	public function get_order_checkout_payment_url(){
		$checkout_payment_url = '<?php if ( isset($order) && $order->has_status( \'pending\' ) ) : ?>
		<?php
		printf(
			wp_kses(
				
				__( \'%s\', \'woocommerce\' ),
				array(
					\'a\' => array(
						\'href\' => array(),
					),
				)
			),
			\'<a class="thwec-link" href="\' . esc_url( $order->get_checkout_payment_url() ) . \'">\' . esc_html__( \'Pay for this order\', \'woocommerce\' ) . \'</a>\'); ?>	
		<?php endif; ?>';
		return $checkout_payment_url;
	}

	/**
	 * Payment method
	 */
	public function get_order_payment_method(){
		$payment_method = '<?php if(isset($order)) : ?>';
		$payment_method.= '<?php echo $order->get_payment_method_title(); ?>';
		$payment_method.= '<?php endif; ?>';
		return $payment_method;
	}

	/**
	 * Customer note
	 */
	public function get_customer_note( $preview ){
		$customer_note = '';
		if( $preview ){
			$customer_note.= '<?php if( isset( $order ) ) : ';
			$customer_note.= '$notes = $order->get_customer_order_notes();';
			$customer_note.= 'reset($notes);';
			$customer_note.= '$note_key = key($notes);';
			$customer_note.= '$customer_note = isset( $notes[$note_key]->comment_content ) ? $notes[$note_key]->comment_content : \'\';';
			$customer_note.= 'if( !empty( $customer_note ) ): ?>';
			$customer_note.= '<blockquote><?php echo wptexturize( $customer_note ); ?></blockquote>';
			$customer_note.= '<?php endif; endif; ?>';

		}else{
			$customer_note = '<?php if(isset($customer_note)) : ?>';
			$customer_note.= '<blockquote><?php echo wptexturize( $customer_note ); ?></blockquote>';
			$customer_note.= '<?php endif; ?>';
		}
		return $customer_note;
	}

	public function get_customer_note_plain_text( $preview ){
		$customer_note = '';
		if( $preview ){
			$customer_note.= '<?php if( isset( $order ) ) : ';
			$customer_note.= '$notes = $order->get_customer_order_notes();';
			$customer_note.= 'reset($notes);';
			$customer_note.= '$note_key = key($notes);';
			$customer_note.= '$customer_note = isset( $notes[$note_key]->comment_content ) ? $notes[$note_key]->comment_content : \'\';';
			$customer_note.= 'if( !empty( $customer_note ) ): ';
			$customer_note.= 'echo wptexturize( $customer_note );';
			$customer_note.= 'endif; endif; ?>';
		}else{
			$customer_note = '<?php if(isset($customer_note)) : ';
			$customer_note.= 'echo wptexturize( $customer_note );';
			$customer_note.= 'endif; ?>';
		}
		return $customer_note;
	}

	/**
	 * User login
	 */
	public function get_user_login(){
		$user_login = '<?php if(isset($user_login)) : ?>';
		$user_login .= '<?php echo \'<strong>\' . esc_html( $user_login ) . \'</strong>\' ?>';
		$user_login .= '<?php elseif( isset($order) ): ?>';
		$user_login .= '<?php $user = $order->get_user(); ?>';
		$user_login .= '<?php echo $user->user_login; ?>';
		$user_login .= '<?php endif; ?>';
		return $user_login;
	}

	/**
	 * User password
	 */
	public function get_user_pass(){
		$user_pass = '<?php if ( \'yes\' === get_option( \'woocommerce_registration_generate_password\' ) && isset($password_generated) ) : ?>';
		$user_pass.= '<?php echo \'<strong>\' . esc_html( $user_pass ) . \'</strong>\' ?>';
		$user_pass.= '<?php endif; ?>';
		return $user_pass;
	}

	/**
	 * Account area url
	 *
	 * @param  boolean $echo render or return
	 * @return string $content button content
	 */
	public function get_account_area_url( $echo=false ){
		if( $echo ){
			return esc_url( wc_get_page_permalink( 'myaccount' ) );
		}else{
			return '<?php echo make_clickable( esc_url( wc_get_page_permalink( \'myaccount\' ) ) ); ?>';
		}
	}

	/**
	 * Account area order section url
	 *
	 * @param  boolean $echo render or return
	 * @return string $content button content
	 */
	public function get_account_order_url( $echo=false ){
		if( $echo ){
			return esc_url( wc_get_account_endpoint_url( 'orders' ) );
		}else{
			return '<?php echo make_clickable( esc_url( wc_get_account_endpoint_url( \'orders\' ) ) ); ?>';
		}
	}

	/**
	 * Password reset url
	 */
	public function get_reset_password_url($preview){
		if( $preview ){
			return '<a class="link thwec-link" href="#"><?php _e( \'Click here to reset your password\', \'woocommerce\' ); ?></a>';
		}
		$reset_pass = '<?php if( isset($order) ) : ?>';
		$reset_pass .= '<?php $user = $order->get_user(); ?>';
		$user_id = THWEC_Admin_Utils::woo_version_check('3.4.0') ? '$user->ID;' : '$user->user_login;';
		$reset_pass .= '<?php $user_id = '.$user_id.'?>';
		$reset_pass .= '<?php $reset_key = get_password_reset_key( $user ); ?>';
		$reset_pass .= '<?php endif; ?>';
		$reset_pass .= '<?php if(isset($reset_key) && isset($user_id)): ?>';
		if( THWEC_Admin_Utils::woo_version_check('3.4.0') ){
			$reset_pass .= '<a class="link thwec-link" href="<?php echo esc_url( add_query_arg( array( \'key\' => $reset_key, \'id\' => $user_id ), wc_get_endpoint_url( \'lost-password\', \'\', wc_get_page_permalink( \'myaccount\' ) ) ) ); ?>">
				<?php _e( \'Click here to reset your password\', \'woocommerce\' ); ?></a>';
		}else{
			$reset_pass .= '<a class="link thwec-link" href="<?php echo esc_url( add_query_arg( array( \'key\' => $reset_key, \'login\' => $user_id ), wc_get_endpoint_url( \'lost-password\', \'\', wc_get_page_permalink( \'myaccount\' ) ) ) ); ?>">
				<?php _e( \'Click here to reset your password\', \'woocommerce\' ); ?></a>';
		}
		$reset_pass.= '<?php endif; ?>';
		return $reset_pass;
	}

	/**
	 * Set new password url
	 */
	public function get_new_password_url($preview){
		if( $preview ){
			return '<p><a href="#"><?php printf( esc_html__( \'Click here to set your new password.\', \'woocommerce\' ) ); ?></a></p>';
		}
		$set_pass = '';
		if( THWEC_Admin_Utils::woo_version_check('6.0.0') ){
			$set_pass .= '<?php if ( \'yes\' === get_option( \'woocommerce_registration_generate_password\' ) && isset( $password_generated ) && isset( $set_password_url ) ) : ?>';
			$set_pass .= '<p><a href="<?php echo esc_attr( $set_password_url ); ?>"><?php printf( esc_html__( \'Click here to set your new password.\', \'woocommerce\' ) ); ?></a></p>';
			$set_pass .= '<?php endif; ?>';
		}
		return $set_pass;
	}

	/**
	 * Order table total loop start
	 */
	public function order_table_total_loop_start(){
		$order_data = '<?php
		if(isset($order)){
			$totals = $order->get_order_item_totals();
			if ( $totals ) {
				$i = 0;
				foreach ( $totals as $total_key => $total ) {
					$total[\'label\'] = isset( $wecm_order_table_labels ) && isset( $wecm_order_table_labels[$total_key] ) ? $wecm_order_table_labels[$total_key] : $total[\'label\']; 
					$i++;
					?>';
		return $order_data;
	}

	/**
	 * Order total labels
	 */
	public function order_table_total_labels(){
		$order_data = '<?php echo wp_kses_post( apply_filters("thwec_rename_order_total_labels", $total[\'label\']) ); ?>';
		return $order_data;
	}

	/**
	 * Order table values
	 */
	public function order_table_total_values(){
		$order_data = '<?php echo wp_kses_post( $total[\'value\'] ); ?>';
		return $order_data;
	}

	/**
	 * Order table order note
	 */
	public function order_table_total_loop_end(){
		$order_data = '<?php
				}
			}
			if ( isset($order) && $order->get_customer_note() ) {
				?>
				<tr>
					<th class="td" scope="row" colspan="2" style="text-align:inherit;padding-top:inherit;padding-right:inherit;padding-bottom:inherit;padding-left:inherit;font-size:inherit;color:inherit;font-family:inherit;line-height:inherit;border-color:inherit;"><?php esc_html_e( \'Note:\', \'woocommerce\' ); ?></th>
					<td class="td" style="text-align:inherit;padding-top:inherit;padding-right:inherit;padding-bottom:inherit;padding-left:inherit;font-size:inherit;color:inherit;font-family:inherit;line-height:inherit;border-color:inherit;"><?php echo wp_kses_post( wptexturize( $order->get_customer_note() ) ); ?></td>
				</tr>
				<?php
			}
		}
			?>';
		return $order_data;
	}

	/**
	 * Order note in order table
	 *
	 * @param  string $styles css inline style
	 * @param  string $font_css coma seperated font string
	 */
	public function order_table_additional_td_css( $styles, $font_css ){
		$font_css = explode(',', $font_css);
		$fonts = THWEC_Utils::font_family_list();
		if( $font_css && is_array( $font_css ) ){
			foreach ($font_css as $index => $key) {
				if( isset( $fonts[$key] ) ){
					$styles .= "font-family:".$fonts[$key].";";
				}	
			}
		}
		$order_data = '<?php
				}
			}
			if ( isset($order) && $order->get_customer_note() ) {
				?>
				<tr>
					<th class="td" scope="row" colspan="2" style="'.$styles.'"><?php esc_html_e( \'Note:\', \'woocommerce\' ); ?></th>
					<td class="td" style="'.$styles.'"><?php echo wp_kses_post( wptexturize( $order->get_customer_note() ) ); ?></td>
				</tr>
				<?php
			}
		}
			?>';
		return $order_data;
	}

	/**
	 * Order table header item
	 */
	public function order_table_header_product(){
		$order_data = '<?php echo __( apply_filters("thwec_rename_order_total_labels", "Product"), \'woocommerce\' ); ?>';
		return $order_data;
	}

	/**
	 * Order table header quantity
	 */
	public function order_table_header_qty(){
		$order_data = '<?php echo __( apply_filters("thwec_rename_order_total_labels", "Quantity"), \'woocommerce\' ); ?>';
		return $order_data;
	}

	/**
	 * Order table header price
	 */
	public function order_table_header_price(){
		$order_data = '<?php echo __( apply_filters("thwec_rename_order_total_labels", "Price"), \'woocommerce\' ); ?>';
		return $order_data;
	}

	/**
	 * Order item loop start
	 */
	public function order_table_item_loop_start(){
		$order_data = '<?php 
		$items = $order->get_items();
		foreach ( $items as $item_id => $item ) :
	$product = $item->get_product();
	if ( apply_filters( "woocommerce_order_item_visible", true, $item ) ) {
		?>';
		return $order_data;
	}

	/**
	 * Order item section loop end
	 */
	public function order_table_item_loop_end(){
		$order_data = '<?php
		}
		$show_purchase_note=true;
		if ( $show_purchase_note && is_object( $product ) && ( $purchase_note = $product->get_purchase_note() ) ) : ?>
			<tr>
				<td colspan="3" style="text-align:<?php echo $text_align; ?>;vertical-align:middle; border: 1px solid #eee; font-family: \'Helvetica Neue\', Helvetica, Roboto, Arial, sans-serif;"><?php echo wpautop( do_shortcode( wp_kses_post( $purchase_note ) ) );?></td>
			</tr>
		<?php endif; ?>
		<?php endforeach; ?>';
		return $order_data;
	}

	/**
	 * Order item class filter
	 */
	public function order_table_class_filter(){
		$order_data = '<?php echo esc_attr( apply_filters( \'woocommerce_order_item_class\', \'order_item\', $item, $order ) ); ?>';
		return $order_data;
	}

	/**
	 * Order item qunatity
	 */
	public function order_table_items_qty(){
		$order_data = '<?php echo apply_filters( \'woocommerce_email_order_item_quantity\', $item->get_quantity(), $item ); ?>';
		return $order_data;
	}

	/**
	 * Order item price
	 */
	public function order_table_items_price(){
		$order_data = '<?php echo $order->get_formatted_line_subtotal( $item ); ?>';
		return $order_data;
	}	

	/**
	 * Order item contents
	 *
	 * @param  boolean $img show image or not
	 */
	public function order_table_items($img=false){
		$order_data = '<?php '; 
		if($img){
			$order_data .= '$show_image = true;';
			$order_data .= '$image_size = apply_filters("thwec_product_image_size", array( 32, 32));';
		}else{
			$order_data .= '$show_image = false;';
		}
		$order_data .= '$show_sku = apply_filters( "thwec_show_order_table_sku", $sent_to_admin, $item_id, $item, $order, $plain_text );';
		$order_data .= '

				// Show title/image etc
				if ( $show_image ) {
					echo apply_filters( \'woocommerce_order_item_thumbnail\', \'<div style="margin-bottom: 5px"><img src="\' . ( $product->get_image_id() ? current( wp_get_attachment_image_src( $product->get_image_id(), \'thumbnail\' ) ) : wc_placeholder_img_src() ) . \'" alt="\' . esc_attr__( \'Product image\', \'woocommerce\' ) . \'" height="\' . esc_attr( $image_size[1] ) .\'" width="\' . esc_attr( $image_size[0] ) . \'" style="vertical-align:middle; margin-\' . ( is_rtl() ? \'left\' : \'right\' ) . \': 10px;" /></div>\', $item );
				}

				// Product name
				echo apply_filters( \'woocommerce_order_item_name\', $item->get_name(), $item, false );

				// SKU
				if ( $show_sku && is_object( $product ) && $product->get_sku() ) {
					echo \' (#\' . $product->get_sku() . \')\';
				}

				// allow other plugins to add additional product information here
				do_action( \'woocommerce_order_item_meta_start\', $item_id, $item, $order, $plain_text );

				wc_display_item_meta( $item );

				// allow other plugins to add additional product information here
				do_action( \'woocommerce_order_item_meta_end\', $item_id, $item, $order, $plain_text );

			?>';
		return $order_data;
	}


	public function ywgc_before(){
		return '<?php if( isset( $gift_card ) && $gift_card instanceof YWGC_Gift_Card_Premium ){ ?>';
	}

	public function ywgc_after(){
		return '<?php } ?>';
	}

	/**
	 * Order condition opening
	 */
	public function order_table_before_loop(){
		$loop = '<?php if(isset($order)){ ?>';
		return $loop;
	}

	/**
	 * Order condition closing
	 */
	public function order_table_after_loop(){
		$loop = '<?php } ?>';
		return $loop;
	}

	/**
	 * Billing address content
	 *
	 * @param  boolean $preview if previewing or not
	 */
	public function billing_data( $preview ){
		$address = '<?php echo wp_kses_post( $order->get_formatted_billing_address( esc_html__( "N/A", "woocommerce" ) ) ); ?>
				<?php if ( $order->get_billing_phone() ) : ?>
					<br><?php echo wc_make_phone_clickable( $order->get_billing_phone() ); ?>
				<?php endif; ?>';
		$address .= '<?php if ( $order->get_billing_email() ) : ?>
					<br><a class="thwec-link" href="mailto:<?php echo esc_html( $order->get_billing_email() ); ?>"><?php echo esc_html( $order->get_billing_email() ); ?></a>
				<?php endif; ?>';
		return $address;
	}

	/**
	 * Shipping address content
	 *
	 * @param  boolean $position if condition open or close
	 */
	public function shipping_data_additional($position){
		$additional = '';
		if($position){
			$additional .= '<?php if ( ! wc_ship_to_billing_address_only() && $order->needs_shipping_address() && ( $shipping = $order->get_formatted_shipping_address() ) ) : ?>';
		}else{
			$additional .= '<?php endif; ?>';
		}
		return $additional;
	}

	/**
	 * Shipping address content
	 */
	public function shipping_data(){
		$address = '<?php echo $order->get_formatted_shipping_address(); ?>';
		return $address;
	}

	/**
	 * Customer address content
	 */
	public function customer_data($preview){
		$address = '<?php echo $order->get_formatted_billing_full_name(); ?><br><?php echo wc_make_phone_clickable( $order->get_billing_phone() ); ?>';
		$address .= '<?php if ( $order->get_billing_email() ) : ?>
					<br><a class="thwec-link" href="mailto:<?php echo esc_html( $order->get_billing_email() ); ?>"><?php echo esc_html( $order->get_billing_email() ); ?></a>
				<?php endif; ?>';
		return $address;
	}	
	
	/**
	 * Helper contents for order table
	 *
	 * @param  boolean $tag php tag necessary or not
	 * @return string $content button content
	 */
	public function order_table_before_hook(){
		$order_data = '<?php $text_align = is_rtl() ? "right" : "left"; ?>';
		return $order_data;
	}

	/**
	 * Order table header link
	 *
	 * @param  string $text Order table header text
	 */
	public function order_table_head($text){
		$ot_link = '';
		$ot_title_link = apply_filters('thwec_order_table_title_link', '');
		if( !empty( $ot_title_link ) && in_array( $ot_title_link, $this->link_pkaceholders ) ){
			$ot_link = $this->replace_placeholder_links( $ot_title_link );
		}
		$order_table = '<?php
		if ( $sent_to_admin ) {
			$before = \'<a class="link" style="color:inherit;font-weight:inherit;font-size:inherit;font-family:inherit;line-height:inherit;" href="\' . esc_url( $order->get_edit_order_url() ) . \'">\';
			$after  = \'</a>\';
		} else {
			$before = "";
			$after  = "";';
		if( !empty( $ot_link ) ){
			$order_table .= '$before = \'<a class="link" style="color:inherit;font-weight:inherit;font-size:inherit;font-family:inherit;line-height:inherit;" href="'.$ot_link.'">\';';
		}
		$order_table .= '}
		echo wp_kses_post( $before . sprintf( __( \''.$text.'#%s\', \'woocommerce-email-customizer-pro\' ) . $after . \' (<time datetime="%s">%s</time>)\', $order->get_order_number(), $order->get_date_created()->format( \'c\' ), wc_format_datetime( $order->get_date_created() ) ) );
		?>';
		return $order_table;
	}

	/**
	 * Downloadable product content
	 *
	 * @param  string $mail_type test-mail or not
	 */
	public function downloadable_product_table($mail_type){
		$downloadable_product = '';
		if(!$mail_type){
			$downloadable_product .= '<?php $show_downloads = isset( $order ) && $order->has_downloadable_item() && $order->is_download_permitted() && ! $sent_to_admin && ! is_a( $email, \'WC_Email_Customer_Refunded_Order\' ); ?>';
			$downloadable_product .= '<?php $text_align = is_rtl() ? \'right\' : \'left\'; 
			if( isset($show_downloads) && $show_downloads ){
			$downloads = $order->get_downloadable_items();
			$columns   = apply_filters(
						\'woocommerce_email_downloads_columns\', array(
						\'download-product\' => __( \'Product\', \'woocommerce\' ),
						\'download-expires\' => __( \'Expires\', \'woocommerce\' ),
						\'download-file\'    => __( \'Download\', \'woocommerce\' ),
						)
					); ?>';
			$downloadable_product .= '<?php if($downloads) {?>';
			$downloadable_product .=  '<h2 class="woocommerce-order-downloads__title"><?php esc_html_e( \'Downloads\', \'woocommerce\' ); ?></h2>';
			$downloadable_product .= '<table class="td" cellspacing="0" cellpadding="6" style="width: 100%; font-family: \'Helvetica Neue\', Helvetica, Roboto, Arial, sans-serif; margin-bottom: 40px;" border="1">
			<thead>
				<tr>
					<?php foreach ( $columns as $column_id => $column_name ) : ?>
						<th class="td" scope="col" style="text-align:<?php echo esc_attr( $text_align ); ?>;"><?php echo esc_html( $column_name ); ?></th>
					<?php endforeach; ?>
				</tr>
			</thead>
			<?php foreach ( $downloads as $download ) : ?>
				<tr>
					<?php foreach ( $columns as $column_id => $column_name ) : ?>
						<td class="td" style="text-align:<?php echo esc_attr( $text_align ); ?>;">
							<?php
							if ( has_action( \'woocommerce_email_downloads_column_\' . $column_id ) ) {
								do_action( \'woocommerce_email_downloads_column_\' . $column_id, $download, $plain_text );
							} else {
								switch ( $column_id ) {
									case \'download-product\':
										?>
										<a href="<?php echo esc_url( get_permalink( $download[\'product_id\'] ) ); ?>"><?php echo wp_kses_post( $download[\'product_name\'] ); ?></a>
										<?php
										break;
									case \'download-file\':
										?>
										<a href="<?php echo esc_url( $download[\'download_url\'] ); ?>" class="woocommerce-MyAccount-downloads-file button alt"><?php echo esc_html( $download[\'download_name\'] ); ?></a>
										<?php
										break;
									case \'download-expires\':
										if ( ! empty( $download[\'access_expires\'] ) ) {
											?>
											<time datetime="<?php echo esc_attr( date( \'Y-m-d\', strtotime( $download[\'access_expires\'] ) ) ); ?>" title="<?php echo esc_attr( strtotime( $download[\'access_expires\'] ) ); ?>"><?php echo esc_html( date_i18n( get_option( \'date_format\' ), strtotime( $download[\'access_expires\'] ) ) ); ?></time>
											<?php
										} else {
											esc_html_e( \'Never\', \'woocommerce\' );
										}
										break;
								}
							}
							?>
						</td>
					<?php endforeach; ?>
				</tr>
				<?php endforeach; ?></table>';
			$downloadable_product .= '<?php } } ?>';
		}
		return $downloadable_product;
	}

	/**
	 * Order opening tag
	 */
	public function add_order_head(){
		$order_head = '<?php if(isset($order)){?>';
		return $order_head;
	}
	
	/**
	 * Order closing tag
	 */
	public function add_order_foot(){
		$order_foot = '<?php } ?>';
		return $order_foot;
	}

	/**
	 * Replace hook contents
	 *
	 * @param  string $hook string to replace
	 * @return string $content button content
	 */
	public function thwec_email_hooks($hook){
		switch($hook){
			 case '{email_header_hook}':
                $hook ='<?php do_action( \'woocommerce_email_header\', $email_heading, $email ); ?>'; 
                break;
 			case '{email_order_details_hook}': 
 				$hook = '<?php if(isset($order)){ ?>'; 
 				$hook .= '<div class=\'thwec-order-table-ref\' style=\'border:none;padding:0;margin:0;\'>';
 				$hook .= '<?php do_action( \'woocommerce_email_order_details\', $order, $sent_to_admin, $plain_text, $email ); ?>';
 				$hook .= '</div>';
 				$hook .= '<?php } ?>';
 				break;
  			case '{before_order_table_hook}': 
  				$hook = '<?php if(isset($order)){ 
  					do_action(\'woocommerce_email_before_order_table\', $order, $sent_to_admin, $plain_text, $email); 
  				}?>';
 				break;
  			case '{after_order_table_hook}': 
  				$hook = '<?php if(isset($order)){ 
  					do_action(\'woocommerce_email_after_order_table\', $order, $sent_to_admin, $plain_text, $email); 
  				}?>';
 				break;
  			case '{order_meta_hook}': 
  				$hook = '<?php if(isset($order)){ 
  					do_action( \'woocommerce_email_order_meta\', $order, $sent_to_admin, $plain_text, $email ); 
  				}?>';
 				break;
  			case '{customer_details_hook}': 
  				$hook = '<?php if(isset($order)){ 
  					do_action( \'woocommerce_email_customer_details\', $order, $sent_to_admin, $plain_text, $email ); 
  				}?>';
 				break;
 			case '{email_footer_hook}':
                $hook = '<?php do_action( \'woocommerce_email_footer\', $email ); ?>';
                break;
            case '{email_footer_blogname}':
            $hook = '<?php echo wpautop( wp_kses_post( wptexturize( apply_filters( \'woocommerce_email_footer_text\', \'\' ) ) ) ); ?>';
            default:
                $hook = '';
		}
		return $hook;
	}

	/**
	 * Replace content with account urls
	 *
	 * @param  string $placeholder account area url placeholder
	 * @return string $link replaced content
	 */
	public function replace_placeholder_links( $placeholder ){
		$link = '';
		switch ( $placeholder ) {
			case '{account_area_url}':
				$link = $this->get_account_area_url( true );
				break;
			case '{account_order_url}':
				$link = $this->get_account_order_url( true );
				break;
			default:
				$link = '';
				break;
		}
		return $link;
	}
}

endif;