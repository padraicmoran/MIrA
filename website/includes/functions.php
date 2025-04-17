<?php
/* 
Some general functions customised for this website
*/

// handle tidy URL links
function getLink($type, $id) {
	global $tidyURLs;
	$link = '#';
	if ($tidyURLs) {
		if ($type == 'ms') $link = '/' . $id;
		else if ($type == 'texts' || $type == 'people' || $type == 'places') $link = '/' . $type . '/' . $id;
	}
	else {
		if ($type == 'mss' || $type == 'texts' || $type == 'people' || $type == 'places') $link = '/index.php?page=' . $type . '&id=' . $id;
	}
	return $link;
}

function makeMsHeading($ms) {
	global $libraries;

	$libraryID = strval($ms->identifier['libraryID']);
	$heading = $libraries[$libraryID]['city'] . ', ' . $libraries[$libraryID]['name'] . ', ' . $ms->identifier->shelfmark;
	if ($ms->identifier->ms_name !='') $heading = $ms->identifier->ms_name . ': ' . $heading;
	if (count($ms->identifier) > 1) $heading .= ', etc.';
	return $heading;	
}

?>
