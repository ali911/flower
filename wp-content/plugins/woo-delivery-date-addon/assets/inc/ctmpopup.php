<?php
add_action('wp_ajax_save_delivery_date_session' , 'save_delivery_date_session');
add_action('wp_ajax_nopriv_save_delivery_date_session','save_delivery_date_session');
function save_delivery_date_session(){

     $currentDate =  $_POST['currentDate'];
    setcookie('ctm_date_picker_cookies', $currentDate, time() + (86400 * 30), "/");

     echo $currentDate;
      die();
  }


add_action("wp_footer", "my_ctm_js_func_script", 20);

function my_ctm_js_func_script(){ ?>
  <script>
       var ajaxurl = "<?php echo admin_url( 'admin-ajax.php' ); ?>";
 
  jQuery(document).ready(function() {
    
    var show_slctd_date = "<?php echo $_COOKIE["ctm_date_picker_cookies"]; ?>";
        
    // jQuery("#flw-selected-date").text(show_slctd_date);
          

/////////////////////////////////SET DELIVERY DATE IN CALENDER/////////////////////////////////////////////
        //  console.log(show_slctd_date);
        var myArray_ = show_slctd_date.split("/");
        var month_ = parseInt(myArray_[0] -1);
        var day_ = parseInt(myArray_[1]);
        var year_ = parseInt(myArray_[2]);


       var myDate = new Date(year_,month_,day_); 
        jQuery('.someone_date_picker').datepicker();
        jQuery('.someone_date_picker').datepicker('setDate', myDate);

        
    //////////////////////////////////////////////////////////////////////////////
    jQuery.ajax({
            url: ajaxurl,
            type: 'post',
            dataType: "text",
            data: { action: 'save_delivery_date_session', currentDate: jQuery(".someone_date_picker").val() },
            success: function(data) {
//               console.log(data);
              jQuery("#flw-selected-date").text(data);
              // get_date_from_session_fun();
            }
        });
    //////////////////////////////////////////////////////////////////////////////

      });
//////////////////////////AJAX CALL ON CHANGE////////////////////////////////////////
        jQuery(".someone_date_picker").change(function(){
            // jQuery("#ctm_ppup_date_picker").val(jQuery(this).val());
            jQuery("#flw-selected-date").text(jQuery(this).val());
            jQuery.ajax({
            url: ajaxurl,
            type: 'post',
            dataType: "text",
            data: { action: 'save_delivery_date_session', currentDate: jQuery(".someone_date_picker").val() },
            success: function(data) {
//               console.log(data);
              jQuery("#flw-selected-date").text(data);
              // get_date_from_session_fun();
            }
        });
      });
      jQuery("span#flw-selected-date").css({"display": "block"});
    </script>
    <?php
}