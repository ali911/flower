<?php
/**
 * The admin email mapping page functionality of the plugin.
 *
 * @link       https://themehigh.com
 * @since      1.0.0
 *
 * @package    woocommerce-email-customizer-pro
 * @subpackage woocommerce-email-customizer-pro/admin
 */
if(!defined('WPINC')){	die; }

if(!class_exists('THWEC_Admin_Template_Mapping')):

class THWEC_Admin_Template_Mapping{
	/**
	 * Main instance of the class
	 *
	 * @access   protected
	 * @var      $_instance    
	 */
	protected static $_instance = null;

	/**
	 * Manages the email status and corresponding assigned templates
	 *
	 * @access   private
	 * @var      $page_id    email - template mapping array
	 */
	private $template_map = array();
	
	/**
	 * Stores the plugin template settings
	 *
	 * @access   private
	 * @var      $db_settings    template settings
	 */
	private $db_settings = array();

	/**
	 * Manages the email statuses used in this plugin
	 *
	 * @access   private
	 * @var      $email_list    list of email statuses
	 */
	private $email_list = array();

	/**
	 * Stores value for YITH Gift card plugin activation status
	 *
	 * @access   private
	 * @var      $ywgc_active    YITH Gift card plugin activation status
	 */
	private $ywgc_active = false;

	/**
	 * Manages the mapping form fields
	 *
	 * @access   private
	 * @var      $template_map    form fields
	 */
	private $map_fields = array();

	/**
	 * Manages the list of placeholders used in the email subject
	 *
	 * @access   private
	 * @var      $subject_placeholders    placeholders used in the email subject
	 */
	private $subject_placeholders = array();

	/**
	 * Manages the admin email statuses in the list of email statuses
	 *
	 * @access   private
	 * @var      $admin_email_status    admin email statuses
	 */
	private $admin_email_status = array();

	/**
	 * Manages the user created email templates
	 *
	 * @access   private
	 * @var      $templates    list of email templates
	 */
	private $templates = array();

	/**
	 * Manages the relation between template and its wpml versions
	 *
	 * @access   private
	 * @var      $wpml_map    array of template and its wpml versions
	 */
	private $wpml_map = array();

	/**
	 * Manages the email mapping form submission result
	 *
	 * @access   private
	 * @var      $result   boolean result of form submission
	 */
	private $result = 'initialized';

	/**
	 * Construct
	 */
	public function __construct() {
		$this->init_helpers();
		$this->manage_email_mapping();
		$this->init_constants();
	}
	
	/**
	 * Main THWEC_Admin_Template_Mapping Instance.
	 *
	 * Ensures only one instance of THWEC_Admin_Template_Mapping is loaded or can be loaded.
	 *
	 * @since 3.5.0
	 * @static
	 * @return THWEC_Admin_Template_Mapping Main instance
	 */
	public static function instance() {
		if(is_null(self::$_instance)){
			self::$_instance = new self();
		}
		return self::$_instance;
	}

	public function manage_email_mapping(){
		if( $this->is_form_action() ){
			$this->handle_email_map_form_actions();
		}
	}

	private function init_helpers(){
		$this->email_list = THWEC_Utils::email_statuses();
		// $this->ywgc_active = THWEC_Utils::is_ywgc_active();
		// if( $this->ywgc_active ){
			// $this->email_list = array_merge( $this->email_list, THWEC_Utils::ywgc_emails() );
		// }
		// $this->email_list =  array_merge( $this->email_list, $this->get_email_instances() );
		$this->attachment_email_id = array(
			'admin-new-order'					=>	'new_order',
			'admin-cancelled-order'				=>	'cancelled_order',
			'admin-failed-order'				=>	'failed_order',
			'customer-on-hold-order'			=>	'customer_on_hold_order',
			'customer-processing-order'			=>	'customer_processing_order',
			'customer-completed-order'			=>	'customer_completed_order',
			'customer-refunded-order'			=>	'customer_refunded_order',
			'customer-partially-refunded-order' => 	'customer_partially_refunded_order',
			'customer-invoice'					=>	'customer_invoice',
			'customer-note'						=>	'customer_note',
			'customer-reset-password'			=>	'customer_reset_password',
			'customer-new-account'				=>	'customer_new_account'
		);
	}

	public function init_constants(){
		$settings = THWEC_Utils::get_template_settings();
		$this->template_map = THWEC_Utils::get_template_map();
		$this->db_settings = THWEC_Utils::get_template_settings();
		$this->wpml_map = THWEC_Utils::get_wpml_map( $this->db_settings );
		$this->templates = $this->get_templates();
		$this->email_subjects = isset( $this->db_settings['email_subject'] ) && !empty( $this->db_settings['email_subject'] ) ? $this->db_settings['email_subject'] : false;
		$this->email_attachments = isset( $this->db_settings['email_attachments'] ) && !empty( $this->db_settings['email_attachments'] ) ? $this->db_settings['email_attachments'] : false;

		$this->map_fields = array(
			'template-list'		=> array(
				'type'=>'select', 'name'=>'template-list[]', 'label'=>'', 'value'=>'','class'=>'thwec-paragraph','options'=>'',
			),
			'template-subject'	=> array(
				'type'=>'textarea', 'name'=>'template-subject[]', 'label'=>'', 'value'=>'','class'=>'thwec-paragraph'
			),
			'email-attachment' => array(
				'type'=>'file', 'name'=>'email-attachment[]', 'label'=>'', 'value'=>'','class'=>''
			)
		);

		$this->admin_email_status = array(
			'admin-new-order',
			'admin-cancelled-order',
			'admin-failed-order'
		);

		$this->subject_placeholders = array(
			'{customer_name}',
			'{customer_full_name}',
			'{site_name}', 
			'{order_id}'			,
			'{order_created_date}'	,
			'{order_completed_date}',
			'{order_total}',
			'{order_formatted_total}',
			'{billing_first_name}',
			'{billing_last_name}',
			'{billing_last_name}',
			'{billing_company}',
			'{billing_country}',
			'{billing_address_1}',
			'{billing_address_2}',
			'{billing_city}',
			'{billing_state}',
			'{billing_postcode}',
			'{billing_phone}',
			'{billing_email}',
			'{shipping_first_name}',
			'{shipping_last_name}',
			'{shipping_company}',
			'{shipping_country}',
			'{shipping_address_1}',
			'{shipping_address_2}',
			'{shipping_city}',
			'{shipping_state}',
			'{shipping_postcode}',
			'{payment_method}'
		);
	}

	public function get_template_icon_url($template){
		if( strpos( $template, "YITH Gift Cards ") !== false ){
			return THWEC_ASSETS_URL_ADMIN.'images/yith.svg';
		}
		return THWEC_ASSETS_URL_ADMIN.'images/woo.svg';
	}

	public function is_form_action(){
		if( isset($_POST['save_map']) || isset($_POST['reset_map']) ){
			return true;
		}
		return false;
	}

	private function handle_email_map_form_actions(){

		if( isset($_POST['save_map']) ){
			$this->result = $this->save_map();
		}else if( isset($_POST['reset_map']) ){
			$this->result = $this->reset_to_default();
		}
	}

	public function render_page(){
		?>
		<div id="thwec_template_mapping" class="thwec-plain-background">
			<?php
			$this->render_notifications();
			$this->render_heading();
			$this->render_body();
			?>
		</div>
		<?php
	}

	public function render_notifications(){
		if( $this->result === "initialized" ){
			return;
		}

		$action = isset($_POST['save_map']) ? "save" : (isset($_POST['reset_map']) ? "reset" : "");

		if( $this->result ){
			$result = "success";
			$icons = "dashicons-yes";
			$message = $action === "save" ? "Settings Saved." : "Template Settings Successfully Reset.";
		}else{
			$result = "error";
			$icons = "dashicons-no-alt";
			$message = $action === "save" ? "Your changes were not saved due to an error (or you made none!)." : "Templates not reset (or nothing to reset!).";
		}
		?>
		<div id="thwec_validations" class="thwec-template-validation">
			<div class="validation-wrapper thwec-<?php echo $result; ?>">
        		<span class="dashicons <?php echo $icons; ?>"></span>
		        <div class="validation-messages">
		            <p class="thwec-label"><?php echo $result; ?></p>
		            <p class="thwec-label-light"><?php echo $message; ?></p>
		        </div>
		    </div>
        </div>
        <script>
        	jQuery(function($) {
        		setTimeout(function() { $("#thwec_validations").remove(); }, 2000);
        	});
        </script>
        <?php
	}

	public function render_heading(){
		?>
		<div class="thwec-mapping-title">
			<h1 class="thwec-main-heading">Email Mapping</h1>
		</div>
		<?php
	}

	public function render_body(){
		?>
		<form name="template_map_form" action="" method="POST">
			<?php
			if ( function_exists('wp_nonce_field') ){
				wp_nonce_field( 'thwec_email_map', 'thwec_email_map_nonce' );
	    	}
	    	?>
			<table id="thwec_template_map">
				<tbody>
					<?php foreach ($this->email_list as $key => $email) {
						echo '<tr>';
						$this->mapping_row_template( $email, $key );
						$this->mapping_row_choose_template( $key );
						$this->mapping_row_subject( $key );
						echo '</tr>';
						echo '<tr class="thwec-map-divider">';
						$this->render_map_field_attachment($key);
						echo '</tr>';
					}
					?>
				</tbody>
				<tfoot>
					<tr>
						<td colspan="3">
							<button class="btn btn-primary thwec-mapping-button" name="save_map" type="submit">Save</button>
							<button class="btn thwec-mapping-button" id="thwec_reset_mapping" name="reset_map" type="submit">Reset</button>
						</td>
						<td></td>
					</tr>
				</tfoot>
			</table>
		</form>
		<?php
	}

	public function mapping_row_template( $template, $key ){
		$url = $this->get_template_icon_url( $template );
		?>
		<td class="thwec-mapping-column-template">
			<input type="hidden" name="i_email-id[]" value="<?php echo $key; ?>">
			<div class="thwec-template-information">
				<div class="thwec-template-icon thwec-inline thwec-template-info">
					<img src="<?php echo $url; ?>">
				</div>
				<div class="thwec-template-label thwec-inline thwec-template-info">
					<div class="thwec-label"><?php echo $template; ?></div>
				</div>
			</div>
		</td>
		<?php
	}

	public function mapping_row_choose_template( $email ){
		?>
		<td class="thwec-mapping-column-map">
			<label class="thwec-paragraph thwec-block thwec-label-light">Choose from saved templates</label>
			<?php $this->render_map_field_template($this->map_fields['template-list'], $email); ?>
		</td>
		<?php
	}

	public function mapping_row_subject( $email ){
		?>
		<td class="thwec-mapping-column-subject">
			<label class="thwec-paragraph thwec-block thwec-label-light">Edit email subject</label>
			<?php 
			$this->render_map_field_subject($this->map_fields['template-subject'], $email); 
			?>
		</td>
		<?php
		
	}

	private function render_map_field_attachment($email){
		$email = isset( $this->attachment_email_id[$email] ) ? $this->attachment_email_id[$email] : $email;
		$attachments = isset( $this->email_attachments[$email] ) ? $this->email_attachments[$email] : false;
		?>
		<td></td>
		<td colspan="2" class="thwec-mapping-column-attachment" data-email="<?php echo esc_attr($email); ?>">
			<div class="thwec-attachments-wrapper">
				<div class="thwec-upload-attachment thwec-paragraph thwec-label-light thwec-map-flex">
					<img src="<?php echo THWEC_ASSETS_URL_ADMIN.'images/upload-attachment.svg'; ?>">
					<div class="thwec-paragraph thwec-label-light thwec-map-flex">Upload files 
						<span class="thwec-map-link thwec-choose-attachment">Browse file to upload</span>
						<img src="<?php echo THWEC_ASSETS_URL_ADMIN.'images/choose-attachment.svg'; ?>">
					</div>
				</div>
				<div class="thwec-uploaded-attachments">
					<?php 
					if( is_array( $attachments ) ){
						foreach ($attachments as $index => $attachment) {
							$this->render_attachment( $email, $attachment );
						}
					}
					?>
				</div>
			</div>
		</td>
		<?php
	}

	private function render_attachment($email, $attachment){
		$file_name 	= isset( $attachment['file_name'] ) ? $attachment['file_name'] : '';
		$file_url 	= isset( $attachment['file_url'] ) ? $attachment['file_url'] : '';
		$file_size 	= isset( $attachment['file_size'] ) ? $attachment['file_size'] : '';
		?>
		<div class="thwec-uploaded-attachment">
            <div class="thwec-attachment-meta">
            	<span class="thwec-attachment-title"><?php echo esc_html( $file_name ); ?></span>
            	<span class="thwec-attachment-size thwec-label-light"><?php echo esc_html( $file_size ); ?></span>
            </div>
            <img src="<?php echo THWEC_ASSETS_URL_ADMIN.'/images/delete-attachment.svg' ?>" class="thwec-remove-attachment" />
            <input type="hidden" name="i_email-attachment-<?php echo esc_attr($email); ?>[]" value="<?php echo esc_html( $file_name ); ?>" />
            <input type="hidden" name="i_email-attachment-url-<?php echo esc_attr($email); ?>[]" value="<?php echo esc_html( $file_url ); ?>" />
            <input type="hidden" name="i_email-attachment-size-<?php echo esc_attr($email); ?>[]" value="<?php echo esc_html( $file_size ); ?>" />
        </div>
		<?php
	}

	/**
	 * Save the settings from posted from values
	 *
	 * @param  null
	 * @return boolean $result settings saved or not
	 */
	private function save_map(){
		$temp_data = array();
		$settings = $this->prepare_settings($_POST);
		$result = THWEC_Utils::save_template_settings($settings);
		return $result;
	}

	/**
	 * Prepare template settings from the posted form values
	 *
	 * @param  array $posted posted form values
	 * @return array $settings template settings
	 */
	private function prepare_settings($posted){
		$settings = THWEC_Utils::get_template_settings();
		$template_map = $settings[THWEC_Utils::SETTINGS_KEY_TEMPLATE_MAP];
		$template_subject = $this->prepare_template_subjects( $settings );
		$email_attachments = isset($settings[THWEC_Utils::SETTINGS_KEY_EMAIL_ATTACHMENTS]) ? $settings[THWEC_Utils::SETTINGS_KEY_EMAIL_ATTACHMENTS] : array();
		$file_ext = 'php';
		$def_subjects = THWEC_Utils::email_subjects();
		if( isset( $_POST['i_email-id'] ) && is_array( $_POST['i_email-id'] ) ){
			foreach ($_POST['i_email-id'] as $key => $value) {
				if( array_key_exists( sanitize_text_field( $value ), $this->email_list ) ){
					$template_map[sanitize_text_field( $value )] = isset( $_POST['i_template-list'][$key] ) ? sanitize_text_field( $_POST['i_template-list'][$key] ) : '';
					$subject = isset( $_POST['i_template-subject'][$key] ) ? sanitize_text_field( $_POST['i_template-subject'][$key] ) : '';
					
					$attachment = array();
					$attachment_email = isset( $this->attachment_email_id[$value] ) ? $this->attachment_email_id[$value] : $value;
					if( isset( $_POST['i_email-attachment-'.$attachment_email] ) && is_array( $_POST['i_email-attachment-'.$attachment_email] ) ){
						foreach($_POST['i_email-attachment-'.$attachment_email] as $index => $filename) {
							$attachment[$index] = array(
								'file_name' => sanitize_text_field( $filename ),
								'file_url' 	=> isset( $_POST['i_email-attachment-url-'.$attachment_email][$index] ) ? sanitize_url( $_POST['i_email-attachment-url-'.$attachment_email][$index] ) : '',
								'file_size' => isset( $_POST['i_email-attachment-size-'.$attachment_email][$index] ) ? sanitize_text_field( $_POST['i_email-attachment-size-'.$attachment_email][$index] ) : ''
							);
						}
					}
					$email_attachments[$attachment_email] = $attachment;
					if( empty( $subject ) ){
						$subject = isset( $def_subjects[$value] ) ? sanitize_text_field( $def_subjects[$value] ): '[{site_name}]: You have a new message';
					}
					$template_subject[sanitize_text_field($value)] = $subject;
				}
			}
		}
		$this->create_subject_translations( $template_subject );
		$settings[THWEC_Utils::SETTINGS_KEY_TEMPLATE_MAP] 	= $template_map;
		$settings[THWEC_Utils::SETTINGS_KEY_SUBJECT_MAP] 	= $template_subject;
		$settings[THWEC_Utils::SETTINGS_KEY_EMAIL_ATTACHMENTS] 	= $email_attachments;
		return $settings;
	}

	/**
	 * Register the subject strings to WPML
	 *
	 * @param  array $subjects subjects
	 * @return void
	 */
	private function create_subject_translations( $subjects ){
		if( is_array( $subjects ) ){
			foreach ($subjects as $id => $subject) {
				if( !in_array( $id, $this->admin_email_status ) ){
					$subject = $this->clean_subjects_for_translation( $subject );
					THWEC_Admin_Utils::wpml_register_string( $id, $subject);
				}
			}
		}
	}

	/**
	 * Replace subject placeholder with common placeholder for WPML support
	 *
	 * @param  string $subject subject
	 * @return string $subjects replaced subject.
	 */
	private function clean_subjects_for_translation( $subject ){
		foreach ($this->subject_placeholders as $index => $placeholder ) {
			$subject = str_replace( $placeholder, '%s', $subject);
		}
		return $subject;
	}

	/**
	 * Get the template subjects from settings
	 *
	 * @param  array $settings template settings
	 * @return array $subjects subjects of all available emails.
	 */
	private function prepare_template_subjects( $settings ){
		$subjects = array();
		if( isset( $settings[THWEC_Utils::SETTINGS_KEY_SUBJECT_MAP] ) &&  !empty( $settings[THWEC_Utils::SETTINGS_KEY_SUBJECT_MAP] ) ){
			$subjects = $settings[THWEC_Utils::SETTINGS_KEY_SUBJECT_MAP];
		}
		return $subjects;
	}

	/**
	 * Get the email subject for an email status. 
	 *
	 * @param  string $status name of the email status
	 * @return string $subject subject of the email status
	 */
	public function get_email_subject( $status ){
		$subject = '[{site_name}]: You have a new message';
		$defaults = THWEC_Utils::email_subjects();
		if( isset( $this->email_subjects[$status] ) && !empty( $this->email_subjects[$status] ) ){
			$subject = $this->email_subjects[$status];

		}else if( isset( $defaults[$status] ) && !empty( $defaults[$status] ) ){
			$subject = $defaults[$status];

		}
		return $subject;
	}

	/**
	 * Get the email subject for an email status. 
	 *
	 * @param  string $status name of the email status
	 * @return string $subject subject of the email status
	 */
	public function get_email_attachments( $status ){
		$attachment = array();
		if( isset( $this->email_attachments[$status] ) && !empty( $this->email_attachments[$status] ) ){
			$attachment = $this->email_attachments[$status];

		}
		return $attachment;
	}

	public function render_map_field_template( $field, $email ){
		$name = isset( $field['name'] ) ? $field['name'] : '';
		$class = isset( $field['class'] ) ? $field['class'] : '';
		$template = isset( $this->template_map[$email] ) ? $this->template_map[$email] : "";
		echo '<select name="i_'.$name.'" class="'.$class.'">';
		foreach ($this->templates as $key => $value) {
			$selected = $template === $key ? "selected" : "";
			echo '<option value="'.$key.'" '.$selected.'>'.$value.'</option>';
		}
		echo '</select>';
	}

	public function render_map_field_subject( $field, $email ){
		$name = isset( $field['name'] ) ? $field['name'] : '';
		$class = isset( $field['class'] ) ? $field['class'] : '';
		$subject = $this->get_email_subject( $email );
		echo '<textarea name="i_'.$name.'" class="'.$class.'">'.$subject.'</textarea>';
	}

	public function get_templates(){
		$new_templates = array();
		$wpml_templates = array();
		$select_box = array('' => 'Default Template');
		$templates = isset( $this->db_settings['templates'] ) ? $this->db_settings['templates'] : false;
		if( is_array( $templates ) ){
			foreach ($templates as $tkey => $tdata) {
				$tname = $tdata['display_name'];
				if( THWEC_Utils::is_wpml_active() && apply_filters('thwec_wpml_template_list_filter', true ) ){
					if( $this->is_wpml_template( $tkey ) ){
						$tname = $tdata['display_name'];
						$tkey = isset( $tdata['base'] ) ? $tdata['base'] : ( isset( $tdata['lang'] ) ? str_replace( '-'.$tdata['lang'] , '', $tkey ) : '' );
					}
				}else{
					$lang_suffix =  isset( $tdata['lang'] ) ? $tdata['lang'] : ''; 
					$tname = $tdata['display_name'].( !empty($lang_suffix) ? '[ '.$lang_suffix.' ]' : '');
				}
				$new_templates[$tkey] = $tname;
			}
		}
		return array_merge( $select_box, $new_templates );
	}

	 /**
	 * Checks whether the template is a wpml template in the configured default language
	 *
	 * @param  string $template_name template name key
	 * @return string template name without wpml language suffix
	 */	
	public function is_default_lang_template( $template_name ){
		$lang_code = THWEC_Utils::get_wpml_locale( apply_filters( 'wpml_default_language', NULL ), true );
		return str_replace( '-'.$lang_code, '', $template_name );
	}

	/**
	 * Checks whether the template is a wpml template
	 *
	 * @param  string $template template name key
	 * @return booelan wpml template or not
	 */	
	public function is_wpml_template( $template ){
		return is_array( $this->wpml_map ) && array_key_exists( $template, $this->wpml_map );
	}

	/**
	 * Reset the template settings. Includes template mapping and subjects
	 *
	 */
	public function reset_to_default() {
		$delete_opt = false;
		$delete_opt = THWEC_Utils::delete_settings();
		return $delete_opt;
	}

	/**
	 * Add required email statuses to customizer emails status from WC_Emails instance
	 *
	 * @return array $emails list of email statuses
	 */	
    public function get_email_instances( ){
    	$emails = array();
    	$this->compatibility_settings = THWEC_Utils::get_compatibility( $this->db_settings );
    	$wc_compatibility = array();
    	$wc_emails = WC_Emails::instance();
		$wc_emails = isset( $wc_emails->emails ) ? $wc_emails->emails : false;
		if( $wc_emails ){
			foreach ($wc_emails as $key => $wc_email) {
				if( in_array( $wc_email->id, THWEC_Utils::THWEC_EMAIL_INDEX ) ){
					continue;
				}
			
				if( THWEC_Utils::is_order_status_manager_active() && THWEC_Utils::is_order_status_manager_email( $wc_email) ){
					if( !in_array( $wc_email->id, $wc_compatibility ) ){
						$wc_status_manager = THWEC_Utils::get_order_status_manager_slug( $wc_email );
						$wc_compatibility[$wc_status_manager] = $wc_email->id;

					}
					$emails = THWEC_Utils::get_order_status_manager_emails( $wc_email, $emails );
				}
			}
			$this->compatibility_settings['wc-order-status-manager'] = $wc_compatibility;
		}
		return $emails;
    }
	
}

endif;