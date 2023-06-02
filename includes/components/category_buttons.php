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

	print '<div class="btn-group" role="group">';

	// prepare styles
	if ($type == 'outline') {
		$insertClass = 'btn btn-outline-secondary mb-1 ';
		$insertStyle = 'border-color: ' . $color . '; color: ' . $color . '; ';
	}
	else {
		$insertClass = 'btn btn-secondary mb-1 ';
		$insertStyle = 'background-color: ' . $color . '; ';
	}
	
	if ($linking) {
		$url = '/index.php?page=mss&cat=' . $catID;
		print '  <a type="button" href="' . $url . '" class="' . $insertClass . 'px-0" style="' . $insertStyle . '; width: 45px; "><b>' . $displayCode . '</b></a>';
		print '  <a type="button" href="' . $url . '" class="' . $insertClass . ' text-start " style="' . $insertStyle . '; width: 230px; ">' . $name . '</a>';
	}
	else {
		print '  <button type="button" class="' . $insertClass . 'px-0" style="' . $insertStyle . 'width: 45px; cursor: default; "><b>' . $displayCode . '</b></button>';
		print '  <button type="button" class="' . $insertClass . ' text-start " style="' . $insertStyle . 'width: 230px; cursor: default; ">' . $name . '</button>';
	}
	print '</div> ' . "\n";
}

// write a small category icon
function writeCategoryIcon($catID, $linking) {

	global $msCategories;
	$name = $msCategories[$catID];
	$displayCode = $msCategories[$catID]['displayCode'];
	$type = $msCategories[$catID]['type'];
	$color = $msCategories[$catID]['color'];

	if ($type == 'outline') {
		$insertClass = 'btn btn-outline-	secondary btn-small mb-1 ';
		$insertStyle = 'border-color: ' . $color . '; color: ' . $color . '; padding: 2px; ';
	}
	else {
		$insertClass = 'btn btn-secondary btn-small mb-1 ';
		$insertStyle = 'background-color: ' . $color . '; padding: 2px; ';
	}
	print '<a type="button" title="' . $name . '" data-bs-toggle="tooltip" href="/index.php?page=mss&cat=' . $catID . '" class="' . $insertClass . '" style="' . $insertStyle . '"><b>' . $displayCode . '</b></a> ';
}

?>