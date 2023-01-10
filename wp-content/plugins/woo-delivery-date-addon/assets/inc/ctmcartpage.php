<?php

function before_calculate_totals( $cart_obj ) {
    if ( is_admin() && ! defined( 'DOING_AJAX' ) ) {
     return;
    }
    // Iterate through each cart item
    foreach( $cart_obj->get_cart() as $key => $value ) {  
        // echo "<pre>";
        // print_r($value);
        // echo "testings";
        // $value['data']->set_price( ( 100 ) );

    //   if( isset( $value['csCost'] ) ) {
    //    $getPrice = $value['data']->get_price();
    //     $price = $value['csCost'] + $getPrice;
    //     $value['data']->set_price( ( $price ) );
    //   }
    //   if( isset( $value['setCost'] ) ) {
    //    $getPrice = $value['data']->get_price();
    //     $price = $value['setCost'] + $getPrice;
    //     $value['data']->set_price( ( $price ) );
    //   }
     
    }
   }
   add_action( 'woocommerce_before_calculate_totals', 'before_calculate_totals', 10, 1 );

   /////////////

add_action('wp_ajax_surge_price_by_datepicker_rage_in_cart_checkout' , 'surge_price_by_datepicker_rage_in_cart_checkout');
add_action('wp_ajax_nopriv_surge_price_by_datepicker_rage_in_cart_checkout','surge_price_by_datepicker_rage_in_cart_checkout');
function surge_price_by_datepicker_rage_in_cart_checkout(){

    $datepickerDate = $_POST['datepickerDate'];
    $rearbageDate = explode('/', $datepickerDate);
    
   
     $today = $rearbageDate[2].'-'.$rearbageDate[0].'-'.$rearbageDate[1];

     $cart = WC()->cart->cart_contents;

    foreach ($cart as $cart_item_id => $cart_item) {
        $product = $cart_item['data'];

        //  WC()->cart->get_cart()[$cart_item_key]['data']->set_regular_price('101');
        // WC()->session->set('updated_price_', 10);
        //  $cart_item['updated_price_'] = 10 ;
        // echo "<pre>";
        // print_r($cart_item);
        
        // echo $cart_item['data']->regular_price;
        $product_id = $cart_item['product_id'];
        $product_price =  $cart_item['data']->regular_price;
    
        $get_terms = get_the_terms( $product_id, 'product_cat' );
        $catId =  $get_terms[0]->term_id;
    
        $category_surge_pricing = get_term_meta($catId, "category-surge-pricing", true);
        $product_surge_pce =  get_post_meta($product_id, "surge-prices", true) ?  get_post_meta($product_id, "surge-prices", true) : $category_surge_pricing;

        
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

        $cart_item['updated_price_'] = $updated_price;
        WC()->cart->cart_contents[$cart_item_id] = $cart_item;

        //  echo $cart_item['updated_price_'] = $updated_price;
        // $cart_item['updated_price_'] = $updated_price;
        // echo "<pre>";
        // print_r($cart_item);
        // WC()->session->set('updated_price_', $updated_price);
        // echo $updated_price;
    }
    WC()->cart->set_session();
     die();
}


//////////////////////////////////// JS CODE ST HERE///////////////////////////////////////////

add_action("wp_footer", "my_ctm_cart_page_js_func", 100);

function my_ctm_cart_page_js_func(){ 
    if ( is_cart() || is_checkout() ) { ?>
     <script type="text/javascript">
        var ajaxurl = "<?php echo admin_url( 'admin-ajax.php' ); ?>";
        jQuery(document).ready(function () {
            jQuery(".someone_date_picker").change(function(){
                jQuery.ajax({
                    url: ajaxurl,
                    type: 'post',
                    dataType: "text",
                    data: { action: 'surge_price_by_datepicker_rage_in_cart_checkout', datepickerDate: jQuery(this).val() },
                    success: function(data) {
                    // console.log(data, 'change');
                    jQuery(".jet-popup__close-button").click();
                    jQuery( 'body' ).trigger( 'update_checkout' );
                     jQuery("[name='update_cart']").removeAttr('disabled').attr('aria-disabled','false').trigger("click");
                    }
            });
          });
        });
        </script>
    <?php 
    }
}
////////////////////////////


add_action('woocommerce_before_calculate_totals', 'set_new_customer_discount', 100, 1 );
function set_new_customer_discount( $cart ) {
    if ( is_admin() && ! defined( 'DOING_AJAX' ) )
        return;
        $ctmprice = WC()->session->get('updated_price_');

    // if ( did_action( 'woocommerce_before_calculate_totals' ) >= 2 )
    //     return;

    // // If it's not a new customer we exit
    // if( ! WC()->session->get('updated_price_') )
    //     return; // Exit

    // Loop Through cart items
    foreach ( $cart->get_cart() as $cart_item ) {
        $cart_item[ 'data' ]->set_price( $cart_item['updated_price_']);
    }
}
