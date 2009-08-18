<?php
if( !defined( 'ABSPATH' ) && !defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit();
} else {
if (!class_exists('ghpseoSetupHandler')) include ('ghpseo-setup-functions.php');
$ghpseo_options_set = ghpseoSetupHandler::grab_settings(); // get the set of options we're deleting

	   if (current_user_can('delete_plugins')) {
		   echo '<div id="message" class="updated fade">';
		   foreach ($ghpseo_options_set as $optionset=>$optionarray) {
			 foreach ($optionarray as $setting) {
			   $delete_setting = delete_option('ghpseo_' . $setting[0]);
			   if($delete_setting) { // confirm each deleted setting individually
				   echo '<p style="color:green">';
				   printf(__('Setting \'%s\' has been deleted.', 'ghpseo-plugin'), "<strong><em>{$setting[0]}</em></strong>");
				   echo '</p>';
			   } else { // this will occur when user hasn't actually specified a setting yet, since WP doesn't actually seem to fill them in explicitly with the default values upon activation
				   echo '<p style="color:red">';
				   printf(__('Error deleting setting \'%s\'.', 'ghpseo-plugin'), "<strong><em>{$setting[0]}</em></strong>");
				   echo '</p>';
			   }
			 } // end inner loop over individual settings
		   }
		   echo '<strong>Thank you for using Greg&#8217;s High Performance SEO plugin!</strong>';
		   echo '</div>'; 
	  }
}

?>