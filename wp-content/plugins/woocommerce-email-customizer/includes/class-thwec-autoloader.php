<?php
/**
 * Auto-loads the required dependencies for this plugin.
 *
 * @link       https://themehigh.com
 * @since      1.0.0
 *
 * @package    woocommerce-email-customizer-pro
 * @subpackage woocommerce-email-customizer-pro/includes
 */
if(!defined('WPINC')){	die; }

if(!class_exists('THWEC_Autoloader')):

class THWEC_Autoloader {
	private $include_path = '';

	private $class_path = array(
		'thwec_import' 				=> 'includes/migrate/class-thwec-import.php',
		'thwec_export' 				=> 'includes/migrate/class-thwec-export.php',
		'thwec_views' 				=> 'includes/views/class-thwec-views.php',
	);
	
	/**
	 * Construct
	 */
	public function __construct() {
		$this->include_path = untrailingslashit(THWEC_PATH);
		
		if(function_exists("__autoload")){
			spl_autoload_register("__autoload");
		}
		spl_autoload_register(array($this, 'autoload'));
	}

	/**
	 * load file
	 *
	 * @param string $path file path to be loaded
	 * @return boolean file loaded or not
	 */
	private function load_file( $path ) {
		if ( $path && is_readable( $path ) ) {
			require_once( $path );
			return true;
		}
		return false;
	}
	
	/**
	 * Get file name from class name
	 *
	 * @param string $class class name
	 * @return string file name
	 */
	private function get_file_name_from_class( $class ) {
		return 'class-' . str_replace( '_', '-', $class ) . '.php';
	}
	
	/**
	 * Load files
	 *
	 * @param string $class class name
	 */
	public function autoload( $class ) {
		$class = strtolower( $class );
		$file  = $this->get_file_name_from_class( $class );
		$path  = '';
		$file_path  = '';

		if (isset($this->class_path[$class])){
			$file_path = $this->include_path . '/' . $this->class_path[$class];
		} else {
			if (strpos($class, 'thwec_admin') === 0){
				$path = $this->include_path . '/admin/';
			} elseif (strpos($class, 'thwec_public') === 0){
				$path = $this->include_path . '/public/';
			} elseif (strpos($class, 'thwec_utils') === 0){
				$path = $this->include_path . '/includes/utils/';
			} elseif (strpos($class, 'thwec_builder') === 0){
				$path = $this->include_path . '/includes/modules/';
			} else{
				$path = $this->include_path . '/includes/';
			}
			$file_path = $path . $file;
		}
		
		if( empty($file_path) || (!$this->load_file($file_path) && strpos($class, 'thwec_') === 0) ) {
			$this->load_file( $this->include_path . $file );
		}
	}
}

endif;

new THWEC_Autoloader();
