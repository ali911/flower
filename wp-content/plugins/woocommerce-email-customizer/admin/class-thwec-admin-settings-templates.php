<?php
/**
 * The admin template settings page functionality of the plugin.
 *
 * @link       https://themehigh.com
 * @since      1.0.0
 *
 * @package    woocommerce-email-customizer-pro
 * @subpackage woocommerce-email-customizer-pro/admin
 */
if(!defined('WPINC')){	die; }

if(!class_exists('THWEC_Admin_Settings_Templates')):

class THWEC_Admin_Settings_Templates{

	/**
	 * Main instance of the class
	 *
	 * @access   protected
	 * @var      $_instance    
	 */
	protected static $_instance = null;

	/**
	 * Stores the plugin template settings
	 *
	 * @access   private
	 * @var      $db_settings    template settings
	 */
	private $db_settings = array();

	/**
	 * Manages the list of email templates for displaying
	 *
	 * @access   private
	 * @var      $template_list    email templates list
	 */
	private $template_list = array();

	/**
	 * Stores the data required for the working of compatible plugins
	 *
	 * @access   private
	 * @var      $compatibility_settings    data required for compatible plugins
	 */
	private $compatibility_settings = array();

	/**
	 * Messages for template management actions
	 *
	 * @access   private
	 * @var      $map_msgs    messages
	 */
	private $map_msgs = array();

	/**
	 * Manages the relation between template and its wpml versions
	 *
	 * @access   private
	 * @var      $wpml_map    array of template and its wpml versions
	 */
	private $wpml_map = array();

	/**
	 * Construct
	 */
	public function __construct() {
		$this->init_constants();
	}
	
	/**
	 * Main THWEC_Admin_Settings_Templates Instance.
	 *
	 * Ensures only one instance of THWEC_Admin_Settings_Templates is loaded or can be loaded.
	 *
	 * @since 1.0
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
	 * initialize the variables required
	 */	
	public function init_constants(){
		$this->email_list = THWEC_Utils::email_statuses();

		$this->map_msgs = array(
			true	=> array(
				'msg' 	=> 	array(
					'save'		=>	'Settings saved',
					'reset'		=>	'Template settings successfully reset',
					'delete'	=>	'Template successfully deleted',
					'duplicate'	=>	'Template duplicate created successfully',
				),
				'class'		=>	'thwec-save-success',
			),
			false	=> array(
				'msg' 	=> 	array(
					'save'				=>	'Your changes were not saved due to an error (or you made none!).',
					'reset'				=>	'Reset not done due to an error (or nothing to reset!).',
					'delete'			=>	'An error occured or (or template file doesn\'t exist!).',
					'template-missing'	=>  'Your changes were not saved due to missing template files',
					'duplicate'	=>	'Template was not duplicated. Try again.',
				),
				'class'		=>	'thwec-save-error',
			),
		);
	}

	/**
	 * Get the templates list (either user or sample)
	 *
	 * @param  string $link_tab user templates or sample templates
	 * @return array template list
	 */	
	public function get_link_tab_templates( $link_tab ){
		$templates = isset( $this->template_list[$link_tab] ) && !empty( $this->template_list[$link_tab] ) ? $this->template_list[$link_tab] : array();
		if($link_tab === "sample"){
			return apply_filters('thwec_sample_template_listing', $templates);
		}
		return $templates;
	}

	private function get_template_label( $label ){
		if( strlen($label) > 0 && strlen($label) > 17 ){
			return substr($label, 0, 15)." ....";
		}
		return $label;
	}

	/**
     * Render the templates submenu page
     *
     */
	public function render_page( $migrate_instance ){
		$this->db_settings = THWEC_Utils::get_template_settings();
		$import_obj = THWEC_Import::instance();
		$import_obj->render_page();
		$this->email_list = array_merge( $this->email_list, $this->get_email_instances() );
		$this->render_content();
	}

	/**
	 * Render content of the page and manage form actions
	 */	
	private function render_content(){
		$map_result = 'onload';
		$map_action = false;
		if(isset($_POST['delete_template'])){
			$map_result = $this->delete_template();
			$map_action = 'delete';
		}
		if(isset($_POST['duplicate_template'])){
			$map_result = $this->duplicate_template();
			$map_action = 'duplicate';
		}
		if($map_result !== 'onload' && $map_action){
			$class = isset($this->map_msgs[$map_result]['class']) ? $this->map_msgs[$map_result]['class'] : '';
			$msg = isset($this->map_msgs[$map_result]['msg'][$map_action]) ? $this->map_msgs[$map_result]['msg'][$map_action] : '';
			$result = $map_result ? "success" : "error";
			$icons = $map_result ? "dashicons-yes" : "dashicons-no-alt";
			?>	
			<div id="thwec_temp_map_save_messages" class="thwec-template-validation">
				<div class="validation-wrapper thwec-<?php echo $result; ?>">
	        		<span class="dashicons <?php echo $icons; ?>"></span>
			        <div class="validation-messages">
			            <p class="thwec-label"><?php echo $result; ?></p>
			            <p class="thwec-label-light"><?php echo $msg; ?></p>
			        </div>
			    </div>
	        </div>
			<script type="text/javascript">
				jQuery(function($) {
				    setTimeout(function(){
						$("#thwec_temp_map_save_messages").remove();
					}, 2000);
				});
			</script>
			<?php
		}
		$this->init_helpers();
		$this->render_template_page();
    }

    /**
	 * Delete a template
	 *
	 * @param  null
	 * @return boolean $result templated deleted or not
	 */
	private function delete_template(){
		$result = false;
		$template_name = isset( $_POST['i_template_name'] ) ? sanitize_text_field( $_POST['i_template_name'] ) : false ;
		if( $template_name ){
			$settings = THWEC_Utils::get_template_settings();
			$templates = $settings[THWEC_Utils::SETTINGS_KEY_TEMPLATE_LIST];
			$wpml_map = isset( $settings[THWEC_Utils::wpml_map_key()] ) ? $settings[THWEC_Utils::wpml_map_key()] : false;
			$settings = $this->delete_db_data( $template_name, $templates, $wpml_map, $settings );
			$result = THWEC_Utils::save_template_settings($settings);
		}
		return $result;
	}

	/**
	 * Delete a template
	 *
	 * @param  string $template_name name of the template
	 * @param  array  $templates list of templates
	 * @param  array  $wpml_map  wpml compatible template names
	 * @param  array  $settings template settings
	 * @return array $settings settings after removing selected template
	 */
	private function delete_db_data( $template_name, $templates, $wpml_map, $settings ){
		if( isset( $templates[$template_name] ) ){
			if( $wpml_map && is_array( $wpml_map ) && array_key_exists( $template_name, $wpml_map ) ){
				//Check if wpml template
				if( apply_filters('thwec_wpml_template_list_filter', true ) && THWEC_Utils::is_wpml_active() ){
				// Delete all translation of a template at once
					$wpml_base_name = $wpml_map[$template_name];
					$keys = array_keys( $wpml_map, $wpml_map[$template_name] );
					if( is_array( $keys ) ){
						foreach ( $keys as $index => $translated ) {
							$this->delete_file( $translated );
							unset( $wpml_map[$translated] );
							if( isset( $templates[$translated] ) ){
								unset( $templates[$translated]);
							}
						}
					}
					$settings = $this->delete_from_template_map( $wpml_base_name, $settings );
				}else{
					// Delete Single WPML template
					$this->delete_file( $template_name );
					$base_template = $wpml_map[$template_name];
					unset($templates[$template_name]);
					unset( $wpml_map[$template_name] );
					if( count( array_keys( $wpml_map, $base_template ) ) < 1 ){
						$settings = $this->delete_from_template_map( $base_template, $settings );
					}
				}
				$settings[THWEC_Utils::wpml_map_key()] = $wpml_map;
			}else{
				// Non WPML template
				$this->delete_file( $template_name );
				unset($templates[$template_name]);
				$settings = $this->delete_from_template_map( $template_name, $settings );
			}
		}else{
			//Deleting templates with create translation ( missing translation ) option
			$template_name = isset( $_POST['i_template_translation_name'] ) ? sanitize_text_field( $_POST['i_template_translation_name'] ) : false;
			if( isset( $templates[$template_name] ) ){
				$this->delete_file( $template_name );
				unset($templates[$template_name]);
				unset( $wpml_map[$template_name] );
				$settings = $this->delete_from_template_map( $template_name, $settings );
			}
			
		}
		$settings[THWEC_Utils::SETTINGS_KEY_TEMPLATE_LIST] = $templates;
		return $settings;
		
	}

	/**
	 * Delete the template from template map
	 *
	 * @param  $template_name name of the template
	 * @param  $settings plugin settings
	 * @return boolean $settings plugin settings
	 */
	private function delete_from_template_map( $template_name, $settings ){
		if( isset( $settings['template_map'] ) && is_array( $settings['template_map'] ) ){
			if( in_array( $template_name, $settings['template_map'] ) ){
				$map_keys = array_keys( $settings['template_map'], $template_name );
				if( is_array( $map_keys ) ){
					foreach ( $map_keys as $index => $template_key ) {
						$settings['template_map'][$template_key] = '';
					}
				}
			}
		}
		return $settings;
	}

	/**
	 * Delete a template file
	 *
	 * @param  string $template_name name of the template
	 * @return void
	 */
	private function delete_file( $template_name ){
		$file = THWEC_CUSTOM_TEMPLATE_PATH.$template_name.'.php';
		if(is_file($file)){
			unlink($file); // delete file		  	
		}
	}

	private function duplicate_template(){
		$result = false;
		$template_name = isset( $_POST['i_template_name'] ) ? sanitize_text_field( $_POST['i_template_name'] ) : false ;
		if( $template_name ){
			$settings = THWEC_Utils::get_template_settings();
			$templates = $settings[THWEC_Utils::SETTINGS_KEY_TEMPLATE_LIST];
			$wpml_map = isset( $settings[THWEC_Utils::wpml_map_key()] ) ? $settings[THWEC_Utils::wpml_map_key()] : array();
			$clone = isset( $templates[$template_name] ) ? $templates[$template_name] : false;
			if( $clone ){
				$template_key = date("Y_m_d_H_i_s");
				if( THWEC_Utils::is_wpml_active() && isset( $clone["lang"] ) && isset( $clone["base"] ) ){
					$base_template = $clone["base"];
					$wpml_templates = in_array( $base_template, $wpml_map ) ? array_keys( $wpml_map, $base_template ) : false;
					$clone_languages = array();
					if( is_array( $wpml_templates) ){
						foreach ($wpml_templates as $index => $wpml_key ) {
							$wpml_template = isset( $templates[$wpml_key] ) ? $templates[$wpml_key] : false;
							if( $wpml_template ){
								$language = THWEC_Utils::is_wpml_active() ? ( isset( $wpml_template["lang"] ) ? $wpml_template["lang"] : "" ) : "";
								$templates = $this->copy_template($template_key, $wpml_template, $templates, $language);
								$wpml_map[$template_key."-".$language] = $template_key;
							}
						}
						$settings[THWEC_Utils::wpml_map_key()] = $wpml_map;
					}
				}else{
					$templates = $this->copy_template($template_key, $clone, $templates, false);
				}
				$settings[THWEC_Utils::SETTINGS_KEY_TEMPLATE_LIST] = $templates;
			}
			$result = THWEC_Utils::save_template_settings($settings);
		}
		return $result;
	}

	/**
	 * Duplicate a template file
	 *
	 * @param  string $name name of the template to duplicate
	 * @param  array $templates list of templates
	 * @param  array $wpml_map array of wpml templates
	 * @return array $templates modified list of templates
	 */
	private function copy_template( $clone_key, $clone, $templates, $language=false ){
		$display_name = isset( $clone["display_name"] ) ? $clone["display_name"] : false;
		$clone["display_name"] = !$display_name ? $clone_key : trim($display_name)." Copy";
		if( isset( $clone["base"] ) ){
			$clone["base"] = $clone_key;
		}
		if( !empty($language) ){
			$clone_key = $clone_key."-".$language;
		}
		if( isset($templates[$clone_key]) ){
			return $templates;
		}
		$templates[$clone_key] = $clone;
		return $templates;
	}

     /**
	 * execute helper functions
	 */	
    public function init_helpers(){
    	$this->db_settings = THWEC_Utils::get_template_settings();
    	$this->check_for_missing_dependencies();
    	$this->template_list = THWEC_Utils::get_template_list($this->db_settings, true);
    	$this->wpml_map = THWEC_Utils::get_wpml_map( $this->db_settings );
    }

     /**
	 * Display the template page content
	 */	
	public function render_template_page(){
		$sample_templates = $this->get_link_tab_templates( 'sample' );
    	$custom_templates = $this->get_link_tab_templates( 'user' );
    	if( THWEC_Utils::is_wpml_active() && apply_filters('thwec_wpml_template_list_filter', true ) ){
			$custom_templates = $this->format_template_list($custom_templates);
		}
    	?>
    	<div id="thwec_ajax_load_modal"></div>
    	<div id="thwec_import_modal" style="display: none;">
    		<div class="import-wrapper">
    		</div>
    	</div>
    	<div class="thwec-template-sample-wrapper thwec-template-wrapper">
    		<div class="thwec-template-header mb-20">
    			<p class="thwec-main-heading">Templates</p>
    			<div class="thwec-template-search">
    				<img class="thwec-search-icon" src="<?php echo THWEC_ASSETS_URL_ADMIN.'images/search.svg'; ?>">
	    			<input type="text">
	    		</div>
    		</div>
    		<div class="thwec-template-header">
    			<p class="thwec-sub-heading">Sample Templates</p>
    		</div>
    		<div class="thwec-templates thwec-sample-templates">
		    	<div class="thwec-template-preview-wrapper">
		    		
					<?php foreach ($sample_templates as $key => $label) { 
						$sample_icon = $key === "notifier_instock_mail" || $key === "notifier_subscribe_mail" ? 'custom-templates' : $key;
					?>
						<div class="thwec-template-box">
							<form name="thwec_edit_template_form_<?php echo $key; ?>" action="" method="POST">
								<input type="hidden" name="i_template_type" value="sample">
								<input type="hidden" name="i_template_name" value="<?php echo $key; ?>">
								<div class="thwec-template-image" style='background-image: url(<?php echo THWEC_ASSETS_URL_ADMIN."images/${sample_icon}.svg" ?>) ;'>	
								</div>
								<div class="thwec-template-name">
									<p class="thwec-label" title="<?php echo esc_attr( $label['display_name'] ); ?>"><?php echo esc_html( $this->get_template_label( $label['display_name'] ) ); ?></p>
								</div>
								<div class="close-template-manage-menu"></div>
								<div class="template-manage-menu">
									<div class="template-manage-menu-item">
										<button type="submit" class="thwec-template-action-links" formaction="<?php echo THWEC_Utils::get_admin_url(); ?>" name="edit_template" title="Edit">
											<img src="<?php echo THWEC_ASSETS_URL_ADMIN ?>/images/template-edit.svg">
										</button>
									</div>
								</div>
							</form>
						</div>
					<?php } ?>
		    	</div>
		    </div>
		</div>
		<div id="thwec_template_list">
			<div class="thwec-template-custom-wrapper thwec-template-wrapper">
				<div class="thwec-template-header thwec-custom-template-header">
		    		<div class="thwec-custom-templates-left">
			    		<p class="thwec-sub-heading">Custom Templates</p>
			    		<a id="export_templates" class="button">Export</a>
			    		<a id="import_templates" class="button">Import</a>
			    	</div>
			    	<div class="thwec-custom-templates-right" style="display: none;">
			    	</div>
		    	</div>
			    <div class="thwec-templates thwec-custom-templates">
		    		<div class="thwec-template-preview-wrapper">
			    		<?php foreach ($custom_templates as $key => $label) { ?>
			    			<div class="thwec-template-box">
			    				<form name="thwec_edit_template_form_<?php echo $key; ?>" action="" method="POST">
			    					<input type="hidden" name="i_template_type" value="custom">
									<input type="hidden" name="i_template_name" value="<?php echo $key; ?>">
									<input type="hidden" name="i_template_translation_name" value="<?php echo isset($label['lang']) ? $label['lang'] : ''; ?>">
				    				<div class="thwec-template-image" style="background-image: url(<?php echo THWEC_ASSETS_URL_ADMIN ?>/images/custom-templates.svg) ;">	
									</div>
									<div class="template-manage-menu">
										<div class="template-manage-menu-item">
											<button type="submit" class="thwec-template-action-links" formaction="<?php echo THWEC_Utils::get_admin_url(); ?>" name="edit_template" title="Edit">
												<img src="<?php echo THWEC_ASSETS_URL_ADMIN ?>/images/template-edit.svg">
											</button>
											<button type="submit" class="thwec-template-action-links delete-template" name="delete_template" title="Delete">
												<img src="<?php echo THWEC_ASSETS_URL_ADMIN ?>/images/template-delete.svg">
											</button>
											<button type="submit" class="thwec-template-action-links" name="duplicate_template" title="Duplicate">
												<img src="<?php echo THWEC_ASSETS_URL_ADMIN ?>/images/template-duplicate.svg">
											</button>
										</div>
									</div>
				    				<div class="thwec-template-name">
				    					<p class="thwec-label" title="<?php echo esc_attr( $label['display_name'] ); ?>"><?php echo esc_html( $this->get_template_label( $label['display_name'] ) ); ?></p>
				    				</div>
				    				<input type="checkbox" name="thwec_user_templates[]" value="<?php echo esc_attr($key); ?>" class="select-export-template">
				    			</form>
			    			</div>
			    		<?php } ?>
						<div class="thwec-template-box thwec-template-add-new">
							<div class="thwec-add-new">
								<a href="<?php echo esc_url( THWEC_Utils::get_admin_url() ); ?>">
									<span class="dashicons dashicons-plus-alt2"></span>
									<p class="thwec-paragraph">Add New</p>
								</a>
							</div>
						</div>
			    	</div>
	    		</div>
	    	</div>
	    	<div id="export_actions_panel" style="display: none;">
	    		<div class="export-panel-fields">
	    			<div class="export-field-columns">
						<p>
							<span class="label-left">Copy email subjects</span>
							<label for="export_email_subjects">
								<input type="checkbox" name="thwec_copy_subjects" id="thwec_copy_subjects">
								Click to copy email subject
							</label>
						</p>
						<p>
							<span class="label-left">Copy email mapping</span>
							<label for="export_email_mapping">
								<input type="checkbox" name="thwec_copy_mapping" id="thwec_copy_mapping">
								Copy email mapping
							</label>
						</p>
	    			</div>
	    			<button class="button" type="button" name="do_export" id="do_export">Export</button>
	    		</div>
	    	</div>
	    </div>
    	<?php
    }

    /**
	 * Remove missing template from the template map. 
	 *
	 * Fix for WECM-294
	 *
	 */
	private function remove_missing_template_from_map(){
		if( apply_filters( 'thwec_missing_templates_in_mapping', true ) && isset( $this->db_settings[THWEC_Utils::get_templates_map_key()] ) ){
    		$maps = $this->db_settings[THWEC_Utils::get_templates_map_key()];
    		$templates = $this->db_settings[THWEC_Utils::get_templates_key()];
    		$wpml_map = isset( $this->db_settings[THWEC_Utils::wpml_map_key()] ) ? $this->db_settings[THWEC_Utils::wpml_map_key()] : array();
    		if( is_array( $maps ) ){
    			foreach ($maps as $key => $name) {
    				if( empty( $name ) ){
    					continue;
    				}
    				if( !isset( $templates[$name] ) && !in_array( $name, $wpml_map ) ){
    					$remove = array_keys( $maps, $name );
    					
    					if( is_array( $remove ) ){
    						foreach ($remove as $r_index => $r_key) {
    							$this->db_settings[THWEC_Utils::get_templates_map_key()][$r_key] = '';
    						}
    					}
    				}
    			}
    			THWEC_Utils::save_template_settings($this->db_settings);
    		}
    	}
	}

	/**
	 * Get the template submenu url
	 */	
	public function get_template_manage_url( $action=false ){
		$url = 'admin.php?page=th_email_customizer_templates';
		if( $action && !empty( $action ) ){
			$url .= '&action='.$action;
		}
		return admin_url($url);
	}

	/**
	 * Add required email statuses to customizer emails status from WC_Emails instance
	 *
	 * @return array $emails list of email statuses
	 */	
    public function get_email_instances( ){
    	$emails = array();
    	$this->compatibility_settings = THWEC_Utils::get_compatibility( $this->db_settings );
    	$wc_compatibility = isset( $this->compatbility['wc-order-status-manager'] ) ? $this->compatbility['wc-order-status-manager'] : array();
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

    /**
	 * Find and save missing settings that are required later.
	 */	
    public function check_for_missing_dependencies(){
    	$changed = false;
    	$sample_templates_change = false;
    	if( isset( $this->db_settings[THWEC_Utils::get_template_samples_key()] ) && empty( $this->db_settings[THWEC_Utils::get_template_samples_key()] ) ){
    		$this->db_settings[THWEC_Utils::get_template_samples_key()] = THWEC_Utils::get_sample_settings();
    		$changed = true;
    		$sample_templates_change = true;
    	}
    	if( isset( $this->db_settings[THWEC_Utils::get_template_subject_key()] ) && empty( $this->db_settings[THWEC_Utils::get_template_subject_key()] ) ){
    		$this->db_settings[THWEC_Utils::get_template_subject_key()] = THWEC_Utils::email_subjects();
    		$changed = true;
    	}

    	if( isset( $this->db_settings[THWEC_Utils::thwec_compatibility_key()] ) ){
    		$compatibility = $this->db_settings[THWEC_Utils::thwec_compatibility_key()];
    		if( is_array( $this->compatibility_settings['wc-order-status-manager'] ) ){
    			if( is_array( $compatibility['wc-order-status-manager'] ) ){
    				if( array_keys( $compatibility['wc-order-status-manager'] ) != array_keys( $this->compatibility_settings['wc-order-status-manager'] ) ){
		    			$this->db_settings[THWEC_Utils::thwec_compatibility_key()] = $this->compatibility_settings;
		    			$changed = true;
    				}
    			}
    		}
    	}else{
    		$this->db_settings[THWEC_Utils::thwec_compatibility_key()] = $this->compatibility_settings;
    		$changed = true;
    	}

    	// if(!$sample_templates_change && THWEC_Utils::is_back_in_stock_notifier_active() && isset( $this->db_settings[THWEC_Utils::get_template_samples_key()] )){
    	// 	if( !isset( $this->db_settings[THWEC_Utils::get_template_samples_key()]['notifier_instock_mail'] ) || !isset( $this->db_settings[THWEC_Utils::get_template_samples_key()]['notifier_subscribe_mail'] ) ){
    	// 		$sample_template_settings = THWEC_Utils::get_sample_settings();
    	// 		$this->db_settings[THWEC_Utils::get_template_samples_key()] = apply_filters('thwec_sample_templates', $this->db_settings[THWEC_Utils::get_template_samples_key()], $sample_template_settings );
    	// 		$changed = true;
    	// 	}
    	// }

    	if( $changed ){
    		THWEC_Utils::save_template_settings($this->db_settings);
    		$this->db_settings = THWEC_Utils::get_template_settings();
    	}
    }

    /**
	 * Format the template names to show/hide wpml language suffix
	 *
	 * @param  array $list template list
	 * @return array $nvalue updated template list
	 */	
	public function format_template_list( $list ){
		$nvalue = array();
		$wpml_list = array();
		$def_lang = THWEC_Utils::get_wpml_locale( apply_filters( 'wpml_default_language', NULL ), true );
		foreach ($list as $index => $data) {
			if( isset($data['lang']) ){
				// Lang templates
				if( array_key_exists( $index, $this->wpml_map ) ){
					$base_template = $this->wpml_map[$index];
					if( !in_array( $base_template, $wpml_list ) ){
						array_push( $wpml_list, $base_template );
						$template = $base_template.'-'.$def_lang;
						$nvalue[$template] = isset( $list[$template] ) ? $list[$template] : $this->prepare_template_name( $base_template, $list ) ;
					}
				}else{
					$base_template = isset( $data['base'] ) ? $data['base'] : false;
					if( $base_template && !in_array( $base_template, $wpml_list ) ){
						array_push( $wpml_list, $base_template );
						$template = $base_template.'-'.$def_lang;
						$nvalue[$template] = isset( $list[$template] ) ? $list[$template] : $this->prepare_template_name( $base_template, $list ) ;
					}
				}
			}else{
				//Other non wpml templates
				$nvalue[$index] = $data;
			}
		}
		return $nvalue;
	}

	/**
	 * Get the status and label of the template for WPML compatibility
	 *
	 * @param  string $basename template name key
	 * @return array status and label of template
	 */	
	public function prepare_template_name( $basename, $list ){
		$langs = icl_get_languages();
		if( is_array( $langs ) ){
			foreach ( $langs as $language => $object ) {
				$key = $basename.'-'.strtolower($object['default_locale']);
				if( isset( $list[$key] ) && isset( $list[$key]['display_name'] ) ){
					return array( 'status' => 'missing', 'display_name' => $list[$key]['display_name'], 'key' => $key );
				}
			}
		}
	}

	
}

endif;