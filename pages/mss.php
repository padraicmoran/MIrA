<?php

// PROCESS SEARCH 
// Load matches into $results object
//
$searchCat = cleanInput('cat') ?? '';
$searchLib = cleanInput('lib') ?? '';
$results = searchMSS($xml_mss, $search, $searchCat, $searchLib);

// DISPLAY HEADERS
//
if ($search != '') {
	// keyword search
	print '<h2 class="mb-4">Searching for &ldquo;' .  $search . '&rdquo;</h2>';
}
elseif ($searchCat != '') {
	// browse by category 
	if (isset($msCategories[$searchCat])) {
		print '<h2 class="mb-4">Browse category: ';
		writeCategoryButton($searchCat, false);		 
		print '</h2>';
	//	print '<a type="button" class="btn text-light" href="index.php?msid=001">' . $cat . '</a> ';
	}
}
elseif ($searchLib != '') {
	// browse by library
	print '<h2 class="mb-4">Browse by library: ' .  $libraries[$searchLib]['city'] . ', ' .  $libraries[$searchLib]['name'] . '</h2>';
}

else {
	// show all entries
	print '<h2 class="mb-4">All manuscripts</h2>';
}


// check for coherent results, then prepare list
//
if (isset($results)) {

	// check for matches
	$matches = count($results);
	if ($matches == 0) {
		print '<p class="pb-5">No matches found.</p>';
	}
	else {
		
		// display results
		listMSS($results);
	}
}

?>
