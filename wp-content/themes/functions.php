<?php
// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

// BEGIN ENQUEUE PARENT ACTION
// AUTO GENERATED - Do not modify or remove comment markers above or below:

if ( !function_exists( 'chld_thm_cfg_locale_css' ) ):
function chld_thm_cfg_locale_css( $uri ){
    if ( empty( $uri ) && is_rtl() && file_exists( get_template_directory() . '/rtl.css' ) )
        $uri = get_template_directory_uri() . '/rtl.css';
    return $uri;
}
endif;
add_filter( 'locale_stylesheet_uri', 'chld_thm_cfg_locale_css' );
        
if ( !function_exists( 'child_theme_configurator_css' ) ):
function child_theme_configurator_css() {
    wp_enqueue_style( 'chld_thm_cfg_child', trailingslashit( get_stylesheet_directory_uri() ) . 'style.css', array( 'astra-theme-css','woocommerce-layout','woocommerce-smallscreen','woocommerce-general' ) );

    // wp_enqueue_script( 'ale_custom_script', 'https://code.jquery.com/ui/1.10.4/jquery-ui.js', NULL, 1.0, true );
    // wp_enqueue_style( 'ale_custom_lightness', 'https://code.jquery.com/ui/1.10.4/themes/ui-lightness/jquery-ui.css' );
}
endif;
add_action( 'wp_enqueue_scripts', 'child_theme_configurator_css', 10 );
// END ENQUEUE PARENT ACTION

// add_action("admin_enqueue_scripts", function(){
//     wp_enqueue_script( 'ale_custom_script_admin', 'https://code.jquery.com/ui/1.10.4/jquery-ui.js', NULL, 1.0, true );
//     wp_enqueue_style( 'ale_custom_lightness_admin', 'https://code.jquery.com/ui/1.10.4/themes/ui-lightness/jquery-ui.css' );
// });

/////////////////////////////////////////////////////////////////////

add_action("woocommerce_before_add_to_cart_quantity", "add_custom_datepicker_fucntion");

function add_custom_datepicker_fucntion(){
    // echo get_transient( 'ctm_date_picker_session' );
echo '<p> <input type="hidden" autocomplete="off" placeholder="Enter Date" name="ctm_date_picker"  id="ctm_ppup_date_picker"></p>';
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
add_action( 'woocommerce_before_calculate_totals', 'misha_recalc_price' );

function misha_recalc_price( $cart_object ) {
foreach ( $cart_object->get_cart() as $value ) {
    $value[ 'data' ]->set_price( $value['updated_price_']);
}
}

///////////// SAVE CUSTOM DATA INTO DATABASE AFTER ORDER PLACING ///////////////

add_action( 'woocommerce_checkout_create_order_line_item', 'wdm_add_custom_order_line_item_meta',10,4 );

function wdm_add_custom_order_line_item_meta($item, $cart_item_key, $values, $order){

    if(array_key_exists('updated_price_', $values)) {
        $item->add_meta_data('updated_price_', $values['updated_price_']);
    }
}

//////////////////////////////////Save the Data of Custom Checkout WooCommerce Fields/////////////////////////////////////

function cloudways_save_extra_checkout_fields( $order_id, $posted ){
     $get_date_from_ss  = get_transient( 'ctm_date_picker_session' );
    //  $date_if_empty  = get_transient( 'ctm_date_session_if_empty' );
    // if (!empty($get_date_from_ss)) {
    //     $get_date_frm_session = $get_date_from_ss;
    // } else {
    //     $get_date_frm_session = $date_if_empty;
    // }

    if($get_date_from_ss) {
        update_post_meta( $order_id, 'ctm_date_picker', $get_date_from_ss );
    }
}
add_action( 'woocommerce_checkout_update_order_meta', 'cloudways_save_extra_checkout_fields', 10, 2 );

/////////////////////////////////////Display  the Data of  WooCommerce Custom Fields to User/////////////////////////////

function cloudways_display_order_data( $order_id ){
    // WC()->session->set( 'ctm_date_picker_session', null );
    // WC()->session->set( 'ctm_date_session_if_empty', null );
    delete_transient('ctm_date_picker_session');
    delete_transient('ctm_date_session_if_empty');
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
    $product_price = floatval (trim($product->get_regular_price()));

    $get_terms = get_the_terms( $product_id, 'product_cat' );

$catId =  $get_terms[0]->term_id;

$category_surge_pricing = get_term_meta($catId, "category-surge-pricing", true);


$product = wc_get_product($product_id); // If needed
$product_surge_pce =  get_post_meta($product_id, "surge-prices", true) ?  get_post_meta($product_id, "surge-prices", true) : $category_surge_pricing;
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
                        echo '<input name="updated_price_" class="dlr_price" type="hidden" value='. $updated_price .' > ';
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
    } 
});

/////////////////////////////////// HIDE ADD TO CART BUTTON //////////////////////////////////////////


//////////////////////////////////// JS CODE ST HERE///////////////////////////////////////////

add_action("wp_footer", "my_ctm_js_func");

function my_ctm_js_func(){ 
if (is_product()) { 
    global $woocommerce, $post;
    $currentMonth =  date('M');
    $availability_ = get_post_meta($post->ID, "availability", true);
    $availability_true =  $availability_[$currentMonth];
    if ($availability_true === "false") { ?>
        <script> jQuery(".e-atc-qty-button-holder").html("<h2>Product Not Available</h2>"); </script>
    <?php } ?>
        <script type="text/javascript">
        jQuery(document).ready(function () {
            getprice = jQuery('input[name="updated_price_"]').val();
            jQuery('span.woocommerce-Price-amount.amount bdi').html('<span class="woocommerce-Price-currencySymbol">$</span>'+getprice);
            jQuery('.single-product span.woocommerce-Price-amount.amount').css("display", "inline-block");
        });
</script>
<?php 
}
} 

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