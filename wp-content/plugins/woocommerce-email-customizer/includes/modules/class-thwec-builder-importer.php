<?php
/**
 * The file contains functions to handle when a template is loaded to edit
 *
 * @link       https://themehigh.com
 * @since      3.4.0
 *
 * @package    woocommerce-email-customizer-pro
 * @subpackage woocommerce-email-customizer-pro/admin
 */
if(!defined('WPINC')){  die; }

if(!class_exists('THWEC_Builder_Importer')):

class THWEC_Builder_Importer {
    /**
     * Main instance of the class
     *
     * @access   protected
     * @var      $_instance    
     */
    protected static $_instance = null;

    /**
     * Manages the status of WPML plugin
     *
     * @access   private
     * @var      $wpml_active   WPML active or not
     */
    private $wpml_active = false;

    /**
     * Manages the default language configured in WPML
     *
     * @access   private
     * @var      $wpml_default_lang   default WPML language
     */
    private $wpml_default_lang = null;

    /**
     * Main THWEC_Builder_Importer Instance.
     *
     * Ensures only one instance of THWEC_Builder_Importer is loaded or can be loaded.
     *
     * @since 3.4
     * @static
     * @return THWEC_Builder_Importer Main instance
     */
    public static function instance() {
        if(is_null(self::$_instance)){
            self::$_instance = new self();
        }
        return self::$_instance;
    }

    /**
     * Construct
     */
    public function __construct() {
        $this->init_constants();
    }

    /**
     * Setup class variables
     */
    public function init_constants(){
        $this->wpml_active = THWEC_Utils::is_wpml_active();

        if ( $this->wpml_active) {
            $this->wpml_default_lang = THWEC_Utils::get_wpml_locale( apply_filters( 'wpml_default_language', NULL ) );
        }
    }

    /**
     * Prepare template data from the posted data while editing a saved template
     *
     * @param  array $posted $_POST data
     * @return array $import_data template meta data
     */ 
    public function prepare_template_data( $posted ){
        $import_data = array();
        $template_content = '';
        $template_type = '';
        $template_data = THWEC_Utils::get_template_settings();
        
        if( is_array( $posted ) ){
            if( isset( $posted['i_template_name'] ) ){// Editing a saved template
                $template_key = sanitize_text_field( $posted['i_template_name'] );
                $template_type = isset( $posted['i_template_type'] ) ? sanitize_text_field( $posted['i_template_type'] ) : 'user';

            }else if( isset( $posted['thwec_template_lang'] ) ){
                // When language is swtiched from builder
                $template_key = isset( $posted['template_format_name'] ) ? $posted['template_format_name'] : '';
                $wpml_lang = $this->wpml_default_lang == $posted['thwec_template_lang'] ? '' : $posted['thwec_template_lang'];
                if( $template_key && !empty( $wpml_lang ) ){
                    $template_key = $template_key.'-'.$wpml_lang;
                }
            }else{
                return;
            }

            if($template_type == 'sample'){
                $template_data = apply_filters('thwec_sample_templates', $template_data);
            }
            
            if( $template_type == 'sample' ){
               $import_data = array(
                    "type" => "sample",
                    "template_lang" => strtolower($this->wpml_default_lang),
                    "template_json" => isset( $template_data['thwec_samples'][$template_key]['template_data'] ) ? $template_data['thwec_samples'][$template_key]['template_data'] : ""
                );
            }else if( isset( $template_data['templates'][$template_key] ) ){
                // Editing a saved template (inlcudes translated templates)
                $import_data = $this->get_template_data( $template_data, $template_key, $posted );
            }else{
                //If language switcher in builder is changed the first time for a saved template or trying to edit a template not in the template list
                $wpml_map = isset( $template_data['thwec_wpml_map'] ) ? $template_data['thwec_wpml_map'] : array();
                $base_tname = str_replace( '-'.strtolower( $this->wpml_default_lang ), '', $template_key );
                if( isset( $posted['thwec_template_lang'] ) ){
                    $template_key = isset( $posted['template_format_name'] ) ? $posted['template_format_name'] : '';
                    $template_key_lang = $template_key.'-'.strtolower( $this->wpml_default_lang );
                    if( isset( $template_data['templates'][$template_key_lang] ) ){
                        //Load template from default language
                        $import_data = $this->get_template_data( $template_data, $template_key_lang, $posted, true );
                    }else if( isset( $template_data['templates'][$template_key] ) ){
                        //Load non-wpml template if any.
                        $import_data = $this->get_template_data( $template_data, $template_key, $posted, true );
                    }
                }else if( in_array( $base_tname, $wpml_map ) ){
                    //Reserved for create translation (when translation in def language is missing in Templates ) button in templates
                    $translations = array_keys( $wpml_map, $base_tname );
                    if( isset( $translations[0] ) && !empty( $translations[0] ) ){
                        $import_data = $this->get_template_data( $template_data, $translations[0], $posted, false, strtolower( $this->wpml_default_lang ) );
                    }
                }
            }
        }
        return $import_data;
    }

    /**
     * Get template data for the loaded template
     * 
     * @param  array $data selected template data from database
     * @param  string $key template key name of template selected
     * @param  array $posted $_POST data
     * @param  boolean $fresh_translation if new translation for the selected template (WPML)
     * @param  array $lang langauge of the template selected ( WPML )
     * @return array template meta data
     */
    public function get_template_data( $data, $key, $posted, $fresh_translation=false, $lang=false ){
        $display_name = isset( $data['templates'][$key]['display_name'] ) ? $data['templates'][$key]['display_name'] : '';
        $formated_name = isset( $posted['template_format_name'] ) ? sanitize_text_field( $posted['template_format_name'] ) : ( isset( $data['templates'][$key]['lang'] ) ? str_replace( '-'.$data['templates'][$key]['lang'], '', $key ) : '' );
        if( empty( $formated_name ) ){
            $formated_name = strtolower( str_replace(" ", "_", $data['templates'][$key]['display_name']) );
        }
        $add_css = isset( $data['templates'][$key]['additional_css'] ) ? $data['templates'][$key]['additional_css'] : "";
        if( $fresh_translation && isset( $posted['thwec_template_lang'] ) && !empty( $posted['thwec_template_lang'] ) ){
            $template_lang =  $posted['thwec_template_lang'];
        }else{
            $template_lang = isset( $data['templates'][$key]['lang'] ) ? $data['templates'][$key]['lang'] : '';
        }
        if( $lang ){
            $template_lang = $lang;
        }
        $template_json = isset( $data['templates'][$key]['template_data'] ) ? $data['templates'][$key]['template_data'] : "";
        $template_version = isset( $data['templates'][$key]['version'] ) ? $data['templates'][$key]['version'] : "";
        $template_details = array(
            "type" => "custom",
            "display_name" => $display_name,
            "add_css" => $add_css,
            "template_lang" => $template_lang,
            "template_json" => $template_json,
            "is_react_template" => $this->is_react_template( $data, $key),
            "template_key" =>  $this->get_template_key( $data, $key ),
            "created_in_free" => false,
            "remove_unencoded" => false,
        );

        if( isset( $data['templates'][$key]['plan'] ) && $data['templates'][$key]['plan'] === "free" ){
            $template_details["created_in_free"] = true;
            if( $template_version === '2.3.0' ){
                $template_details["remove_unencoded"] = true;
            }
        }else{
            if( $template_version === '3.5.0' ){
                $template_details["remove_unencoded"] = true;
            }
        }
        return $template_details;
    }

    private function is_react_template($data, $key){
        $template_version = isset( $data['templates'][$key]['version'] ) ? $data['templates'][$key]['version'] : "";
        $react_version = 
        $plan = isset( $data['templates'][$key]["plan"] ) ? $data['templates'][$key]["plan"] : false;
        $json = isset( $data['templates'][$key]["template_data"] ) ? json_decode( $data['templates'][$key]["template_data"] ) : false;

        if( $plan ){
            if( $plan === "free" || $json->contents ){
                return 1;
            }
            return 0;
        }else{
            return version_compare( $template_version, '3.5.0', '>=' ) ? 1 : 0;
        }
    }

     /**
     * Get template key for the loaded template
     * 
     * @param  boolean $data selected template data from database
     * @param  array $key template key name of template selected
     * @return array template meta data
     */
    private function get_template_key( $data, $key ){
        $language = isset($data['templates'][$key]['lang']) ? $data['templates'][$key]['lang'] : false; 
        $base = isset($data['templates'][$key]['base']) ? $data['templates'][$key]['base'] : false; 
        if( $language && $base ){
           return $base;
        }
        return $key;
    }
}

endif;