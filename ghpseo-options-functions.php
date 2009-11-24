<?php

/*  Copyright (c) 2009 Greg Mulhauser, http://counsellingresource.com

	Developers: If you'd like to use this class for your own plugins, to avoid the monstrosity that is the usual method for creating WordPress options pages, please go right ahead. In return, I'd be much obliged if you could please mention my site and/or plugins on your own site, as well as keeping this message and copyright notice intact with any classes you distribute which are based on this class. (You know the drill: "lots of effort has gone into developing this software, blah blah blah".)
	
	The gregsOptionsHandler class is designed for use under WordPress 2.7+ and assumes that options have already been set up with add_option and register_setting. See the gregsSetupHandler class provided in the file ending "-setup-functions.php" for details on how options can easily be grouped into sets for use with this class. That same class also sets up the CSS which formats the options pages produced by this class.
	
	The class supports radio buttons, checkboxes, text, textarea, and select options, as well as options marked 'extra' which will just be displayed as-is, without any extra parsing or structure. (You might use the 'extra' type to display a donation button, for example.) It also supports a plain page of instructions for your plugin, which will just be loaded as-is, without any modifications. You'll find extensive usage examples included in the options files distributed with this plugin. Two caveats that might be less obvious: 1) in keeping with the usual ".ini" practice, everything on a line after a semi-colon will be ignored, so use the replacements capability for anything that requires one in your final output, and 2) avoid equals signs except as part of the standard "label = value" structure of the ".ini" file.

	This class assumes it sits within a file ending with "-functions.php", and it looks for ".ini" files named with the same pattern, minus the "-functions.php", and optionally located within a subdirectory that gets passed in. It has been done this way so if you only have one or two options pages and want to keep them at the same directory level as this class, you can do so, and everything will sort nicely -- but if you have a bunch of them, you can just dump them in a subdirectory. The class also looks for a subdirectory of ".txt" files sitting in the same directory as your ".ini" files. These files will be grabbed without parsing and the contents inserted at the top of your options pages when the donation flag passed to the class is false. In other words, when no donation has been made, the content of the files will be inserted; when a donation has been made, the content will not be inserted.
	
	The options pages themselves are created out of ".ini" files parsed with a quick and clean, PHP4-compatible reader that does only the bare minimum we need. The more capable PHP5 parse_ini_file might be more efficient -- I'm not sure -- but the PHP4 version of that function has problems which prevent it from doing the job for us; thus the quick and clean function instead. If you would rather use the PHP5 version when running under PHP5, un/comment the relevant lines in prep_settings.
	
	The rest will hopefully be self-explanatory if you have a peek at the options setup page which loads this class, together with the extensive examples in this plugin's options files.
	
    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License version 3 as
    published by the Free Software Foundation.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA

*/

class ghpseoOptionsHandler {

var $replacements = array(); // set of replacements to perform during parsing
var $pages = array(); // set of pages we're expecting, as an array with page name, page title, page menu entry
var $domain; // our text translation domain
var $plugin_prefix; // prefix for each option name
var $oursettings; // holds full set of parsed settings
var $ourextra; // holds extra material to be displayed at top of settings page
var $page_title; // indicates main title for page, derived from array passed in
var $instructions; // indicates whether we're handling an instructions page (i.e., no submit button)
var $path; // where are we?
var $submenu; // plain name of submenu we're displaying
var $thispage; // name of this page, from keys in var $pages

function ghpseoOptionsHandler($swap = array(), $pages = array(),$domain,$plugin_prefix='',$subdir='',$instname='') {
$this->__construct($swap,$pages,$domain,$plugin_prefix,$subdir,$instname);
return;
} 

function __construct($swap = array(), $pages = array(),$domain,$plugin_prefix='',$subdir='',$instname='') {
$this->replacements = $swap;
$this->domain = $domain;
$this->plugin_prefix = $plugin_prefix;
$this->pages = $pages;
$dir = str_replace(basename( __FILE__),"",plugin_basename(__FILE__)); // get plugin folder name
$base = str_replace("-functions.php","",basename( __FILE__)); // get this file's name without extension, assuming it ends with '-functions.php'
$this->path = $dir . $base;
$subdir .= ($subdir != '') ? '/' : '';
$root = WP_PLUGIN_DIR . '/' . $dir . $subdir; // this is where we're looking for our options files
$sub = isset ($_GET['submenu']) ? $_GET['submenu'] : '';
$filetail = ($sub != '') ? "-$sub" : ''; // options file corresponding to this submenu
$this->submenu = $sub;
$this->instructions = ($sub == $instname) ? true : false; // we'll do less work for the instructions page
$extraload = $root . '/extra/' . $base . $filetail . '.txt'; // set up for grabbing extra options page content
$this->ourextra = (file_exists($extraload)) ? file_get_contents($extraload) : '';
$mainload = $root . $base . $filetail . '.ini'; // set up for grabbing main options page content
$this->oursettings = $this->prep_settings($mainload,$instname);
if (!($this->instructions)) $this->oursettings = array_map(array($this,'do_option_replacements'),$this->oursettings);
return;
} // end constructor

function prep_settings($toload = '',$instname='instructions') { // grab and parse a settings page into an array
$ourpages = $this->pages;
$sub = $this->submenu;
if (file_exists($toload)) {
	$this->thispage = ($sub != '') ? $sub : 'default';
	if (array_key_exists($sub,$ourpages))
	   $this->page_title = wptexturize(__($ourpages[$sub][0],$this->domain));
	   else ($sub == '') ? $this->page_title = wptexturize(__($ourpages['default'][0],$this->domain)) : $this->page_title = '';
	if ($this->instructions) $settings = file_get_contents($toload);
	elseif (PHP_VERSION >= 5)
// If you want to use the PHP5 function when available, uncommment the following line and comment out the line after
//	   $settings = parse_ini_file($toload);
	   $settings = $this->parse_ini_file_php4($toload);
	   else $settings = $this->parse_ini_file_php4($toload);
	   } // end action if corresponding ini file exists
	else $settings = array();
return $settings;
} // end prepping settings

function parse_ini_file_php4 ($file) {
// quick and clean replacement because PHP 4.4.7 fails to load arrays properly
$file_handle = fopen($file, "rb");
while (!feof($file_handle) ) {
	$line_of_text = trim(fgets($file_handle),"\r\n ");
	if (strstr($line_of_text,';')) {
	   $temp = explode(';',$line_of_text);
	   $line_of_text = $temp[0];
	   } // end handling comments
	$firstchar = substr($line_of_text,0,1);
	if (!(($line_of_text == '') || ($firstchar == '['))) { // ignore sections and blanks
	   $parts = explode('=', $line_of_text);
	   $parts[0] = trim($parts[0],'[] ');
	   $parts[1] = trim($parts[1],' "');
	   $output[$parts[0]][]=$parts[1];
	   } // end handling only non-sections
	}
fclose($file_handle);
return $output;
}

function adust_setting_name($setting='') { // we like a prefix on our settings
return $this->plugin_prefix . $setting;
} // end adjusting setting name

function do_option_replacements($content='') { // we may have some values to swap out
$content = str_replace(array_keys($this->replacements),array_values($this->replacements),$content);
return $content;
}

function do_save_button($buttontext='Save Changes') { // make our save button
$button = __($buttontext, $this->domain);
if ($this->instructions) $save = '';
else $save = <<<EOT
<table class="form-table">
<tr valign="top">
<th scope="row"></th>
<td><p class="submit">
<input type="submit" name="Submit" class="button-primary" value="{$button}" />
</p>
</td>
</tr>
</table>
EOT;
return $save;
} // end creating save button

function do_pagemenu() { // make a simple list menu of all our options pages
$output = '';
$ourpages = $this->pages;
if (count($ourpages) > 1) {
	$output = "<div class='" . $this->plugin_prefix . "menu'>\n<ul>\n";
	foreach ($ourpages as $page=>$details) {
	   $menutitle = wptexturize(__($details[1],$this->domain));
	   $menutitle = str_replace(' ','&nbsp;',$menutitle);
	   if ( $this->thispage == $page )
			$output .= "<li><strong>{$menutitle}</strong> | </li>";
	   else { // do a link
			$submenu = ($page == 'default') ? "" : "&amp;submenu={$page}";
			$output .= "<li><a href=\"options-general.php?page={$this->path}.php{$submenu}\">{$menutitle}</a> | </li>";
			} // end doing an actual link
	   } // end loop over pages
	$output = substr($output,0,strlen($output)-8) . '</li>'; // snip off the last ' | ' inside the <li>
	$output .= "</ul>\n</div>\n";
	} // end check for array with just one page
return $output;
} // end creating page menu

function conflict_check($problemapps=array(),$name='') { // are other plugins running which could conflict with this one? if so, construct a message to that effect
$domain = $this->domain;
$warningclass = $this->plugin_prefix . 'warning';
foreach ($problemapps as $problemapp) {
   $test = (array_key_exists('class',$problemapp)) ? 'class' : 'function';
   $testfx = $test . '_exists';
   if ($testfx($problemapp[$test])) {
	   $conflict = $problemapp['name'];
	   $warning = $problemapp['warning'];
	   if (array_key_exists('remedy',$problemapp)) $remedy = $problemapp['remedy'];
	   else $remedy = '';
	   } // end testing for problem apps
   } // end loop over problem apps
if ($conflict == '') $message = '';
else {
$warningprefix = __('Warning: Possible conflict with', $domain);
$warningend = ($remedy != '') ? $remedy : __('For best results, please disable the interfering plugin',$domain);
$message = <<<EOT
<div class="{$warningclass} error fade">
<p><strong><em>{$warningprefix} '{$conflict}'</em></strong></p>
<p>{$warning} <em>{$name}</em>.</p>
<p>{$warningend} '{$conflict}'</strong>.</p>
</div>
EOT;
} // end generating conflict message
return wptexturize($message);
} // end conflict check

function display_options($settings_prefix='',$problems=array(),$name='',$dofull=true,$donated=false) {
// put together a whole page of options from body, title, menu, save button, etc.
$body = $this->do_options($dofull,false);
$title = $this->page_title;
$save = $this->do_save_button();
$menu = $this->do_pagemenu();
$thispage = $this->thispage;
$domain = $this->domain;
$plugin_prefix = $this->plugin_prefix;
$thankspre = __("Thank you for recognizing the value of this plugin with a direct financial contribution or with a link to:",$domain);
$thankspost = __("I really appreciate your support!",$domain);
$donation = ($donated) ? wptexturize("<div class='{$plugin_prefix}thanks'><p>{$thankspre} {$name}. {$thankspost}</p></div>") : $this->ourextra;
$conflict = $this->conflict_check($problems,$name);

$displaytop = <<<EOT
<div class="wrap">
<form method="post" action="options.php"> 
EOT;
$displaybot = <<<EOT
<h2>{$title}</h2>
{$menu}
{$donation}
{$conflict}
{$body}
{$save}
</form>
</div>
EOT;
echo $displaytop;
if (!$this->instructions) settings_fields($settings_prefix . $thispage);
screen_icon();
echo $displaybot;
return;
} // end displaying options

function do_options($full=true,$echo=true) { // meat & potatoes: further process the array which we got by parsing the ini file
$settings = $this->oursettings;
$domain = $this->domain;
if (!is_array($settings)) return wptexturize(__($settings,$domain));
$output = '';
$elements = count($settings['setting']);
$stepper = '0';

while ($stepper < $elements) { // hey, don't complain about funky indenting -- it's all those eots

$header = wptexturize(__($settings['header'][$stepper], $domain));
$preface = wptexturize(__($settings['preface'][$stepper], $domain));

if ($header != '')
	$output .= "<h3>{$header}</h3>\n";
if (($preface != '') && $full)
	$output .= "<p>$preface</p>\n";
if (($header != '') || ($preface != ''))
	$output .= '<table class="form-table ' . $this->plugin_prefix . 'table">';

$output .=  '<tr valign="top"><th scope="row">' . $settings['label'][$stepper] . "</th>\n<td>\n";

$properties = explode(',', $settings['type'][$stepper]);

if ($properties[0] == 'text') {
$settings['setting'][$stepper] = $this->adust_setting_name($settings['setting'][$stepper]);
// we use wp_specialchars_decode first in case this field has htmlspecialchars set as its callback filter with register_settings
// have to use wp_specialchars_decode TWICE because WP is double-specialcharring it
$echosetting = htmlspecialchars(stripslashes(wp_specialchars_decode(wp_specialchars_decode(get_option($settings['setting'][$stepper], ENT_QUOTES), ENT_QUOTES))));
$echodescription = wptexturize(__($settings['description'][$stepper], $domain));
$output .= <<<EOT
<input type="text" size="{$properties[1]}" name="{$settings['setting'][$stepper]}" value="{$echosetting}" />\n<br />{$echodescription}
EOT;
} // end handling text

elseif ($properties[0] == 'textarea') {
$settings['setting'][$stepper] = $this->adust_setting_name($settings['setting'][$stepper]);
// we use wp_specialchars_decode first in case this field has htmlspecialchars set as its callback filter with register_settings
// have to use wp_specialchars_decode TWICE because WP is double-specialcharring it
$echotext = htmlspecialchars(stripslashes(wp_specialchars_decode(wp_specialchars_decode(get_option($settings['setting'][$stepper], ENT_QUOTES), ENT_QUOTES))));
$output .= <<<EOT
\n<textarea cols="{$properties[1]}" rows="$properties[2]" name="{$settings['setting'][$stepper]}">{$echotext}</textarea>\n
EOT;
$description = wptexturize(__($settings['description'][$stepper], $domain));
if ($description != '')
	$output .=  "<br />$description";
} // end handling textarea

elseif (($properties[0] == 'checkbox') || ($properties[0] == 'radio')) {
$nowcounter = 0;
$nowsettings = explode(',',$settings['setting'][$stepper]);
$nowvalues = explode(',',$settings['value'][$stepper]);
$nowdescriptions = explode('|',$settings['description'][$stepper]);
$output .= "<ul>\n";
while ($nowcounter < $properties[1]) {
$nowsettings[$nowcounter] = $this->adust_setting_name($nowsettings[$nowcounter]);
($properties[0] == 'checkbox') ?
			$testcheck = $nowcounter : $testcheck = 0; // if radio button, only look at setting 0 in following test, because there is only one, otherwise step through the settings
(get_option($nowsettings[$testcheck]) == $nowvalues[$nowcounter]) ?
			$checked = ' checked="checked"' : $checked = '';
$echodescription = wptexturize(__($nowdescriptions[$nowcounter],$domain));
if ($properties[0] == 'checkbox')
	$output .= <<<EOT
<li><label for="{$nowsettings[$nowcounter]}"><input name="{$nowsettings[$nowcounter]}" type="checkbox" id="{$nowsettings[$nowcounter]}" value="{$nowvalues[$nowcounter]}"{$checked} />&nbsp;{$echodescription}</label></li>\n
EOT;
else $output .= <<<EOT
<li><input type="radio" name="{$nowsettings[0]}" value="{$nowvalues[$nowcounter]}"{$checked} />&nbsp;{$echodescription}</li>\n
EOT;
$nowcounter ++;
} // end loop over number of boxes or buttons
$output .= "</ul>\n";
} // end handling checkbox or radio

elseif ($properties[0] == 'select') {
$nowcounter = 0;
$nowvalues = explode(',',$settings['value'][$stepper]);
$nowdescriptions = explode('|',$settings['description'][$stepper]);
$settings['setting'][$stepper] = $this->adust_setting_name($settings['setting'][$stepper]);
$output .= '<select name="' . $settings['setting'][$stepper] . '" size="1">';
while ($nowcounter < $properties[1]) {
(get_option($settings['setting'][$stepper]) == $nowvalues[$nowcounter]) ?
			$selected = ' selected="selected"' : $selected = '';
$output .=  <<<EOT
<option value="{$nowvalues[$nowcounter]}"{$selected}>{$nowdescriptions[$nowcounter]}</option>\n
EOT;
$nowcounter ++;
} // end loop over select values
$output .= "</select>\n";
} // end handling select

elseif ($properties[0] == 'extra')
		$output .= wptexturize(__($settings['description'][$stepper], $domain));

$output .= "\n</td>\n</tr>\n";

if (($stepper + 1 == $elements) || ($settings['header'][$stepper + 1] != '') || ($settings['preface'][$stepper + 1] != '')) {
$output .= '</table>';
}

$stepper ++;
} // end loop over headings

if ($echo)
	echo $output;
else return $output;

return;

} // end function which outputs options

} // end class definition

?>