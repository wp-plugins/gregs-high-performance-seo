<?php
require_once('ghpseo-writing-functions.php');

function ghpseo_writing_setngo() { // set up our writing page additions

$name = "Greg's High Performance SEO";
$domain = 'ghpseo-plugin'; // text domain
$plugin_prefix = 'ghpseo'; // all-around prefix used by this plugin, no underscore or hyphen
$restricted = get_option('ghpseo_restrict_access'); // indicates whether to restrict access to just those authors who can publish

$meta_set = array(  // our set of additions
"secondary_title" => array(  
				"name" => "_ghpseo_secondary_title",  
				"type" => "text",  
				"std" => "",  
				"title" => __( 'Secondary Title', $domain ),  
				"description" => __( 'You can specify how the secondary title will be used on the plugin settings pages.', $domain ),
				"allow_tags" => true,
				),
"keywords" => array(  
				"name" => "_ghpseo_keywords",  
				"type" => "text",  
				"std" => "",  
				"title" => __( 'Head Keywords', $domain ),  
				"description" => __( 'This comma-separated list will be included in the head along with any specified tags.', $domain ),
				"allow_tags" => false,
				),
"alternative_description" => array(  
				"name" => "_ghpseo_alternative_description",  
				"type" => "textarea",  
				"rows" => 3,
				"cols" => 40,
				"std" => "",  
				"title" => __( 'Head Description', $domain ),  
				"description" => __( 'If specified, this description overrides the excerpt for use in the head.', $domain ),
				"allow_tags" => false,
				),
"secondary_description" => array(  
				"name" => "_ghpseo_secondary_desc",  
				"type" => "textarea",  
				"rows" => 3,
				"cols" => 40,
				"std" => "",  
				"title" => __( 'Secondary (On-Page) Description', $domain ),  
				"description" => __( 'If specified, this description can be displayed in the post or page body.', $domain ),
				"allow_tags" => true,
				),
);

// clean up our array according to options set
if (!get_option('ghpseo_editing_title')) unset($meta_set['secondary_title']);
if (!get_option('ghpseo_editing_description')) unset($meta_set['alternative_description']);
if (!get_option('ghpseo_editing_keywords')) unset($meta_set['keywords']);

$page_set = $post_set = $meta_set;

if (!get_option('ghpseo_editing_secondary_description_pages')) unset($page_set['secondary_description']);

if (!get_option('ghpseo_editing_secondary_description_posts')) unset($post_set['secondary_description']);

$dashboard_set = (get_option('ghpseo_dashboard')) ? $post_set : array();

$docounter = (get_option('ghpseo_editing_counter') && get_option('ghpseo_editing_description')) ? '_ghpseo_alternative_description' : '';

// and do it!

new ghpseoWritingAdditions($name, $plugin_prefix, $domain, $post_set, $page_set, $dashboard_set, $restricted, $docounter);

return;

} // end doing the writing additions

ghpseo_writing_setngo();

?>