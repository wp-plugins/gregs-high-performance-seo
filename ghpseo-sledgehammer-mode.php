<?php

// Use output buffering only if we really really have to, 'cause it's rude, creates compatibility problems with other plugins, and saps performance unnecessarily

// BEGIN OBNOXIOUS MODE STUFF
function ghpseo_obnoxious_mode_start() { // start the buffer
ob_start('ghpseo_obnoxious_mode_handler');
return;
} // end obnoxious mode startup

function ghpseo_obnoxious_mode_handler($header) { // modify the buffer contents
global $ghpseo; // grab our main object
// replace the title, strip old head meta (if they exist), and add our own
$header = preg_replace('/<title>([\w\W]*?)<\/title>/','<title>' . $ghpseo->select_title(true,false) . '</title>',$header);
$header = preg_replace("/<link rel ?= ?[\"']canonical[\"'] content ?= ?[\"']([\w\W]*?)[\"'] \/>/i",'',$header);
$header = str_replace('</title>', "</title>" . $ghpseo->canonical(), $header);
$replacements = array('description' => 'head_desc',
					  'keywords' => 'head_keywords',
					  'robots' => 'robots',
					  );
foreach ($replacements as $tag=>$value) {
   $header = preg_replace("/<meta name ?= ?[\"']{$tag}[\"'] content ?= ?[\"']([\w\W]*?)[\"'] \/>/i",'',$header);
   $header = str_replace('</title>', "</title>" . $ghpseo->$value(), $header);
   } // end loop over replacements
return $header;
} // end obnoxious mode handler

function ghpseo_obnoxious_mode_finish() {
// flush the output buffer, and flag if there's a potential conflict with any other rude apps
$handlers = ob_list_handlers();
$handlecount = count($handlers);
if ($handlecount > 0)
	$ok = (str_replace('ghpseo','',$handlers[$handlecount - 1]) != $handlers[$handlecount - 1]) ? true: false; 
if ($ok) { // if where expected in the ob handler array, all is well
	ob_end_flush();
	}
else { // otherwise, something else may be interfering, so flag it in the source code for debugging
	echo '<!-- mismatched object handlers detected:' . $handlers[$handlecount - 1] . ' -->';
// let's not flush at this point and just rely on eventual flush upon page delivery -- while this may clobber our head modifications badly, it's better than risking really fouling up some other plugin and potentially the whole page
//	@ob_end_flush(); // flush anyway, with errors suppressed
	}
return;
} // end obnoxious mode finish

add_action('template_redirect', 'ghpseo_obnoxious_mode_start',1); // buffer early
add_action('wp_head', 'ghpseo_obnoxious_mode_finish',99); // flush late

// END OBNOXIOUS MODE STUFF

?>