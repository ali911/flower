<?php
add_action("admin_menu", "alie_custom_wc_products_settings");

      function alie_custom_wc_products_settings() {
          add_menu_page(
              "Hide Future Dates",
              "Hide Future Dates",
              "manage_options",
              "date_settings",
              "date_settings_page_call"
          );
          
      }
		function date_settings_page_call () {
			if (isset($_POST['get_date']) && !$_POST['get_date'] == ''){

				if (in_array($_POST['get_date'], json_decode(get_option("get_date") ))) {
					echo "<script>window.alert('Date Already Exist')</script>";
				} else {
				if (!empty(get_option("get_date"))){
					$get_date = get_option("get_date");
					$get_date = json_decode($get_date); 
					array_push($get_date, $_POST['get_date']);
					update_option("get_date", json_encode($get_date));
					// echo "<pre>"; print_r(json_decode(get_option("get_date"))); echo "</pre>"; // exit();
					
				} else {
					update_option("get_date", json_encode(array($_POST['get_date'])));
					// echo "<pre>"; print_r(json_decode(get_option("get_date"))); echo "</pre>"; // exit();
				}
			}
				
			}
      // else {
			// 	echo "<pre>"; print_r(json_decode(get_option("get_date"))); echo "</pre>"; // exit();
			// }
	
	//////////////////////////////////////////////////////

	if ($_GET['action'] == 'del' ) {
		$index_ = $_GET['id'];
		$singl_date = json_decode(get_option("get_date"));

		unset($singl_date[$index_]);
		$resetArr_index = array_values($singl_date);

		$delete_row = update_option("get_date", json_encode($resetArr_index));

		// echo "<pre>";
		// print_r($resetArr_index);
		// echo "</pre>";

		if ($delete_row) {
			header(
				"Location: " .
					admin_url() .
					"admin.php?page=date_settings"
			); //$_SERVER['HTTP_REFERER']
			exit();
		}//if code execute



	}
			
	?>
			<h1>Hide Future Dates.</h1>
			<form action="" method="post">
				<!-- <label for="birthday">Birthday:</label> -->
				<input type="text" id="get_date" name="get_date" autocomplete="off">
        
				<!-- <input type="submit"> -->
				<?php submit_button(); ?>
			</form>
			<br>
			<table id="hd_date_tb">
  <tr>
    <th>Date</th>
    <th>Action</th>
  </tr>
  <?php 
  $decode_date = json_decode(get_option("get_date"));
//   echo "<pre>";
//   print_r($decode_date);
//   echo "</pre>";
  foreach ($decode_date as $key => $value) {?>
	<tr>
    <td class="ss_date_"><?php echo $value; ?></td>
    <td><a href="<?php echo admin_url('admin.php?page=date_settings&action=del&id='.$key) ?>">Delete </a></td>
  </tr>
  <?php }
  ?>
  
  
</table>
	<?php
	}

  ////////////////////////////

  add_action("admin_footer", "admin_ctm_js_func_for_future_date");

function admin_ctm_js_func_for_future_date(){ ?>
<script type="text/javascript">
jQuery(document).ready(function () {
        jQuery('#get_date').datepicker({
        // beforeShowDay: function(date){
        // var day = date.getDay();
        // return [ day > 1 && day < 6, ''];
        // },
        minDate: 0,
        dateFormat: 'd/m/yy'
    });

}); // ready
</script> 
<?php }