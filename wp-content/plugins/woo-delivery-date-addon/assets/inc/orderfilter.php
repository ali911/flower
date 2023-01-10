<?php
////////////////////////////////////////////////////////////////////////////////

add_filter('manage_edit-shop_order_columns', 'misha_order_items_column' );
function misha_order_items_column( $order_columns ) {

return array_slice( $order_columns, 0, 4, true )
+ array( 'order_products' => 'Delivery Date' )
+ array( 'order_shipping_date' => 'Shipping Date' )
+ array_slice( $order_columns, 4, NULL, true );

// $order_columns['order_products'] = "Delivery Date ";
// $order_columns['order_shipping_date'] = "Shipping Date ";
// return $order_columns;
}

////////////////////////////////////////////////////////////////////////////////

add_action( 'manage_shop_order_posts_custom_column' , 'misha_order_items_column_cnt' );
function misha_order_items_column_cnt( $colname ) {
global $woocommerce, $post;

    $order = new WC_Order($post->ID);
    $get_shipping_method = $order->get_shipping_method();

    global $the_order; // the global order object
    $get_delry_date =  get_post_meta( $post->ID, 'ctm_date_picker', true );
    
    if( $colname == 'order_products' ) {
            if ($get_delry_date) {
            echo $get_delry_date;
            }
    }

    if ($colname == 'order_shipping_date') {
        if ($get_shipping_method === "Flat rate") {
            echo $shipping_date_ = $get_delry_date;
        } else {
            $shipping_date_ = explode("/", $get_delry_date);
            $from_unix_time = mktime(0, 0, 0, $shipping_date_[0], $shipping_date_[1], $shipping_date_[2]);
            $day_before = strtotime("yesterday", $from_unix_time);
            echo $shipping_date_ = date('m/d/Y', $day_before);
        }
    }
}

 add_action('pre_get_posts', 'filter_woocommerce_orders_in_the_table', 99, 1);
function filter_woocommerce_orders_in_the_table($query){
	if (!is_admin()) {
		return;
	}
	global $pagenow;
	if ('edit.php' === $pagenow && 'shop_order' === $query->query['post_type'] && isset( $_GET['date_from'] )
        && $_GET['date_from'] != ''&& isset( $_GET['date_to'] )
        && $_GET['date_to'] != '') {
		 $query->set( 'post_type', 'shop_order' );
		 $startDate  =   $_GET['date_from'];
        $endDate  =   $_GET['date_to'];
		
		if($_GET['date_type'] == 'shipping_date'){
             $shipping_date_ = explode("/", $startDate);
             $from_unix_time = mktime(0, 0, 0, $shipping_date_[0], $shipping_date_[1], $shipping_date_[2]);
             $day_before = strtotime("yesterday", $from_unix_time);
             $startDate = date('m/d/Y', $day_before);
             
                     
         }
       
		
		$meta_query =  array(
            'relation' => 'AND',
            array(
                'key' => 'ctm_date_picker',
                'value' => $startDate,
                'compare'   => '>=',
            ),
            array(
                'key' => 'ctm_date_picker',
                'value' => $endDate,
                'compare'   => '<=',
            )
        );

        $query->set( 'meta_query', $meta_query ); // Set the new "meta query"
        $query->set( 'posts_per_page', 10 ); // Set "posts per page"
        $query->set( 'paged', ( get_query_var('paged') ? get_query_var('paged') : 1 ) ); // Set "paged"
		
		
		

	}
}
////

add_action('restrict_manage_posts', 'show_is_first_order_checkbox');
function show_is_first_order_checkbox() {
	?>
		<style>
			.tablenav .actions select{
				float:none !important;
			}
		</style>
		<select name="date_type">
            <option value="delivery_date" >Delivery Date</option>
            <option value="shipping_date" >Shipping Date</option>
        </select>
		<label>
			<!-- Start Date: <input type="date" name="after_date" id="after_date"> -->
      <input type="text" autocomplete="off" placeholder="Enter Date" name="date_from"  id="start_datepicker"  />
		</label>

		<label>
			<!-- End Date: <input type="date" name="before_date" id="before_date"> -->
      <input type="text" autocomplete="off" placeholder="Enter Date" name="date_to"  id="end_datepicker"  />
		</label>
	<?php
}


add_action("admin_footer", "admin_ctm_js_func_for_st_end_date");

function admin_ctm_js_func_for_st_end_date(){ ?>
  <script type="text/javascript">
    jQuery(document).ready(function () {
      jQuery('#start_datepicker').datepicker({
        dateFormat: 'mm/dd/yy',
		onSelect: function (date) {
			//var dt = new Date(selected);
            //dt.setDate(dt.getDate() + 1);
            jQuery("#end_datepicker").datepicker("option", "minDate", date);
    	}  
      });
      jQuery('#end_datepicker').datepicker({
        dateFormat: 'mm/dd/yy',
		  onSelect: function (date) {
			   jQuery("#start_datepicker").datepicker("option", "maxDate", date);
		  }
      });
    }); // ready
  </script> 
<?php }
