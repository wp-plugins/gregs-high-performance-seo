<?php
require_once('ghpseo-options-functions.php');

function ghpseo_options_setngo() { // set up our options pages
$name = "Greg's High Performance SEO";
$settings_prefix = 'ghpseo_options_'; // prefix for each distinct set of options registered, used by WP's settings_fields to set up the form correctly
$domain = 'ghpseo-plugin'; // text domain
$plugin_prefix = 'ghpseo_'; // prefix for each option name, with underscore
$subdir = 'options-set'; // subdirectory where options ini files are stored
$instname = 'instructions'; // name of page holding instructions
$dofull = get_option('ghpseo_abbreviate_options') ? false : true; // flip this value so unitialized option default of zero will equate to "do not abbreviate, show us full options"
$donated = get_option('ghpseo_donated');
$site_link = ' <a href="http://counsellingresource.com/">CounsellingResource.com</a>';
$plugin_page = " <a href=\"http://counsellingresource.com/features/2009/07/23/high-performance-seo/\">Greg's High Performance SEO plugin</a>";
$paypal_button = '<a href="https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=2799661"><img src="https://www.paypal.com/en_GB/i/btn/btn_donate_LG.gif" name="paypalsubmit" alt="" border="0" /></a>';
$replacements = array( // values we'll swap out in our option page text
					 '%site_link%' => $site_link,
					 '%plugin_page%' => $plugin_page,
					 '%paypal_button%' => $paypal_button,
					 );
$standard_warning = __('The plugin listed above, which employs output buffering hacks to circumvent limitations imposed by WordPress APIs, may interfere with the usability of many different plugins designed to enhance the functionality of the head section of WordPress output. It may interfere with the normal operation of this plugin:', $domain);
$problems = array( // these indicate presence of other plugins which may cause problems
			'headspace' => array(
				'class' => 'HeadSpace2_Plugin',
				'name' => 'HeadSpace 2',
				'warning' => $standard_warning,
				 ),
			'aiosp' => array(
				'class' => 'All_in_One_SEO_Pack',
				'name' => 'All in One SEO Pack',
				'warning' => $standard_warning,
				 ),
			'platinum' => array(
				'class' => 'Platinum_SEO_Pack',
				'name' => 'Platinum SEO Pack',
				'warning' => $standard_warning,
				 ),
			'metaseo' => array(
				'class' => 'MetaSeoPack',
				'name' => 'Meta SEO Pack',
				'warning' => $standard_warning,
				 ),
			'seoultimate' => array(
				'class' => 'SU_Module',
				'name' => 'SEO Ultimate',
				'warning' => $standard_warning,
				 ),
			'wpseo' => array(
				'class' => 'WPlize',
				'name' => 'wpSEO',
				'warning' => $standard_warning,
				 ),
			);
$pages = array ( // file names and titles for each page of options
			   'default' => array(
			   "$name: " . __('Configuration',$domain),
			   __('Configuration',$domain),
			   ),
			   'maintitles' => array(
			   "$name: " . __('Main Titles',$domain),
			   __('Main Titles',$domain),
			   ),
			   'secondarytitles' => array(
			   "$name: " . __('Secondary (Body) Titles',$domain),
			   __('Secondary Titles',$domain),
			   ),
			   'secondarydesc' => array(
			   "$name: " . __('Secondary Descriptions',$domain),
			   __('Secondary Descriptions',$domain),
			   ),
			   'pagedcomments' => array(
			   "$name: " . __('Paged Comments',$domain),
			   __('Paged Comments',$domain),
			   ),
			   'headmeta' => array(
			   "$name: " . __('Head Meta',$domain),
			   __('Head Meta',$domain),
			   ),
			   'legacy' => array(
			   "$name: " . __('Support for Legacy SEO Plugins',$domain),
			   __('Legacy SEO Plugins',$domain),
			   ),
			   $instname => array(
			   "$name: " . __('Instructions and Background Information',$domain),
			   __('Instructions',$domain),
			   ),
			   'donating' => array(
			   "$name: " . __('Support This Plugin',$domain),
			   __('Contribute',$domain),
			   ),
			   );

$options_handler = new ghpseoOptionsHandler($replacements,$pages,$domain,$plugin_prefix,$subdir,$instname); // prepares settings

// just in case we need to grab anything from the parsed result first, this is where we'd do it

$options_handler->display_options($settings_prefix,$problems,$name,$dofull,$donated); // now show the page

return;
} // end displaying the options

if (current_user_can('manage_options')) ghpseo_options_setngo();

?>