<?php
// person details

if (file_exists('data/people.xml')) {
	$xml_people = simplexml_load_file('data/people.xml');
	$filter = $xml_people->xpath('//person[@id="' . $id . '"]');

	if ($filter) {
		$person = $filter[0];

		if ($tidyURLs) $linkBack = '/people/';
		else $linkBack = '/index.php?page=people';
		print '<div class="h5 text-secondary"><a href="' . $linkBack . '">People</a></div>';

		print '<h2>' . $person->firstNames . ' ' . $person->surname . '</h2>';
		print '<p>' . $person->lifetime . '</p>';
		

		// find related manuscripts
		$results = $xml_mss->xpath('//person[@id="' . $id . '"]/ancestor::manuscript');

		// sorting
		// cannot sort a SimpleXML object, so transfer top-level objects into an array instead
		$resultsSorted = array();
		foreach($results as $node) {
			$resultsSorted[] = $node;
		}
		// display results
		print '<h3 class="mt-5">Associated manuscripts</h4>';
		listMSS($resultsSorted);
		
		// stable URL
		print '<div class="text-secondary small mt-5">Stable URL: <a class="text-secondary" href="/people/' . $id . '">http://www.mira.ie/people/' . $id . '</a></div>';
	}
	else {
		// if no match, exit to general list
		require('pages/people.php');
	}
}

?>

