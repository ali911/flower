<?php

/////////////////////////////////////////////////////////////////////

//  add_action("woocommerce_before_add_to_cart_quantity", "add_custom_datepicker_fucntion");

function add_custom_datepicker_fucntion(){
    // echo $_COOKIE["ctm_date_picker_cookies"].'cookies';
	// echo '<p> <input type="hidden" autocomplete="off" placeholder="Enter Date" name="ctm_date_picker"  id="ctm_ppup_date_picker"></p>';
	// echo '<div class="qty2">Quantity </div>';
}
/////////////ADD CUSTOM META FROM SINGLE PRODUCT PAGE///////////////

 add_filter("woocommerce_add_cart_item_data", "wdm_add_item_data", 10, 3);

function wdm_add_item_data($cart_item_data, $product_id, $variation_id){
    $cart_item_data["updated_price_"] = $_POST["updated_price_"];
    return $cart_item_data;
}

/////////////////////////////////////// SHOW CUSTOM PRICE OF THE PRODUCT IN THE CART/////////////////////////////////////////////////

add_filter('woocommerce_cart_item_price', 'alie_wpd_show_regular_price_on_cart', 30, 3);
function alie_wpd_show_regular_price_on_cart($price, $values, $cart_item_key){
    
    $new_price =  $values['updated_price_'];
    return "$" .$new_price;
}
/////////////////////////////////////// UPDATE PRODUCT PRICE IN THE CART/////////////////////////////////////////////////

//  add_action( 'woocommerce_before_calculate_totals', 'misha_recalc_price' );

// function misha_recalc_price( $cart_object ) {
//     foreach ( $cart_object->get_cart() as $value ) {
//         $value[ 'data' ]->set_price( $value['updated_price_']);
//     }
// }

///////////// SAVE CUSTOM DATA INTO DATABASE AFTER ORDER PLACING ///////////////

add_action( 'woocommerce_checkout_create_order_line_item', 'wdm_add_custom_order_line_item_meta',10,4 );

function wdm_add_custom_order_line_item_meta($item, $cart_item_key, $values, $order){

    if(array_key_exists('updated_price_', $values)) {
        $item->add_meta_data('updated_price_', $values['updated_price_']);
    }
}

//////////////////////////////////Save the Data of Custom Checkout WooCommerce Fields/////////////////////////////////////

function cloudways_save_extra_checkout_fields( $order_id, $posted ){
     $get_date_from_ss  = $_COOKIE["ctm_date_picker_cookies"];
    if($get_date_from_ss) {
        update_post_meta( $order_id, 'ctm_date_picker', $get_date_from_ss );
    }
}
add_action( 'woocommerce_checkout_update_order_meta', 'cloudways_save_extra_checkout_fields', 10, 2 );

/////////////////////////////////////Display  the Data of  WooCommerce Custom Fields to User/////////////////////////////

function cloudways_display_order_data( $order_id ){
    // delete_transient('ctm_date_picker_cookies');
    unset($_COOKIE['ctm_date_picker_cookies']);

    ?>
    <h2><?php _e( 'Delivery Date' ); ?></h2>
    <table class="shop_table shop_table_responsive additional_info">
        <tbody>
            <tr>
                <th><?php _e( 'Delivery Date:' ); ?></th>
                <td><?php echo get_post_meta( $order_id, 'ctm_date_picker', true ); ?></td>
            </tr>
        </tbody>
    </table>
<?php }
add_action( 'woocommerce_thankyou', 'cloudways_display_order_data', 100 );
add_action( 'woocommerce_view_order', 'cloudways_display_order_data', 20 );

/////////////////////////////////// SINGLE PRODUCT PAGE CODE ST /////////////////////////////////////////////

add_action('woocommerce_after_add_to_cart_button', function(){
    global $product;
    $product_id = $product->get_id();

    echo '<input type="hidden" class="" id="productID" value="'.$product_id.'">';
    echo '<input type="hidden" class="" id="ctm_prodyct_Type" value="'. $product->get_type().'">';
    // $product->is_type( 'variable' );

    $product_price = floatval (trim($product->get_regular_price()));

    $get_terms = get_the_terms( $product_id, 'product_cat' );

$catId =  $get_terms[0]->term_id;

$category_surge_pricing = get_term_meta($catId, "category-surge-pricing", true);


 $product = wc_get_product($product_id); // If needed
$product_surge_pce =  get_post_meta($product_id, "surge-prices", true) ?  get_post_meta($product_id, "surge-prices", true) : $category_surge_pricing;
// echo $product_surge_pce.'asd';
$today = date("Y-m-d");

    if ($product_surge_pce ) {
        foreach ($product_surge_pce as $key => $value){ 
            $start_date =  $value['start-date'];
            $end_date =    $value['end-date'];
            // $price_surge = floatval($value['price-surge']);
            $price_type =  $value['price-type'];
            $price_surge =  array_key_exists('price-surge', $value) ? floatval($value['price-surge']) : floatval($value['price']);

        if ($today >=  $start_date && $today <= $end_date ) {
            if ($price_type === "Dollar Price") {
                    $updated_price =  $product_price + $price_surge;
                        echo '<input name="updated_price_" class="custom dlr_price" type="hidden" value='. $updated_price .' > ';
                        break;
                    } else{
                    // if ($price_type === "Percentage") {
                        $updated_per_price = round(($product_price * $price_surge / 100) + $product_price, 2);
                            echo '<input name="updated_price_" class="pertage_price" type="hidden" value='. $updated_per_price .' > ';
                            break;
                        // }
                    }
            } else {
                    echo '<input name="updated_price_" class="regu_price" type="hidden" value='. $product_price .' > '; 
                break;     
            }
        }
    } else {
        echo '<input name="updated_price_" class="regu_price" type="hidden" value='. $product_price .' > '; 
    }
});

/////////////////////////////////// SURGE PRICE BY DATEPICKER  //////////////////////////////////////////

add_action('wp_ajax_surge_price_by_datepicker_rage' , 'surge_price_by_datepicker_rage');
add_action('wp_ajax_nopriv_surge_price_by_datepicker_rage','surge_price_by_datepicker_rage');
function surge_price_by_datepicker_rage(){

    $proType = $_POST['proType'];
    $varproId = intval($_POST['varproId']);
    $product_id =  intval($_POST['productID']);

    if ($proType == "variable") {
        $_product = wc_get_product( $varproId );
    } else {
        $_product = wc_get_product( $product_id );
    }

    $product_price = $_product->get_regular_price();

    $datepickerDate =  $_POST['datepickerDate'];
    $rearbageDate = explode('/', $datepickerDate);
   
    
    
    $get_terms = get_the_terms( $product_id, 'product_cat' );
    $catId =  $get_terms[0]->term_id;

    $category_surge_pricing = get_term_meta($catId, "category-surge-pricing", true);

    $product_surge_pce =  get_post_meta($product_id, "surge-prices", true) ?  get_post_meta($product_id, "surge-prices", true) : $category_surge_pricing;

     $today = $rearbageDate[2].'-'.$rearbageDate[0].'-'.$rearbageDate[1];
    //  var_dump($today);
    // Y-m-d
    // echo $product_surge_pce;
    // echo $today;
    //  echo "<pre>";
    //  print_r($product_surge_pce);

    if ($product_surge_pce ) {
        foreach ($product_surge_pce as $key => $value){ 
            $start_date =  $value['start-date'];
            $end_date =    $value['end-date'];
            $price_type =  $value['price-type'];
            $price_surge =  array_key_exists('price-surge', $value) ? floatval($value['price-surge']) : floatval($value['price']);

            // var_dump($start_date);

        if ($today >=  $start_date && $today <= $end_date ) {
            if ($price_type === "Dollar Price") {
                     $updated_price =  $product_price + $price_surge;
                        break;
                    } else{
                    // if ($price_type === "Percentage") {
                        $updated_price = round(($product_price * $price_surge / 100) + $product_price, 2);
                            break;
                        // }
                    }
            } else {
                $updated_price = $product_price;
                break;     
            }
        }
    } else {
        $updated_price = $product_price;
    }
     echo $updated_price;
      die();
  }
///////////////////////////////////////////////////////////////////////////////

add_action('wp_ajax_product_availibilty_by_datepicker_rage' , 'product_availibilty_by_datepicker_rage');
add_action('wp_ajax_nopriv_product_availibilty_by_datepicker_rage','product_availibilty_by_datepicker_rage');
function product_availibilty_by_datepicker_rage(){
    // echo $_POST['availibilty'];
    // echo $_POST['productID'];
    $month_num = $_POST['get_month_'];


    $month_name = date("F", mktime(0, 0, 0, $month_num, 10));  
    // Display month name
    // $month_name."\n";
    $currentMonth = substr($month_name, 0, 3);

    //  $currentMonth = date('M');
    $availability_ = get_post_meta($_POST['productID'], "availability", true);
    $availability_true =  $availability_[$currentMonth];
    echo $availability_true;
    
    die();
}

//////////////////////////////////// JS CODE ST HERE///////////////////////////////////////////

add_action("wp_footer", "my_ctm_js_func", 100);

function my_ctm_js_func(){ 
if (is_product()) { ?>
        <script type="text/javascript">
             var ajaxurl = "<?php echo admin_url( 'admin-ajax.php' ); ?>";
        jQuery(document).ready(function () {
            
              jQuery(".ss_new_price").before('<p class="ctm-vari-price" style="display: none !important"></p>');
            
            jQuery( 'input.variation_id' ).change( function(){
             if( '' != $(this).val() ) {
                var var_id = $(this).val();
                jQuery('.ctm-vari-price').html(var_id);  
        //     //    alert('You just selected variation #' + var_id);
            }
          });


        //  setTimeout(function(){ 
        //     console.log(jQuery(".ctm-vari-price").first().text());
        //     }, 1000);

        //    console.log(jQuery(".ctm-vari-price").first().text());
/////////////////////////////////// FLOWER NOT AVAILBLE DIV APPEND CODE ////////////////////////////////////////////////

            jQuery(".ss_new_price").before('<h2 class="fl-not-avail">Flower Out Of Season</h2>');
            // jQuery(".ss_new_price").before('<p class="ctm-vari-price"></p>');

///////////////////////////////// SHOW UPDATED PRICE ON SURGE BEHAFT////////////////////////////////////////////////////

            getprice = jQuery('input[name*="updated_price_"]').val();
            // jQuery('.ss_new_price span.woocommerce-Price-amount.amount bdi').html('<span class="woocommerce-Price-currencySymbol">$</span>'+getprice);
            // jQuery('.single-product span.woocommerce-Price-amount.amount').css("display", "inline-block");

//////////////////////////////// PRODUCT Availability AJAX CODE STATR   //////////////////////////////////

            jQuery.ajax({
                url: ajaxurl,
                type: 'post',
                dataType: "text",
                data: { action: 'product_availibilty_by_datepicker_rage',get_month_: <?php echo date('m'); ?>, productID: jQuery("#productID").val() },
                success: function(data) {
                //  console.log(data, 'ready');
                if (data === "false") {
                     jQuery("h2.fl-not-avail").attr('style','display: block !important');
                     jQuery(".ss_new_price").attr('style','display: none !important');
                     
                    }else {
                         jQuery(".ss_new_price").attr('style','display: block !important');
                         jQuery("h2.fl-not-avail").attr('style','display: none !important');
                    }
                }
        });
//////////////////////////////// PRODUCT Availability AJAX CODE ON CHANGE START  //////////////////////////////////

        jQuery(".someone_date_picker").change(function(){
            var getDate = jQuery(this).val();
            get_month_ = getDate.split("/")[0];

            jQuery.ajax({
                url: ajaxurl,
                type: 'post',
                dataType: "text",
                data: { action: 'product_availibilty_by_datepicker_rage', get_month_: get_month_,  productID: jQuery("#productID").val() },
                success: function(data) {
                // console.log(data, 'change');
                if (data === "false") {
                     jQuery("h2.fl-not-avail").attr('style','display: block !important');
                     jQuery(".ss_new_price").attr('style','display: none !important');
                     
                    }else {
                         jQuery(".ss_new_price").attr('style','display: block !important');
                         jQuery("h2.fl-not-avail").attr('style','display: none !important');
                    }
                }
            });
        });

///////////////////////////////////// SURGE PRICE BY DATEPICKER AJAX////////////////////////////////////
        setTimeout(function(){ 
            jQuery.ajax({
            url: ajaxurl,
            type: 'post',
            dataType: "text",
            data: { action: 'surge_price_by_datepicker_rage', datepickerDate: jQuery(".someone_date_picker").val(), proType:jQuery("#ctm_prodyct_Type").val(), productID: jQuery("#productID").val(), varproId: jQuery(".ctm-vari-price").first().text() },
            success: function(data) {
                console.log(data, 'ready');
            getprice = jQuery('input[name*="updated_price_"]').val(data);
             jQuery('.ss_new_price span.woocommerce-Price-amount.amount bdi').html('<span class="woocommerce-Price-currencySymbol">$</span>'+data);
             jQuery('.single-product span.woocommerce-Price-amount.amount').attr('style','display: inline-block !important');
            }
        });
    }, 1000);
//////////////////////////////////AJAX CALL ON CHANGE/////////////////////////////////////////////
        jQuery(".someone_date_picker").change(function(){
            jQuery.ajax({
            url: ajaxurl,
            type: 'post',
            dataType: "text",
            data: { action: 'surge_price_by_datepicker_rage', datepickerDate: jQuery(".someone_date_picker").val(), proType:jQuery("#ctm_prodyct_Type").val(), productID: jQuery("#productID").val(), varproId: jQuery(".ctm-vari-price").first().text() },
            success: function(data) {
               console.log(data, 'change');
                getprice = jQuery('input[name*="updated_price_"]').val(data);
            jQuery('.ss_new_price span.woocommerce-Price-amount.amount bdi').html('<span class="woocommerce-Price-currencySymbol">$</span>'+data);
            jQuery('.single-product span.woocommerce-Price-amount.amount').attr('style','display: inline-block !important');
            }
        });
      });

//////////////////////////////////AJAX CALL ON VARIATION CHANGE/////////////////////////////////////////////

jQuery( 'input.variation_id' ).change( function(){
            if( '' != jQuery(this).val() ) {
               var var_id = jQuery(this).val();
                    jQuery.ajax({
                    url: ajaxurl,
                    type: 'post',
                    dataType: "text",
                    data: { action: 'surge_price_by_datepicker_rage', datepickerDate: jQuery(".someone_date_picker").val(), proType:jQuery("#ctm_prodyct_Type").val(), productID: jQuery("#productID").val(), varproId: var_id },
                    success: function(data) {
                        console.log(data, 'variation');
                        getprice = jQuery('input[name*="updated_price_"]').val(data);
                     jQuery('.ss_new_price span.woocommerce-Price-amount.amount bdi').html('<span class="woocommerce-Price-currencySymbol">$</span>'+data);
                     jQuery('.single-product span.woocommerce-Price-amount.amount').attr('style','display: inline-block !important');
                    }
                });
            }
         });
         
        }); //
</script>
<?php 
}
} 