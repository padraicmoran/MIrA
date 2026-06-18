<?php
/* 
Creates buttons and icons for categories; full lists and individual categories
*/


// list categories 
function listCategories() {
	global $msCategories;
	foreach ($msCategories as $catID => $catName) {
		writeCategoryButton($catID, true);
	}
}

// write a large category button
function writeCategoryButton($catID, $linking) {
	global $msCategories;
	$name = $msCategories[$catID];
	$displayCode = $msCategories[$catID]['displayCode'];
	$type = $msCategories[$catID]['type'];
	$color = $msCategories[$catID]['color'];

	// prepare styles
	if ($type == 'outline') {
		$insertClass = 'btn btn-outline-secondary rounded-pill mb-1 px-3 py-2 ';
		$insertStyle = 'border-color: ' . $color . '; color: ' . $color . '; ';
	}
	else {
		$insertClass = 'btn btn-secondary rounded-pill mb-1 px-3 py-2 ';
		$insertStyle = 'background-color: ' . $color . '; ';
	}
	
	if ($linking) {
		$url = '/manuscripts?cat=' . $catID;
		echo '  <a type="button" href="' . $url . '" class="' . $insertClass . ' text-start " style="' . $insertStyle . ';">' . $name . '</a>';
	}
	else {
		echo '  <button type="button" class="' . $insertClass . ' text-start " style="' . $insertStyle . 'cursor: default; ">' . $name . '</button>';
	}
}

// write a small category icon
function writeCategoryIcon($catID, $linking) {

	global $msCategories;
	$name = $msCategories[$catID];
	$displayCode = $msCategories[$catID]['displayCode'];
	$type = $msCategories[$catID]['type'];
	$color = $msCategories[$catID]['color'];

	if ($type == 'outline') {
		$insertClass = 'btn btn-outline-secondary btn-small rounded-pill mb-1 ';
		$insertStyle = 'border-color: ' . $color . '; color: ' . $color . '; padding: 2px; ';
	}
	else {
		$insertClass = 'btn btn-secondary btn-small rounded-pill mb-1 ';
		$insertStyle = 'background-color: ' . $color . '; padding: 2px; ';
	}
	echo '<a type="button" title="' . $name . '" data-bs-toggle="tooltip" href="/manuscripts?&cat=' . $catID . '" class="' . $insertClass . '" style="' . $insertStyle . '"><b>' . $displayCode . '</b></a> ';
}

?>