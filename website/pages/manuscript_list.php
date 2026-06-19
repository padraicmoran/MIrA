<?php
// header
templateTop('manuscripts');

// Check for category selection
$searchCat = cleanInput('cat') ?? '';

// Process different search options (or none)
// Keyword search
if ($search != '') {
	writeBreadcrumb('manuscript', 'Search');
	echo '<div class="mt-2 mb-5 px-3 py-2 btn btn-primary rounded-pill">Search: &ldquo;' .  $search . '&rdquo;</div>';
	$results = searchMSS($xml_mss, 'keyword', $search);
}
// Category search
elseif ($searchCat != '' && isset($msCategories[$searchCat])) {
	writeBreadcrumb('manuscript', 'Search');
	echo '<div class="mt-3 mb-5">';
	writeCategoryButton($searchCat, false);
	echo '</div>';
	$results = searchMSS($xml_mss, 'category', $searchCat);
}
// No search; show all entries
else {
	writeBreadcrumb('manuscript', null);
	$results = searchMSS($xml_mss, null, null);
}

// Check for results, prepare list
if (isset($results)) {
	// Count matches
	$matches = count($results);

	// Display results
	if ($matches > 0) {
		listMSS($results, true);
	}
	else {
		// Notify no results
		echo '<h1>Search results</h1>';
		echo '<p class="mt-3">No matches found.</p>';
		echo '<p>Try browsing 
			<a href="/manuscripts">manuscripts</a>, 
			<a href="/libraries">libraries</a>, 
			<a href="/people">people</a>, 
			<a href="/places">places</a>, 
			<a href="/texts">texts</a>.</p>';
	}
}

?>