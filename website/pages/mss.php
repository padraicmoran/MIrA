<?php

// PROCESS SEARCH 
// Load matches into $results object
//
$searchCat = cleanInput('cat') ?? '';
$searchLib = cleanInput('lib') ?? '';

// display headers
//
if ($search != '') {
	// keyword search
	print '<h2>Searching for &ldquo;' .  $search . '&rdquo;</h2>';
	$results = searchMSS($xml_mss, 'keyword', $search);
}
elseif ($searchCat != '') {
	// browse by category 
	if (isset($msCategories[$searchCat])) {
		print '<h2>Browse category: ';
		writeCategoryButton($searchCat, false);		 
		print '</h2>';
		$results = searchMSS($xml_mss, 'category', $searchCat);
	}
}
elseif ($searchLib != '') {
	// browse by library
	print '<h2>Browse by library: ' .  $libraries[$searchLib]['city'] . ', ' .  $libraries[$searchLib]['name'] . '</h2>';
	$results = searchMSS($xml_mss, 'library', $searchLib);
}
else {
	// show all entries
	print '<h2>All manuscripts</h2>';
	$results = searchMSS($xml_mss, null, null);
}


// check for coherent results, then prepare list
//
if (isset($results)) {

	// check for matches
	$matches = count($results);
	if ($matches == 0) {
		print '<p class="mt-3 pb-5">No matches found.</p>';
	}
	else {
		
		// display results
		listMSS($results);
	}
}

?>