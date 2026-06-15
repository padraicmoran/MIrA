<?php
/* 
Some general functions customised for this website
*/

// handle tidy URL links
function getLink($type, $id) {
	global $tidyURLs;
	if ($type == 'library' || $type == 'manuscript' || $type == 'person' || $type == 'place' || $type == 'text') {
		if ($tidyURLs) $link = '/' . $type . '/' . $id;
		else $link = '/index.php?page=' . $type . '&id=' . $id;
	}
	else {
		$link = '#';
	}
	return $link;
}

function makeMsHeading($ms) {
	global $libraries;

	$libraryID = strval($ms->identifier['libraryID']);
	// get official shelfmark
	$libraryShelfmark = $libraries[$libraryID]['city'] . ', ' . $libraries[$libraryID]['name'] . ', ' . $ms->identifier->shelfmark;
	if (count($ms->identifier) > 1) $libraryShelfmark .= ', etc.';
	// add common name if available
	if ($ms->identifier->ms_name !='') {
		$heading = $ms->identifier->ms_name . ' (' . $libraryShelfmark . ')';
	}
	else {
		$heading = $libraryShelfmark;
	}
	return $heading;	
}

?>
