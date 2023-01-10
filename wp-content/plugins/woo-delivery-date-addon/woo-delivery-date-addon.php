<?php
/**
 * Plugin Name: Woo Delivery Date Addon
 * Description: Woocoomerce Delivery Date Addon extend the functionality in order to add delivery date filter orders based on delivery and shipping date.
 * Version: 1.0
 */

 function ale_files_enqueue_script() {
  wp_enqueue_style( 'custm_bootstrap_css',  plugin_dir_url( __FILE__ ) . 'assets/css/style.css' );
//   wp_enqueue_style( 'ale_custom_lightness', 'https://code.jquery.com/ui/1.10.4/themes/ui-lightness/jquery-ui.css' );
  wp_enqueue_script( 'ale_custom_script', 'https://code.jquery.com/ui/1.10.4/jquery-ui.js', NULL, 1.0, true );

  $get_date = json_decode(get_option("get_date"));
//   echo join($get_date, ",");
  wp_localize_script( 'ale_custom_script', 'my_ajax_object', array('getfuture_date' => $get_date ) );

  }
add_action('wp_enqueue_scripts', 'ale_files_enqueue_script');

/////////////////////////////////////////////////////////////////////

add_action("admin_enqueue_scripts", function(){

    if( $_GET['page'] == 'date_settings'){
        //enqueue your scripts    
    wp_enqueue_style( 'custm_bootstrap_css',  plugin_dir_url( __FILE__ ) . 'assets/css/admin_style.css' );
    wp_enqueue_script( 'ale_custom_script_admin', 'https://code.jquery.com/ui/1.10.4/jquery-ui.js', NULL, 1.0, true );
    wp_enqueue_style( 'ale_custom_lightness_admin', 'https://code.jquery.com/ui/1.10.4/themes/ui-lightness/jquery-ui.css' );
    }
});


include(plugin_dir_path(__FILE__) . '/assets/inc/wcsingle.php');
include(plugin_dir_path(__FILE__) . '/assets/inc/ctmpopup.php');
include(plugin_dir_path(__FILE__) . '/assets/inc/orderfilter.php');
include(plugin_dir_path(__FILE__) . '/assets/inc/hidefutredate.php');
include(plugin_dir_path(__FILE__) . '/assets/inc/emaildelivery.php');
include(plugin_dir_path(__FILE__) . '/assets/inc/ctmcartpage.php');


///////////////////////////STYLE FOR ADMIN ORDER PAGE//////////////////////////////////////////

add_action("admin_head", function(){?>
  <style>
      th#order_products, #order_shipping_date {
          color: #2271b1;
          cursor: pointer;
      }
      #order_line_items table.display_meta {
          display: none;
      }
  </style>
  <?php });

  
//////////////////////////////////// // Display field value on the admin order edit page///////////////////////////////////////////

function cloudways_display_order_data_in_admin( $order ){  ?>
    <div class="order_data_column">
        <h4><?php _e( 'Update Delivery date', 'woocommerce' ); ?><a href="#" class="edit_address"><?php _e( 'Edit', 'woocommerce' ); ?></a></h4>
        <div class="address">
        <?php   echo '<p><strong>' . __( 'Delivery date' ) . '</strong>' . get_post_meta( $order->id, 'ctm_date_picker', true ) . '</p>'; ?>
        <?php 
        //   $shipping_date_ = explode("/", get_post_meta( $order->id, 'ctm_date_picker', true ));
        //   $from_unix_time = mktime(0, 0, 0, $shipping_date_[0], $shipping_date_[1], $shipping_date_[2]);
        //   $day_before = strtotime("yesterday", $from_unix_time);
        // $formatted = date('m/d/Y', $day_before);
        // $order = new WC_Order($order->id);
        $get_delry_date =  get_post_meta( $order->id, 'ctm_date_picker', true );
        $get_shipping_method = $order->get_shipping_method();

        if ($get_shipping_method === "Flat rate") {
             $get_shipping_date_ = $get_delry_date;
        } else {
            $shipping_date_ = explode("/", $get_delry_date);
            $from_unix_time = mktime(0, 0, 0, $shipping_date_[0], $shipping_date_[1], $shipping_date_[2]);
            $day_before = strtotime("yesterday", $from_unix_time);
            $get_shipping_date_ = date('m/d/Y', $day_before);
        }

         echo '<p><strong>' . __( 'Shipping Date' ) . '</strong>' . $get_shipping_date_  . '</p>'; ?> 
        </div>

        <div class="edit_address">
            <input type="text" autocomplete="off" placeholder="Enter Date" name="ctm_date_picker"  id="admin_datepicker" value="<?php echo  get_post_meta( $order->id, 'ctm_date_picker', true ); ?>" />
        </div>
    </div>
<?php }
add_action( 'woocommerce_admin_order_data_after_order_details', 'cloudways_display_order_data_in_admin' );

function cloudways_save_extra_details( $post_id, $post ){
    update_post_meta( $post_id, 'ctm_date_picker', wc_clean( $_POST[ 'ctm_date_picker' ] ) );
    // update_post_meta( $post_id, '_cloudways_dropdown', wc_clean( $_POST[ '_cloudways_dropdown' ] ) );
}
add_action( 'woocommerce_process_shop_order_meta', 'cloudways_save_extra_details', 45, 2 );


///////////////////////////////////////ADMIN JS CODE ST //////////////////////////////////

add_action("admin_footer", "admin_ctm_js_func");

function admin_ctm_js_func(){ ?>
<script type="text/javascript">
jQuery(document).ready(function () {
    jQuery("#order_products, #order_shipping_date").click(function(){
        jQuery("#order_date span").trigger("click");
    });
    ////
        jQuery('#admin_datepicker').datepicker({
        beforeShowDay: function(date){
        var day = date.getDay();
        return [ day > 1 && day < 6, ''];
        },
        minDate: "+14d",
        dateFormat: 'mm/dd/yy'
    });

}); // ready
</script>

<?php }