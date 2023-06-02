<?php

// PROCESS SEARCH 
// Load matches into $results object
//
$sort = cleanInput('sort') ?? '';
$searchCat = cleanInput('cat') ?? '';
$searchLib = cleanInput('lib') ?? '';

if ($search != '') {
	// keyword search

	// pre-search: check library information for keyword; include any matching IDs in overall search
	$xPathLibraries = '';
	foreach ($libraries as $lib) {
		if (strpos($lib['searchIndex'], $search) !== false) $xPathLibraries .= '| //identifier[@libraryID="' . $lib['id'] . '"]//ancestor::manuscript ';
	}

	//	$results = $xml_mss->xpath('//*[contains(text(),"' . $search . '")]//ancestor::manuscript');
	//	hack for case-sensitivity (needed for XPath v1)
	$results = $xml_mss->xpath('
		//*[contains(translate(text(), "ABCDEFGHIJKLMNOPQRSTUVWXYZäëïöüÄËÏÖÜáéíóúÁÉÍÓÚàèìòùÀÈÌÒÙ", "abcdefghijklmnopqrstuvwxyzaeiouaeiouaeiouaeiouaeiouaeiou"),"' . strtolower($search) . '")][not(self::coords|self::link|self::private_notes)]
		//ancestor::manuscript
		' . $xPathLibraries);
}
elseif ($searchCat != '') {
	// browse by category 
	if (isset($msCategories[$searchCat])) {
		$results = $xml_mss->xpath('//manuscript/notes/categories[contains(text(), \'' . $searchCat . '\')]/ancestor::manuscript');
	}
}
elseif ($searchLib != '') {
	// browse by library
	$results = $xml_mss->xpath('//identifier[@libraryID="' . $searchLib . '"]/ancestor::manuscript');
}
else {
	// show all entries
	$results = $xml_mss->manuscript;
}
// END PROCESS SEARCH 


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
		// sorting
		// cannot sort a SimpleXML object, so transfer top-level objects into an array instead
		$resultsSorted = array();
		foreach($results as $node) {
			$resultsSorted[] = $node;
		}
		
		// default sort is by city, library, shelfmark; change for other options below
		if ($sort == '') usort($resultsSorted, 'sortLocation');
		elseif ($sort == 'script') usort($resultsSorted, 'sortScript');
		elseif ($sort == 'date') usort($resultsSorted, 'sortDate');
//		elseif ($sort == 'origin') usort($resultsSorted, 'sortOrigin');
//		elseif ($sort == 'prov') usort($resultsSorted, 'sortProv');


		// total and results and sort form
		print '<form id="sortForm" action="/index.php">';
		print '<input type="hidden" name="page" value="mss" />';
		if ($search != '') print '<input type="hidden" name="search" value="' . $search . '" />';
		if ($searchCat != '') print '<input type="hidden" name="cat" value="' . $searchCat . '" />';
		print '<label for="sort" class="form-label">Sort by</label> ';
		print '<select name="sort" class="" onchange="sortForm.submit(); ">';
		writeOption('', 'location', $sort);
		writeOption('script', 'script', $sort);
		writeOption('date', 'date', $sort);
//		writeOption('origin', 'origin', $sort);
//		writeOption('prov', 'provenance', $sort);
		print '</select>';
		print '</form>';

		// display results
		listMSS($resultsSorted);
	}
}



// sorting functions
function sortScript($a, $b) {
	return strnatcmp($a->description->script, $b->description->script);
}
function sortDate($a, $b) {
	return strnatcmp($a->history->term_post, $b->history->term_post);
}
function sortLocation($a, $b) {
	return strnatcmp($a->identifier['libraryID'], $b->identifier['libraryID']);
}
/*
function sortOrigin($a, $b) {
	return strnatcmp($a->history->origin, $b->history->origin);
}
function sortProv($a, $b) {
	return strnatcmp($a->history->provenance, $b->history->provenance);
}
*/
?>
