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




?>
