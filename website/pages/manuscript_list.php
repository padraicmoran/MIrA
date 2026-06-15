<?php

// PROCESS SEARCH 
// Load matches into $results object
//
$searchCat = cleanInput('cat') ?? '';

// display headers
//
echo '<h2>Manuscripts</h2>';
if ($search != '') {
	// keyword search
	echo '<div class="d-inline-flex my-2 px-3 py-2 text-light small bg-mira rounded">Search: &ldquo;' .  $search . '&rdquo;</div>';
	$results = searchMSS($xml_mss, 'keyword', $search);
}
elseif ($searchCat != '') {
	// browse by category 
	if (isset($msCategories[$searchCat])) {
		writeCategoryButton($searchCat, false);		 
		$results = searchMSS($xml_mss, 'category', $searchCat);
	}
}
else {
	// show all entries
	$results = searchMSS($xml_mss, null, null);
}


// check for coherent results, then prepare list
//
if (isset($results)) {

	// check for matches
	$matches = count($results);
	if ($matches == 0) {
		echo '<p class="mt-3 pb-5">No matches found.</p>';
	}
	else {
		
		// display results
		listMSS($results);
	}
}

?>