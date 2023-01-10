<?php
/**
 * The export functionality of the plugin setings.
 *
 * @link       https://themehigh.com
 * @since      3.4.0
 *
 * @package    woocommerce-email-customizer-pro
 * @subpackage woocommerce-email-customizer-pro/admin
 */
if(!defined('WPINC')){	die; }

if(!class_exists('THWEC_Export')):
 
class THWEC_Export {
	/**
	 * Main instance of the class
	 *
	 * @access   protected
	 * @var      $_instance    
	 */
	protected static $_instance = null;

	/**
	 * Manages the list of all templates
	 *
	 * @access   private
	 * @var      $templates    Templates list
	 */
	private $templates = null;

	/**
	 * Manages the file name of the file to be exported
	 *
	 * @access   private
	 * @var      $filename    file name of exported file
	 */
	private $filename = '';

	/**
	 * Manages the status of the export function
	 *
	 * @access   private
	 * @var      $export_status    status of the export function
	 */
	private $export_status = '';

	/**
	 * Manages the list of templates that doesn't have template file in templates folder
	 *
	 * @access   private
	 * @var      $missing_templates   missing templates list
	 */
	private $missing_templates = array();

	/**
	 * Manages the selected templates to be exported
	 *
	 * @access   private
	 * @var      $export_templates    templates to be exported
	 */
	private $export_templates = array();

	/**
	 * Manage the templates submenu url
	 *
	 * @access   private
	 * @var      $url    templates submenu url
	 */
	private $url = array();
	
	/**
	 * Construct
	 */
	public function __construct() {
		add_action( 'wp_ajax_thwec_do_export', array( $this,'thwec_export_settings' ) );
		add_action( 'admin_init', array( $this, 'download_export_file') );
		add_action( 'in_admin_header', array( $this, 'render_header') );
		$this->url = admin_url( 'admin.php?page=th_email_customizer_templates' );
		$this->init();
	}

	/**
	 * Main THWEC_Export Instance.
	 *
	 * Ensures only one instance of THWEC_Export is loaded or can be loaded.
	 *
	 * @since 2.1
	 * @static
	 * @return THWEC_Export Main instance
	 */
	public static function instance() {
		if(is_null(self::$_instance)){
			self::$_instance = new self();
		}
		return self::$_instance;
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
	 * Encode the settings
	 *
	 * @param string $settings settings to be encoded.
	 * @return string
	 */
	private function get_settings( $settings ){
		$settings = base64_encode(serialize($settings));
		return $settings;
	}

	/**
	 * verify nonce 
	 *
	 */
	public function init(){
		if( isset( $_POST['thwec_skip_generation'] ) ){
			check_admin_referer( 'thwec-skip-missing-files' );
		}
	}

	/**
	 * Initiate the exporting process of template settings
	 *
	 * @since  3.4.0
	 * @return void
	 */
	public function thwec_export_settings(){
		check_ajax_referer( 'thwec-settings-export', 'security' );

		$response = array();
		$settings = THWEC_Utils::get_template_settings();
		$missing_templates = THWEC_Utils::get_missing_tfiles();
		$export_settings = $this->copy_settings( $settings, $missing_templates );
		$export_settings = $this->get_settings( $export_settings );
		$this->create_zip_files( $export_settings );
		if( file_exists( $this->filename ) ){
			$query_args = array(
				'action'   => 'export',
				'process'   => 'thwec_download_settings',
				'nonce'    => wp_create_nonce( 'thwec-download' ),
			);
			$response['url'] = add_query_arg( $query_args, $this->url );
			$response['status'] = 'success';
			wp_send_json( $response );
		}else{
			wp_send_json(
				array( 'status' => 'error' )
			);
		}
	}
	
	/**
	 * Copy the required settings from database for exporting
	 *
	 * @since  3.4.0
	 * @param  array $settings default plugin settings
	 * @return array
	 */
	private function copy_settings( $settings, $missing ){
		$copy_wpml_map = false;
		$export_wpml_map = array();
		$export_settings = array();
		
		$subjects = isset($_POST['copy_subjects']) ? $this->validate_boolean($_POST['copy_subjects']) : false;
		$mapping = isset($_POST['copy_mapping']) ? $this->validate_boolean($_POST['copy_mapping']) : false;
		$settings_wpml_map = isset( $settings['thwec_wpml_map'] ) ? $settings['thwec_wpml_map'] : array();

		if( isset($_POST['templates']) && is_array($_POST['templates']) && !empty( $_POST['templates'] ) ){
			foreach ($_POST['templates'] as $key => $value) {
				$template = sanitize_text_field( $value );
				if( isset( $settings['templates'][$template]) ){
					$export_settings['templates'][$template] = $settings['templates'][$template];
					if( array_key_exists( $value, $settings_wpml_map ) && !array_key_exists( $value, $export_wpml_map ) ){
						$export_wpml_map[$value] = $settings_wpml_map[$value];
					}
				}
			}
			if( !empty( $export_wpml_map ) ){
				$export_settings['thwec_wpml_map'] = $export_wpml_map;
			}
		}else{
			if( is_array( $missing ) && !empty( $missing ) ){
				foreach ($missing as $mkey => $mvalue) {
					if( isset( $settings['templates'][$mkey] ) ){
						unset( $settings['templates'][$mkey] );
					}
				}
			}
			$export_settings['templates'] = isset( $settings['templates'] ) ? $settings['templates'] : '';
			$export_settings['thwec_wpml_map'] = isset( $settings['thwec_wpml_map'] ) ? $settings['thwec_wpml_map'] : '';
		}

		if( $subjects ){
			$export_settings['email_subject'] = isset( $settings['email_subject'] ) ? $settings['email_subject'] : '';
		}

		if( $mapping ){
			$export_settings['template_map'] = isset( $settings['template_map'] ) ? $settings['template_map'] : '';
		}

		$this->export_templates = array_keys( $export_settings['templates'] );
		return $export_settings;
	}

	/**
	 * Creates ZIP file with the settings and template
	 *
	 * @since  3.4.0
	 * @param  array $export_settings required settings copied from database for export
	 * @return void
	*/
	public function create_zip_files( $export_settings ){
		if( !empty( $this->export_templates ) ){
			$zip = new ZipArchive();
			$zipname = THWEC_CUSTOM_TEMPLATE_PATH."thwec-settings.zip";	
			$created = $zip->open($zipname, ZipArchive::CREATE);
			if ($created) {
				if( $zip->addEmptyDir('templates') ) {
					foreach ( $this->export_templates as $file) {
						$filename = $file.'.php';
						$path = THWEC_CUSTOM_TEMPLATE_PATH.$filename;
						if( file_exists( $path ) ){
							$zip->addFile( $path,  'templates/'.$filename);  
						}
					}
				}
				if( $export_settings ){
					$zip->addFromString( 'settings.txt', $export_settings );
				}
				$closed = $zip->close();
				$this->filename = $zipname;
			}
		}
	}

	/**
	 * Set the export headers and download the generated file.
	 *
	 * @since  3.4.0
	 *
	*/
	public function download_export_file(){
		if ( isset( $_GET['action'], $_GET['nonce'] ) && wp_verify_nonce( wp_unslash( $_GET['nonce'] ), 'thwec-download' ) && 'thwec_download_settings' === wp_unslash( $_GET['process'] ) ) {  
			$file = $this->filename;
			$upload_dir = wp_upload_dir();
		    $file = $upload_dir['basedir'].'/thwec_templates/thwec-settings.zip';
			if (headers_sent()) {
			    echo 'HTTP header already sent';
			} else {
			    if (!is_file($file)) {
			        header($_SERVER['SERVER_PROTOCOL'].' 404 Not Found');
			        $this->export_status = 'File not found';
			    } else if (!is_readable($file)) {
			        header($_SERVER['SERVER_PROTOCOL'].' 403 Forbidden');
			        $this->export_status = 'File not readable';
			    } else {
			        header($_SERVER['SERVER_PROTOCOL'].' 200 OK');
			        header("Content-Type: application/zip");
			        header("Content-Transfer-Encoding: Binary");
			        header("Content-Length: ".filesize($file));
			        header("Content-Disposition: attachment; filename=\"".basename($file)."\"");
					header( 'Pragma: no-cache' );
					header( 'Expires: 0' );
			        ob_clean();
					flush();
					readfile($file);
					unlink($file);
					die();
			    }
			}
		}
	}

}

endif;