<?php
/* 
Some general functions customised for this website
*/

// handle tidy URL links
function getLink($type, $id) {
	if ($type == 'library' || $type == 'manuscript' || $type == 'person' || $type == 'place' || $type == 'text') {
		$link = '/' . $type . '/' . $id;
	}
	else {
		// Link type not identified, return dead link
		$link = '#';	
	}
	return $link;
}

function makeMsHeading($ms) {
	// get shelfmark
	$shelfmark = getShelfmark($ms);
	// add common name if available
	if ($ms->identifier->ms_name !='')	$heading = $ms->identifier->ms_name . ' (' . $shelfmark . ')';
	else 								$heading = $shelfmark;
	return $heading;	
}

function getShelfmark($ms) {
	global $libraries;
	$libraryID = strval($ms->identifier['libraryID']);
	$shelfmark = $libraries[$libraryID]['city'] . ', ' . $libraries[$libraryID]['name'] . ', ' . $ms->identifier->shelfmark;
	if (count($ms->identifier) > 1) $shelfmark .= ', etc.';
	return $shelfmark;
}

function writeBreadcrumb($type, $item) {
	$typePaths = [
		// type ID => [section_url, section_name, item_name]
		'library' 	 => ['/libraries/', 	'Libraries'],
		'manuscript' => ['/manuscripts/',	'Manuscripts'],
		'person' 	 => ['/people/', 		'People'],
		'place' 	 => ['/places/', 		'Places'],
		'text' 		 => ['/texts/', 		'Texts'],
	];
	// if $type is valid, set up breadcrumb
	// $item is null > just show section (no link)
	// $item is empty > show section with link
	// $item has content > show section with link and content
	if (isset($typePaths[$type])) {
		echo '<nav style="--bs-breadcrumb-divider: \'‣\';" aria-label="breadcrumb" class="small">';
		echo '  <ol class="breadcrumb bg-light mb-1">';
		echo '    <li class="breadcrumb-item"><a href="/">Home</a></li>';

		// if item is null, show path to a section (but no link on section)
		if (is_null($item)) {
			echo '    <li class="breadcrumb-item active" aria-current="page">' . $typePaths[$type][1] . '</li>';
		}
		// else, show section (linked) and the item if not empty
		else {
			echo '    <li class="breadcrumb-item active"><a href="' . $typePaths[$type][0] . '">' . $typePaths[$type][1] . '</a></li>';
			if ($item <> '') echo '    <li class="breadcrumb-item" aria-current="page">' . $item . '</li>';
		}
		echo '  </ol>';
		echo '</nav>';
	}
}

?>
