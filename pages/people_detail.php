<?php
// person details

if (file_exists('data/people.xml')) {
	$xml_people = simplexml_load_file('data/people.xml');
	$filter = $xml_people->xpath('//person[@id="' . $id . '"]');

	if ($filter) {
		$person = $filter[0];

		if ($tidyURLs) $linkBack = '/people/';
		else $linkBack = '/index.php?page=people';
		print '<div class="h5 text-secondary"></div>';

		print '<h2>';
		print '<a class="text-reset" href="' . $linkBack . '">People</a> ‣ ';
		print $person->firstNames . ' ' . $person->surname . '</h2>';
		print '<p>' . $person->lifetime . '</p>';

		// stable URL
		print '<div class="text-secondary small">Stable URL: <a class="text-secondary" href="/people/' . $id . '">http://www.mira.ie/people/' . $id . '</a></div>';

		// linked data
		$links = $person->xpath('xref');
		if (sizeof($links) > 0) {
			print '<h3 class="mt-5">Linked data</h3>';
			foreach ($links as $link) {
				if ($link['type'] == 'dib') print '<a href="https://www.dib.ie/biography/' . $link . '">Dictionary of Irish Biography</a><br/>';
				if ($link['type'] == 'viaf') print '<a href="' . $link . '">VIAF</a><br/>';
					}
		}

		// find related manuscripts
		$results = $xml_mss->xpath('//person[@id="' . $id . '"]/ancestor::manuscript');

		// sorting
		// cannot sort a SimpleXML object, so transfer top-level objects into an array instead
		$resultsSorted = array();
		foreach($results as $node) {
			$resultsSorted[] = $node;
		}
		// display results
		listMSS($resultsSorted);

		// download
		print '<div class="text-secondary small mt-5">Download <a class="text-secondary" href="/data/people.xml">XML data</a> for people.</div>';

	}
	else {
		// if no match, exit to general list
		require 'pages/people.php';
	}
}

?>