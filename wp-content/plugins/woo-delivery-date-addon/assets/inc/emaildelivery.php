<?php

// add_action( 'woocommerce_email_before_order_table', 'ctm_add_content_specific_email', 20, 4 );
  
// function ctm_add_content_specific_email( $order, $sent_to_admin, $plain_text, $email ) {
//    if ( $email->id == 'customer_processing_order' ) {
//       $orderid = $order->id;
//       $get_shipping_method = $order->get_shipping_method();
  
     
//       $get_delry_date =  get_post_meta( $orderid, 'ctm_date_picker', true );

//       if ($get_shipping_method === "Flat rate") {
//          $shipping_date_ = $get_delry_date;
//     } else {
//         $shipping_date_ = explode("/", $get_delry_date);
//         $from_unix_time = mktime(0, 0, 0, $shipping_date_[0], $shipping_date_[1], $shipping_date_[2]);
//         $day_before = strtotime("yesterday", $from_unix_time);
//         $shipping_date_ = date('m/d/Y', $day_before);
//     }

//     echo '<h2 class="email-upsell-title">Delivery Date: '.$shipping_date_.'</h2>';


//    }
// }

///////////////////////////////  DISABLE CUSTOM META IN ORDER EMAIL ///////////////////////////////

add_filter( 'woocommerce_order_item_get_formatted_meta_data', 'unset_specific_order_item_meta_data', 10, 2);
function unset_specific_order_item_meta_data($formatted_meta, $item){
    // Only on emails notifications
    if( is_admin() || is_wc_endpoint_url() )
        return $formatted_meta;

    // echo '<pre>';
    // print_r($formatted_meta);
    // foreach( $formatted_meta as $key => $meta ){
       
    //     if (in_array($meta->key, array('updated_price_')))
    //         // unset($formatted_meta[$key]);
    //         if ($formatted_meta[$key] == 'updated_price_') {
    //             $formatted_meta[$key] = "Price";
    //         }
    // }
    // return $formatted_meta;
    foreach ( $formatted_meta as $key => $meta ) {
        if ($formatted_meta[$key]->display_key == 'updated_price_') {
            $formatted_meta[$key]->display_key = 'Item Price';
        }
    }
    
    return $formatted_meta;

}

///////////////////////////////  SHOW DELIVERY DATE IN ORDER EMAIL PLUGIN  ///////////////////////////////

function thwecm_show_shipping_rate($order, $email){
	// echo $order->get_id();
      $orderid = $order->get_id();
    //   $get_shipping_method = $order->get_shipping_method();
  
     
      $get_delry_date =  get_post_meta( $orderid, 'ctm_date_picker', true );

    // if ($get_shipping_method === "Flat rate") {
    //      $shipping_date_ = $get_delry_date;
    // } else {
    //     $shipping_date_ = explode("/", $get_delry_date);
    //     $from_unix_time = mktime(0, 0, 0, $shipping_date_[0], $shipping_date_[1], $shipping_date_[2]);
    //     $day_before = strtotime("yesterday", $from_unix_time);
    //     $shipping_date_ = date('m/d/Y', $day_before);
    // }

    echo '<h2 class="email-upsell-title" style="text-align: center; padding-top: 20px;">Delivery Date: '.$get_delry_date.'</h2>';

}

add_action('shipping_rate', 'thwecm_show_shipping_rate', 10, 2);

///////////////////////////////  ADD CUSTOM META AFTER QTY IN ORDER EMAIL ///////////////////////////////

function filter_woocommerce_email_order_item_quantity( $qty_display, $item ) {
    $qty_display = $qty_display .' '. get_post_meta($item['product_id'], 'stem-or-bunch', true);

    return $qty_display; 
}
add_filter( 'woocommerce_email_order_item_quantity', 'filter_woocommerce_email_order_item_quantity', 10, 2 );