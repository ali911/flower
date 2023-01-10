<?php
/**
 * The import functionality of the plugin setings.
 *
 * @link       https://themehigh.com
 * @since      3.4.0
 *
 * @package    woocommerce-email-customizer-pro
 * @subpackage woocommerce-email-customizer-pro/admin
 */
if(!defined('WPINC')){	die; }

if(!class_exists('THWEC_Import')):
 
class THWEC_Import {
	/**
	 * Main instance of the class
	 *
	 * @access   protected
	 * @var      $_instance    
	 */
	protected static $_instance = null;

	/**
	 * Manage the templates submenu url
	 *
	 * @access   private
	 * @var      $url    templates submenu url
	 */
	private $url = null;

	/**
	 * Manages the file name of the file to be imported
	 *
	 * @access   private
	 * @var      $filename    file name of exported file
	 */
	private $file_name = '';

	/**
	 * Construct
	 */
	public function __construct() {
		add_action('wp_ajax_thwec_do_import', array($this,'thwec_import_settings'));
		$this->templates_url = admin_url( 'admin.php?page=th_email_customizer_templates' );
	}

	/**
	 * Main THWEC_Import Instance.
	 *
	 * Ensures only one instance of THWEC_Import is loaded or can be loaded.
	 *
	 * @since 3.4.0
	 * @static
	 * @return THWEC_Import Main instance
	 */
	public static function instance() {
		if(is_null(self::$_instance)){
			self::$_instance = new self();
		}
		return self::$_instance;
	}

	public function render_page(){
		THWEC_Utils::delete_backup_directory();
		$this->render_importer_wizard();
		$this->importer_content_done();
		$this->importer_content_failed();
	}

	/**
	 * Import wizard screen
	 */
	public function render_importer_wizard(){ 
		?>
		<div id="import_modal_content" style="display: none;">
			<form name="thwec_template_import_form" enctype="multipart/form-data" method="POST">
				<div class="import-modal-header">
					<h2 class="thwec-main-heading">Import</h2>
					<span class="close-modal"></span>
				</div>
				<div class="thwec-import-upload-file">
					<p class="thwec-label">Upload and import template files to your store</p>
					<div class="thwec-drop-contents">
						<img src="<?php echo THWEC_ASSETS_URL_ADMIN.'/images/browse.svg'?>">
						<p class="thwec-label thwec-label-light">Choose a settings file <span class="thwec-browse-link">Browse</span></p>
						<input type="file" name="thwec_import_file" class="thwec-import-file">
					</div>
				</div>
			</form>
		</div>
		<?php
	}

	/**
	 * Import wizard screen - Import completed
	 */
	private function importer_content_done(){
		?>
		<div id="thwec_import_success" style="display: none;">
			<div class="import-completed">
				<p class="thwec-port-completed-icon">
					<img src="<?php echo THWEC_ASSETS_URL_ADMIN.'images/upload-completed.svg'; ?>">
				</p>
				<h2 class="thwec-label">Import Completed</h2>
				<p class="wecm-import-msg thwec-label-light"></p>
				<a href="<?php echo esc_url( $this->templates_url); ?>" name="thwec_view_templates">View Templates</a>
			</div>
		</div>
		<?php
	}

	/**
	 * Import wizard screen - Import failed
	 */
	private function importer_content_failed(){
		?>
		<div id="thwec_import_failed" style="display: none;">
			<div class="import-completed">
				<p class="thwec-port-completed-icon">
					<img src="<?php echo THWEC_ASSETS_URL_ADMIN.'images/upload-completed.svg'; ?>">
				</p>
				<h2 class="thwec-label">Import Completed</h2>
				<p class="wecm-import-msg thwec-label-light"></p>
				<a href="<?php echo esc_url( $this->templates_url); ?>" name="thwec_view_templates">View Templates</a>
			</div>
		</div>
		<?php
	}

	/**
	 * Prepare directory and Import uploaded content
	 */
	public function thwec_import_settings(){
		check_ajax_referer( 'thwec-settings-import', 'security' );
		$this->create_import_directory();
		$this->import_actions();
	}

	/**
	 * import ajax function
	 */	
	public function import_actions(){
		$result = array('status' => 'error');
		if( isset( $_POST['screen'] ) ){
			$screen = sanitize_key( $_POST['screen'] );
			if( $screen == 'upload' ){
				$this->perform_import_action_upload();

			}else if( $screen == 'import' ){
				$this->perform_import_action_import();
			}
		}else{
			$result['message'] = 'No screen found';
		}
		wp_send_json( $result );
	}

	/**
	 * Prepare upload directory
	 *
	 * @param  array $upload_dir upload directory attributes
	 * @return array $upload_dir upload directory attributes
	 */	
	public function upload_dir( $upload_dir ){
		$upload_dir['subdir'] = '/thwec_templates/backup';
	    $upload_dir['path'] = $upload_dir['basedir'] . '/thwec_templates/backup';
	    $upload_dir['url'] = $upload_dir['baseurl'] . '/thwec_templates/backup';
	 	
		return $upload_dir;
	}

	/**
	 * Prepare for importing uploaded file
	 *
	 * @return void
	 */
	public function perform_import_action_upload(){
		$uploaded = $this->move_package();
		if( $uploaded && !isset( $uploaded['error'] ) ){
			$this->file_name = basename( $uploaded['file'] );
			if( $this->unpack_package() ){
				$this->copy_settings();
			}
		}
	}

	/**
	 * Move uploaded zip file to directory
	 *
	 * @return array $uploaded uploaded file attributes || error data
	 */
	public function move_package(){
		$uploaded = false;
		if( isset( $_FILES['thwec_import_file']['tmp_name'] ) ){
			if( !$this->valid_settings_file( $_FILES['thwec_import_file'] ) ){
				wp_send_json( $this->error_code('invalid-file') );
			}

			if(!function_exists('wp_handle_upload')){
				require_once(ABSPATH. 'wp-admin/includes/file.php');
				require_once(ABSPATH. 'wp-admin/includes/media.php');
			}
			
			add_filter('upload_dir', array( $this, 'upload_dir'));
			$uploaded = wp_handle_upload($_FILES['thwec_import_file'], array('test_form' => false));
			remove_filter('upload_dir', array( $this, 'upload_dir'));	
		}
		return $uploaded;
	}

	/**
	 * Prepare for package extraction
	 *
	 * @return boolean $extracted extracted or not
	 */
	public function unpack_package(){
		$unpacked = false;
		$working_directory = THWEC_Utils::get_backup_path();
		if( is_dir( $working_directory ) && is_writable( $working_directory ) ){
			// Check that the folder contains at least valid settings.
			if( file_exists( $working_directory.'/'.$this->file_name ) ){
				$unpacked = $this->extract_zip();
			}
		}
		return $unpacked;
	}

	/**
	 * Extract uploaded zip
	 *
	 * @return boolean $extracted extracted or not
	 */
	public function extract_zip(){
		$extracted = false;
		$import_zipname = THWEC_Utils::get_backup_path( $this->file_name );
		$zip = new ZipArchive;

		if( $zip->open( $import_zipname ) === TRUE ){
			if( $zip->locateName('templates/') === false || $zip->locateName('settings.txt') === false ){
				$zip->close();
				THWEC_Utils::delete_backup_directory();
				wp_send_json( $this->error_code('invalid-zip') );
			}else{
				$extracted = $zip->extractTo( THWEC_Utils::get_backup_path());
			}
			wp_delete_file( $import_zipname );
		}
		return $extracted;
	}

	/**
	 * Set error data for ajax response
	 *
	 * @param  array $status status of file upload
	 * @return array $error updated status for ajax response
	 */	
	public function error_code( $status ){
		$error = array();
		$error['error'] = 'error';
		if( $status == 'invalid-file' ){
			$status = 'Invalid file type. Choose a valid file.';
		}else if( $status == 'invalid-zip' ){
			$status = 'No valid templates were found';
		}
		$error['message'] = $status;
		return $error;
	}

	/**
	 * Check if uploaded file is a zip
	 *
	 * @param  array $file uploaded file data
	 * @return boolean zip file or not
	 */	
	public function valid_settings_file( $file ){
		return "zip" == strtolower( pathinfo($file['name'], PATHINFO_EXTENSION) );
	}

	/**
	 * Sanitize the checkbox value and return a boolean value
	 *
	 * @param string $boolean required.
	 * @return boolean
	 */
	private function validate_boolean( $boolean ){
		return filter_var( $boolean, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE );
	}

	/**
	 * Sanitizing template keys
	 */	
	private function sanitize_template_data( $templates ){
		if( is_array( $templates ) ){
			$templates = array_map( 'sanitize_text_field', $templates );
		}
		return $templates;
	}

	/**
	 * Importing uploaded contents
	 */	
	public function perform_import_action_import(){
		$import_count = 0;
		$wpml_map = array();

		$imported_settings = get_option('thwec_import_settings');
		$plugin_settings = THWEC_Utils::get_template_settings();

		$template_list = isset( $_POST['thwec_import_template_list'] ) ? $_POST['thwec_import_template_list'] : false;
		$copy_subject = isset( $_POST['thwec_copy_subjects'] ) ?  $this->validate_boolean( $_POST['thwec_copy_subjects'] ) : false;
		$copy_mapping = isset( $_POST['thwec_copy_mapping'] ) ?  $this->validate_boolean( $_POST['thwec_copy_mapping'] ) : false;
		$replace_existing = isset( $_POST['thwec_replace_existing'] ) ?  $this->validate_boolean( $_POST['thwec_replace_existing'] ) : false;

		if( $template_list && is_array($template_list) ){
			//Copy the selected
			foreach ($template_list as $index => $template_key) {
				if( isset( $imported_settings[THWEC_Utils::get_templates_key()][$template_key] ) ){
					if( isset( $plugin_settings[THWEC_Utils::get_templates_key()][$template_key] ) && !$replace_existing ){
						unset( $template_list[$index] );
						continue;
					}
					$plugin_settings[THWEC_Utils::get_templates_key()][$template_key] = $imported_settings[THWEC_Utils::get_templates_key()][$template_key];
					$import_count++;
					if( isset( $imported_settings[THWEC_Utils::wpml_map_key()][$template_key] ) && !array_key_exists($template_key, $wpml_map ) ){
						$wpml_map[$template_key] = $imported_settings[THWEC_Utils::wpml_map_key()][$template_key];
					}
				}else{
					unset( $template_list[$index] );
				}
			}
			if( !empty( $wpml_map ) ){
				$plugin_settings[THWEC_Utils::wpml_map_key()] = $wpml_map;
			}
		}

		if( $copy_subject ){
			if( isset( $imported_settings[THWEC_Utils::get_template_subject_key()] ) && !empty( $imported_settings[THWEC_Utils::get_template_subject_key()] ) ){
				$plugin_settings[THWEC_Utils::get_template_subject_key() ] = $imported_settings[THWEC_Utils::get_template_subject_key()];
			}
		}

		if( $copy_mapping ){
			if( isset( $imported_settings[THWEC_Utils::get_templates_map_key()] ) && !empty( $imported_settings[THWEC_Utils::get_templates_map_key()] ) ){
				$plugin_settings[THWEC_Utils::get_templates_map_key() ] = $imported_settings[THWEC_Utils::get_templates_map_key()];
			}
		}
		$imported_files = $this->copy_templates( $template_list, $imported_settings );
		$result = THWEC_Utils::save_template_settings($plugin_settings);
		if( $result ){
			$this->delete_import_directory();
		}
		$import_count = ( $import_count == $imported_files ) ? $import_count : 'missing';
		wp_send_json( array(
			'status' => 'success',
			'screen' => 'import', 
			'count' => $import_count,
			'url' => $this->templates_url
		) );
	}

	/**
	 * Copy templates from backup to template folder
	 *
	 * @param  array $template_list templates
	 * @param  array $imported_settings imported settings
	 * @return array $import_count count of imported templates
	 */	
	public function copy_templates( $template_list, $imported_settings ){
		$import_count = 0;
		$templates = $imported_settings[THWEC_Utils::get_templates_key()];
		foreach ($template_list as $index => $key) {
			if( isset( $templates[$key]['file_name'] ) && !empty( $templates[$key]['file_name'] ) ){
				if( file_exists( THWEC_Utils::get_backup_path( '/templates/'.$templates[$key]['file_name'] ) ) ){
					$moved = rename(THWEC_Utils::get_backup_path( '/templates/'.$templates[$key]['file_name'] ), THWEC_CUSTOM_TEMPLATE_PATH.'/'.$templates[$key]['file_name']);
					if( $moved ){
						$import_count++;
					}
				}
			}
		}
		return $import_count;
	}

	/**
	 * Copy settings from file 
	 *
	 */
	public function copy_settings(){
		if( file_exists( THWEC_Utils::get_backup_path( 'settings.txt') ) ){
			$contents = file_get_contents( THWEC_Utils::get_backup_path( 'settings.txt') );
			$contents = unserialize(base64_decode($contents));
			$option = $this->thwec_prepare_temp_options($contents);
			if( $option ){
				$result =  array(
					'status' => 'success',
					'screen' => 'upload',
					'templates' => isset( $contents['templates'] ) ? $this->prepare_template_list( $contents['templates'] ) : '',
					'subject' => isset( $contents['email_subject'] ) && !empty( $contents['email_subject'] ) ? true : false,
					'template_map' => isset( $contents['template_map'] ) && !empty( $contents['template_map'] ) ? true : false,
					'thwec_wpml_map' => isset( $contents['thwec_wpml_map'] ) && !empty( $contents['thwec_wpml_map'] ) ? $contents['thwec_wpml_map'] : array(),
				);
				wp_send_json($result);
			}
		}
	}

	/**
	 * Template list 
	 */
	public function prepare_template_list( $templates ){
		$tlist = array();
		if( is_array( $templates ) ){
			foreach ($templates as $key => $template) {
				$t_suffix = isset( $template['lang'] ) ? sanitize_text_field( $template['lang'] ) : '';
				$t_name = isset( $template['display_name'] ) ? sanitize_text_field( $template['display_name'] ) : '';
				if( !empty( $t_suffix ) ){
					$t_name = $t_name.'['.$t_suffix.']';
				}
				$tlist[$key] = $t_name;
			}
			ksort( $tlist );
		}
		return $tlist;
	}

	/**
	 * Create temporary option for for storing imported settings
	 *
	 * @param  array $contents templates list
	 * @return boolean option saved or not
	 */
	public function thwec_prepare_temp_options( $contents ){
		delete_option('thwec_import_settings');
		return add_option('thwec_import_settings', $contents);
	}

	/**
	 * Create backup directory for managing imported file
	 *
	 */
	public function create_import_directory(){
		$upload_dir = THWEC_Utils::get_backup_path();
		if( !file_exists( $upload_dir ) ){
			wp_mkdir_p( $upload_dir );
		}
	}

	/**
	 * Delete backup directory for managing imported file
	 *
	 */
	public function delete_import_directory(){
		$backup_dir = THWEC_Utils::get_backup_path();
		if( file_exists( $backup_dir ) ){
			$this->delete_files( $backup_dir );
			rmdir( $backup_dir );
		}
	}

	/**
	 * Delete files in backup directory
	 *
	 */
	public function delete_files( $dir ){
		$files = scandir( $dir ); // get all file names
		foreach( $files as $file ){ // iterate files
			if( $file != '.' && $file != '..' ){ //scandir() contains two values '.' & '..' 
				if( is_dir( $dir.'/'.$file ) && $file == 'templates' ){
					$this->delete_files( $dir.'/'.$file );
					rmdir( $dir.'/'.$file );
				}else if( is_file( $dir.'/'.$file ) ){
					unlink( $dir.'/'.$file ); // delete file		  	
				}
			}
		}
	}

	
}

endif;