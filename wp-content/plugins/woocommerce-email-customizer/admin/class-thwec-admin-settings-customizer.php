<?php
/**
 * The admin customizer page of the plugin
 *
 * @link       https://themehigh.com
 * @since      3.4.0
 *
 * @package    woocommerce-email-customizer-pro
 * @subpackage woocommerce-email-customizer-pro/admin
 */
if(!defined('WPINC')){	die; }

if(!class_exists('THWEC_Admin_Settings_Customizer')):

class THWEC_Admin_Settings_Customizer {
    /**
     * Main instance of the class
     *
     * @access   protected
     * @var      $_instance    
     */
	protected static $_instance = null;

    /**
     * Manages the status of YITH gift card plugin
     *
     * @access   private
     * @var      $ywgc_active   YITH gift card plugin active or not
     */
    private $ywgc_active = false;
	
    /**
    * Construct
    */
	public function __construct() {
        $this->init_constants();
	}

    /**
     * Main THWEC_Admin_Settings_Customizer Instance.
     *
     * Ensures only one instance of THWEC_Admin_Settings_Customizer is loaded or can be loaded.
     *
     * @since 3.4
     * @static
     * @return THWEC_Admin_Settings_Customizer Main instance
     */
	public static function instance() {
		if(is_null(self::$_instance)){
			self::$_instance = new self();
		}
		return self::$_instance;
	}

    /**
     * Initialize variables required
     */
    public function init_constants(){
        $this->ywgc_active = in_array( 'yith-woocommerce-gift-cards-premium', THWEC_Utils::compatible_plugins() );
    }

    /**
     * Render the customizer content
     *
     */
    public function render_page(){
        ?>
        <div id="thwec_wrapper" class="thwec-tbuilder-wrapper">
            <div id="thwec_ajax_load_modal"></div>
            <?php $this->render_builder(); ?>
        </div>      
        <?php 
    }

    /**
     * Render the email builder
     *
     */
    private function render_builder(){
        $this->render_template_builder_css_section('thwec_template_css');
        ?>
        <div id="render_builder">
            <script type="text/javascript">
                jQuery(document).ready(function($){
                    if( thwec_var.page_id == 'th_email_customizer_pro' ){
                        var data = {
                            action : 'thwec_initialize_builder',
                            security : thwec_var.initialize_builder
                        }
                        $.ajax({
                            type: 'POST',
                            url: ajaxurl,                        
                            data: data,
                            success:function(data){
                            }
                        });
                    }
                });
            </script>
        </div>
        <?php
    }

    /**
     * Render builder content styles
     */
    private function render_template_builder_css_section($wrapper_id) {
        ?>
        <style id="<?php echo $wrapper_id; ?>_layouts" type="text/css">
            .thwec-block-one-column >tbody > tr > td{
                width: 100%;                
            }

            .thwec-block-two-column >tbody > tr > td{
                width: 50%;             
            }

            .thwec-block-three-column >tbody > tr > td{
                width: 33%;             
            }

            .thwec-block-four-column >tbody > tr > td{
                width: 25%;             
            }
        </style>

        <style id="<?php echo $wrapper_id; ?>" type="text/css">
            .main-builder{
                max-width:600px;
                width:600px;
                margin: auto; 
                box-sizing: border-box;
            }

            <?php if( apply_filters( 'thwec_enable_global_link_color', true ) ){ ?>
                #tb_temp_builder.main-builder  a.thwec-link,
                #tb_temp_builder.main-builder  .thwec-block-text a,
                #tb_temp_builder.main-builder .thwec-block-billing a,
                #tb_temp_builder.main-builder .thwec-block-shipping a,
                #tb_temp_builder.main-builder .thwec-block-customer a{
                    color: <?php echo THWEC_Utils::get_template_global_css('link-color'); ?>;
                    text-decoration: <?php echo THWEC_Utils::get_template_global_css('link-decoration'); ?>;
                }
            <?php } ?>

            #tb_temp_builder .thwec-block-text b{
                font-weight: revert;
            }

            .main-builder .thwec-builder-column{
                background-color: #fff;
                background-size: 100%;
                background-position: center;
                background-repeat: no-repeat;
                border-top-width: 0px;
                border-right-width: 0px;
                border-bottom-width: 0px;
                border-left-width: 0px;
                border-style: none;
                border-color: transparent;
                vertical-align: top;
            }

            .thwec-row{
                border-spacing: 0px;
                padding-top: 0px;
                padding-bottom: 0px;
                padding-right: 0px;
                padding-left: 0px;
                border-top-width: 0px;
                border-right-width: 0px;
                border-bottom-width: 0px;
                border-left-width: 0px;
                border-style: none;
                border-color: transparent;
            }

            .thwec-row,
            .thwec-block{
                width:100%;
                table-layout: fixed;
            }

            .thwec-block td{
                padding: 0;
            }

            .thwec-layout-block{
                overflow: hidden;
            }

            .thwec-row td{
                vertical-align: top;
                box-sizing: border-box;
            }

            .thwec-block-one-column,
            .thwec-block-two-column,
            .thwec-block-three-column,
            .thwec-block-four-column{
                max-width: 100%;
                margin: 0 auto;
                margin-top: 0px;
                margin-right: auto;
                margin-bottom: 0px;
                margin-left: auto;
                background-size: 100%;
                background-repeat: no-repeat;
                background-position: center;
                border-top-width: 1px;
                border-right-width: 1px;
                border-bottom-width: 1px;
                border-left-width: 1px;
                border-style: dotted;
                border-color: #dddddd;
                padding-top: 12px;
                padding-right: 10px;
                padding-bottom: 12px;
                padding-left: 10px;
            }

            .thwec-row .thwec-columns{
                border-top-width: 1px;
                border-right-width: 1px;
                border-bottom-width: 1px;
                border-left-width: 1px;
                border-style: dotted;
                border-color: #dddddd;
                word-break: break-word;
                padding: 10px 10px;
                text-align: center;
                background-position: center;
                background-repeat: no-repeat;
                background-size: 100%;
            }
            
            .thwec-block-gallery-column td{
                width: 30%;
            }
            
            .thwec-block-header{
                overflow: hidden;
                text-align: center;
                box-sizing: border-box;
                position: relative;
                width:100%;
                margin:0 auto;
                max-width: 100%;
                background-size: 100%;
                background-repeat: no-repeat;
                background-position: center;
                background-color:#0099ff;
                border-top-width: 0px;
                border-right-width: 0px;
                border-bottom-width: 0px;
                border-left-width: 0px;
                border-style: none;
                border-color: transparent;
            }
            
            .thwec-block-header .header-logo{
                text-align: center;
                font-size: 0;
                line-height: 1;
                padding: 15px 5px 15px 5px;
            }

            .thwec-block-header .header-logo-ph{
                width:155px;
                height: 103px;
                margin:0 auto;
                border-top-width: 0px;
                border-right-width: 0px;
                border-bottom-width: 0px;
                border-left-width: 0px;
                border-style: none;
                border-color: transparent;
                display: inline-block;
            }

            .thwec-block-header .header-logo-ph img{
                width:100%;
                height:100%;
                display: block;
            }

            .thwec-block-header .header-text{
                padding: 30px 0px 30px 0px; 
                font-size: 0;
            }

            .thwec-block-header .header-text h1{
                margin:0 auto;
                width: 100%;
                max-width: 100%;
                color:#ffffff;
                font-size:40px;
                font-weight:300;
                mso-line-height-rule: exactly;
                line-height:100%;
                vertical-align: middle;
                text-align:center;
                font-family: Georgia, serif;
                border:1px solid transparent;
                box-sizing: border-box; 
            }

            .thwec-block-header .header-text h3{
                padding:0px;
                margin:0;
                color:#ffffff;
                font-size:22px;
                font-weight:300;
                text-align:center;
                font-family: times;
                line-height:150%;       
            }

            .thwec-block-header .header-text p{
                margin:0 auto;
                width: 100%;
                max-width: 100%;
                color:#ffffff;
                font-size:40px;
                font-weight:300;
                mso-line-height-rule: exactly;
                line-height:150%;
                text-align:center;
                font-family: Georgia, serif;
                border:1px solid transparent;
                box-sizing: border-box; 
            }

            .thwec-block-divider{
                margin: 0;
            }

            .thwec-block-divider td{
                padding: 20px 0px;
                text-align: center;
            }

            .thwec-block-divider hr{
                display: inline-block;
                border:none;
                border-top: 2px solid transparent;
                border-color: gray;
                width:70%;
                height: 2px;
                margin: 0;
            }

            .thwec-block-text{
                width: 100%;
                color: #636363;
                font-family: "Helvetica Neue",Helvetica,Roboto,Arial,sans-serif;
                font-size: 13px;
                line-height: 22px;
                text-align:center;
                margin: 0 auto;
                box-sizing: border-box;
            }

            .thwec-block-text .thwec-block-text-holder{
                color: #636363;
                font-family: "Helvetica Neue",Helvetica,Roboto,Arial,sans-serif;
                font-size: 13px;
                line-height: 22px;
                text-align: center;
                padding: 15px 15px;
                background-color: transparent;
                background-size: 100%;
                background-repeat: no-repeat;
                background-position: center;
                border-top-width: 0px;
                border-right-width: 0px;
                border-bottom-width: 0px;
                border-left-width: 0px;
                border-color: transparent;
                border-style: none;
            }

            .thwec-block-text .thwec-block-text-holder p.thwec-text-line{
                margin: 0 0 16px;
            }

            .thwec-block-image{
                width: auto;
                height: auto;
                max-width: 600px;
                box-sizing: border-box;
                width: 100%;
            }

            .thwec-block-image td.thwec-image-column{
                text-align: center;
            }

            .thwec-block-image p{
                padding: 0;
                margin: 0;
                width: 50%;
                padding: 5px 5px;
                display: inline-block;
                max-width: 100%;
                vertical-align: top;
                border-top-width: 0px;
                border-right-width: 0px;
                border-bottom-width: 0px;
                border-left-width: 0px;
                border-style: none;
                border-color: transparent;
            }

            .thwec-block-image img {
                width: 100%;
                height: auto;
                display: block;
            }

            .thwec-block-shipping .shipping-padding,
            .thwec-block-billing .billing-padding,
            .thwec-block-customer .customer-padding{
                padding: 5px 0px 2px 0px;
            }

            .thwec-block-billing,
            .thwec-block-shipping,
            .thwec-block-customer,
            .thwec-block-shipping .thwec-address-alignment,
            .thwec-block-billing .thwec-address-alignment,
            .thwec-block-customer .thwec-address-alignment{
                margin: 0;
                padding:0;
                border: 0px none transparent;
                border-collapse: collapse;
                box-sizing: border-box;
            }

            .thwec-block-billing .thwec-address-wrapper-table,
            .thwec-block-shipping .thwec-address-wrapper-table,
            .thwec-block-customer .thwec-address-wrapper-table{
                width:100%;
                height: 115px;
                background-repeat: no-repeat;
                background-size: 100%;
                background-color: transparent;
                background-position: center;
                border-top-width: 0px;
                border-right-width: 0px;
                border-bottom-width: 0px;
                border-left-width: 0px;
                border-style: none;
                border-color: transparent;
            }

            .thwec-block-customer .thwec-customer-header,
            .thwec-block-billing .thwec-billing-header,
            .thwec-block-shipping .thwec-shipping-header {
                color:#0099ff;
                display:block;
                font-family:"Helvetica Neue",Helvetica,Roboto,Arial,sans-serif;
                font-size:18px;
                font-weight:bold;
                line-height:100%;
                text-align:center;
                margin: 0px;
            }

            .thwec-block-customer .thwec-customer-body,
            .thwec-block-billing .thwec-billing-body,
            .thwec-block-shipping .thwec-shipping-body {
                margin: 0;
                text-align:center;
                line-height:150%;
                border:0px !important;
                font-family: 'Helvetica Neue',Helvetica,Roboto,Arial,sans-serif;
                font-size: 13px;
                padding: 0px 0px 0px 0px;
                color: #444444;
                margin: 13px 0px;
            }

            .thwec-block-social{
                text-align: center;
                width:100%;
                box-sizing: border-box;
                background-size: 100%;
                background-repeat: no-repeat;
                background-position: center;
                background-color: transparent;
                margin: 0 auto;
                border-top-width: 0px;
                border-right-width: 0px;
                border-bottom-width: 0px;
                border-left-width: 0px;
                border-style: none;
                border-color: transparent;
            }

            .thwec-block-social .thwec-social-outer-td{
                padding-top: 0px;
                padding-right: 0px;
                padding-bottom: 0px;
                padding-left: 0px;
            }

            .thwec-block-social .thwec-social-td{
                padding: 15px 3px 15px 3px;
                font-size: 0;
                line-height: 1px;
            }

            .thwec-block-social .thwec-social-icon{
                width: 40px;
                height: 40px;
                margin: 0px;
                text-decoration:none;
                box-shadow:none;
            }
    
            .thwec-block-social .thwec-social-icon img {
                width: 100%;
                height: 100%;
                display:block;
            }

            .thwec-button-wrapper-table{
                width: 80px;
                margin: 0 auto;
                padding-top: 10px;
                padding-right: 0px;
                padding-bottom: 10px;
                padding-left: 0px;
            }

            .thwec-button-wrapper-table td{
                border-radius: 2px;
                background-color: #4169e1;
                text-align: center;
                padding: 10px 0px;
                border-top-width: 1px;
                border-right-width: 1px;
                border-bottom-width: 1px;
                border-left-width: 1px;
                border-style: solid;
                border-color: #4169e1;
                text-decoration: none;
                color: #fff;
                font-size: 13px;
            }

            .thwec-button-wrapper-table td a.thwec-button-link{
                color: #fff;
                line-height: 150%;
                font-size: 13px;
                text-decoration: none;
                font-family: 'Helvetica Neue',Helvetica,Roboto,Arial,sans-serif;
            }

            .thwec-block-gif{
                margin: 0;
                width: 100%;
                height: auto;
                max-width: 600px;
                box-sizing: border-box;
            }

            .thwec-block-gif td.thwec-gif-column{
                text-align: center;
            }

            .thwec-block-gif td.thwec-gif-column p{
                margin: 0;
                width: 50%;
                padding: 10px 10px;
                display: inline-block;
                vertical-align: top;
                border-top-width: 0px;
                border-right-width: 0px;
                border-bottom-width: 0px;
                border-left-width: 0px;
                border-style: none;
                border-color: transparent;
            }

            .thwec-block-gif td.thwec-gif-column img {
                width:100%;
                height:auto;
                display:block;
            }

            .thwec-block-custom-hook{
                /*margin: 0;*/
                /*line-height: 0;*/
            }

            .thwec-block-order{
                background-color: white;
                margin: 0 auto;
                position: relative;
                background-size: 100%;
                background-repeat: no-repeat;
                background-position: center;
                border-top-width: 0px;
                border-right-width: 0px;
                border-bottom-width: 0px;
                border-left-width: 0px;
                border-color: transparent;
                border-style: none;
            }

            .thwec-block-order td{
                word-break: unset;
            }

            .thwec-block-order .order-padding {
                padding:20px 48px;
            }

            .thwec-block-order .thwec-order-heading {
                font-size:18px;
                text-align:left;
                line-height:100%;
                color: #4286f4;
                font-family: 'Helvetica Neue',Helvetica,Roboto,Arial,sans-serif;
            }

            .thwec-block-order .thwec-order-table {
                table-layout: fixed;
                background-color: #ffffff;
                /*margin:auto;*/
                width:100%;
                font-family: "Helvetica Neue",Helvetica,Roboto,Arial,sans-serif;
                color: #636363;
                border: 1px solid #e5e5e5;
                border-collapse:collapse;
            }
            .thwec-block-order .thwec-td {
                color: #636363;
                border: 1px solid #e5e5e5;
                padding:12px;
                text-align: left;
                font-size: 14px;
                line-height: 150%;
            }

            <?php if( apply_filters( 'thwec_order_table_column_auto_width', true ) ) : ?>
                .thwec-order-table td,
                .thwec-order-table th{
                    word-break: keep-all;
                }

                .thwec-block-order .thwec-order-table {
                    table-layout: auto;
                }
            <?php endif; ?>

            .thwec-block-order .thwec-order-item-img{
                margin-bottom: 5px;
            }
            .thwec-block-order .thwec-order-item-img img{
                width: 32px;
                height: 32px;
                display: inline;
                height: auto;
                outline: none;
                line-height: 100%;
                vertical-align: middle;
                margin-right: 10px;
                text-decoration: none;
                text-transform: capitalize;
            }

            .thwec-block-gap{
                height:48px;
                margin: 0;
                box-sizing: border-box;
                background-size: 100%;
                background-color: transparent;
                background-repeat: no-repeat;
                background-position: center;
                border-top-width: 0px;
                border-right-width: 0px;
                border-bottom-width: 0px;
                border-left-width: 0px;
                border-style: none;
                border-color: transparent;
            }

            .thwec-block-one-column .thwec-block-image.thwec-default-placeholder p,
            .thwec-block-one-column .thwec-block-gif.thwec-default-placeholder p{
                width: 10% !important;
            }

            .thwec-block-two-column .thwec-block-image.thwec-default-placeholder p,
            .thwec-block-two-column .thwec-block-gif.thwec-default-placeholder p{
                width: 21% !important;
            }

            .thwec-block-three-column .thwec-block-image.thwec-default-placeholder p,
            .thwec-block-three-column .thwec-block-gif.thwec-default-placeholder p{
                width: 32% !important;
            }

            .thwec-block-four-column .thwec-block-image.thwec-default-placeholder p,
            .thwec-block-four-column .thwec-block-gif.thwec-default-placeholder p{
                width: 45% !important;
            }

            .thwec-short-description{
                margin-top: 10px;
                font-size: 12px;
            }
                        
            <?php if( $this->ywgc_active ){ ?>
            
                .thwec-block-ywgc-header .ywgc-logo,
                .thwec-block-ywgc-header .ywgc-image{
                    padding: 12px;
                }

                .thwec-block-ywgc-header .ywgc-logo .ywgc-logo-shop-image{
                    border:none;
                    display:inline-block;
                    font-size:14px;
                    font-weight:bold;
                    height:auto;
                    outline:none;
                    text-decoration:none;
                    text-transform:capitalize;
                    vertical-align:middle;
                    margin-right:10px;
                    max-width:100%;
                    width: auto;
                    height: 100px;
                }

                .thwec-block-ywgc-header .ywgc-image .ywgc-main-image{
                    width: 520px;
                    max-width: 100%;
                    margin: 0 auto;
                    display: block !important;
                    vertical-align: middle;
                    font-size: 14px;
                    height: auto;
                }

                .thwec-block-ywgc-name-price,
                .thwec-block-ywgc-message,
                .thwec-block-ywgc-code,
                .thwec-block-ywgc-expiry,
                .thwec-block-ywgc-description{
                    width: 100%;
                    min-width: 100%;
                    border: none;
                    padding: 0;
                    margin: 0;
                    table-layout: auto;
                    border-top-width: 0px;
                    border-right-width: 0px;
                    border-bottom-width: 0px;
                    border-left-width: 0px;
                    border-color: transparent;
                    height: auto;
                    word-break: keep-all;
                    position: relative;
                }

                .thwec-block-ywgc-name-price .ywgc-card-product-name{
                    text-align:left;
                    font-weight:bold;
                    font-size:24px;
                    padding:12px;
                    font-family: "Helvetica Neue",Helvetica,Roboto,Arial,sans-serif;
                    line-height: 150%;
                    color: #636363;
                }

                .thwec-block-ywgc-name-price .ywgc-card-amount{
                    width:100px;
                    text-align:left;
                    font-weight:bold;
                    font-size:24px;
                    padding:12px;
                    font-family: "Helvetica Neue",Helvetica,Roboto,Arial,sans-serif;
                    line-height: 150%;
                    color: #636363;
                }

                .thwec-block-ywgc-message .ywgc-card-message{
                    text-align:left;
                    font-weight:bold;
                    padding:12px;
                    font-family: "Helvetica Neue",Helvetica,Roboto,Arial,sans-serif;
                    line-height: 150%;
                    color: #636363;
                    font-size: 14px;
                }

                .thwec-block-ywgc-code .ywgc-card-code-column{
                    padding:12px;
                    text-align: left;
                    vertical-align: middle;
                }

                .thwec-block-ywgc-code .ywgc-card-code-column .ywgc-card-code-title{
                    font-weight:bold;
                    font-size:18px;
                    text-align:left;
                    color: #636363;
                    line-height: 150%;
                    font-family: "Helvetica Neue",Helvetica,Roboto,Arial,sans-serif;
                }

                .thwec-block-ywgc-code .ywgc-card-code-column .ywgc-card-code{
                    color: #808080;
                    font-weight:bold;
                    font-size:18px;
                    text-align:left;
                    line-height: 150%;
                    font-family: 'Helvetica Neue',Helvetica,Roboto,Arial,sans-serif;
                }

                .thwec-block-ywgc-code .ywgc-card-qr-code{
                    text-align: left;
                    padding: 12px;
                    vertical-align: middle;
                }

                .thwec-block-ywgc-code .ywgc-card-qr-code .ywgc-card-qr-code-image{
                    border:none;
                    display:inline-block;
                    font-size:14px;
                    font-weight:bold;
                    height:auto;
                    outline:none;
                    text-decoration:none;
                    text-transform:capitalize;
                    vertical-align:middle;
                    margin-right:10px;
                    max-width:100%;
                    width: 100px;
                    height: 100px;
                }

                .thwec-block-ywgc-expiry .ywgc-expiration-message{
                    text-align: center;
                    color: #ff0000;
                    padding: 12px;
                    font-size: 14px;
                    line-height: 150%;
                    font-family: 'Helvetica Neue',Helvetica,Roboto,Arial,sans-serif;
                }

                .thwec-block-ywgc-description .ywgc-card-description{
                    text-align: center;
                    padding: 12px;
                    color: #636363;
                    font-size: 14px;
                    line-height: 150%;
                    font-family: 'Helvetica Neue',Helvetica,Roboto,Arial,sans-serif;
                }

                .thwec-ygc-button-wrapper-table{
                    width: 232px;
                    margin: 0 auto;
                    padding-top: 10px;
                    padding-right: 0px;
                    padding-bottom: 10px;
                    padding-left: 0px;
                }

                .thwec-ygc-button-wrapper-table td{
                    background-color:#557da1;
                    text-align: center;
                    padding: 8px 15px;
                    border-top-width: 1px;
                    border-right-width: 1px;
                    border-bottom-width: 1px;
                    border-left-width: 1px;
                    border-style: solid;
                    border-color: #557da1;
                    color: #fff;
                    font-size: 13px;
                    border-radius: 5px;
                    background-size: 100%;
                    background-repeat: no-repeat;
                    background-position: center;
                }

                .thwec-ygc-button-wrapper-table td a.thwec-ygc-button-link{
                    color: #ffffff;
                    line-height: 150%;
                    font-size: 13px;
                    text-decoration: none;
                    font-family: 'Helvetica Neue',Helvetica,Roboto,Arial,sans-serif;
                    font-weight: 700;
                    font-size:13px;
                    text-decoration:none;
                }
            <?php } ?>

        </style>
        <?php
    } 

}

endif;