<?php
// person details

if (file_exists('../data/other/libraries.xml')) {
	$xml_libraries = simplexml_load_file('../data/other/libraries.xml');
	$filter = $xml_libraries->xpath('//library[@id="' . $id . '"]');

	if ($filter) {
		$library = $filter[0];

		if ($tidyURLs) $linkBack = '/libraries/';
		else $linkBack = '/index.php?page=libraries';
		echo '<div class="h5 text-secondary"></div>';

		echo '<h2>';
		echo '<a class="text-reset" href="' . $linkBack . '">Libraries</a> ‣ ';
		echo $library->city . ', ' . $library->name . '</h2>';

		// stable URL
		$link = getLink('library', $id);
		echo '<div class="mb-5 text-secondary small">Stable URL: <a class="text-secondary" href="' . $link . '">https://mira.ie' . $link . '</a></div>';

		// find related manuscripts
		$results = $xml_mss->xpath('manuscript[identifier[@libraryID="' . $id . '"]]');

		// sorting
		// cannot sort a SimpleXML object, so transfer top-level objects into an array instead
		$resultsSorted = array();
		foreach($results as $node) {
			$resultsSorted[] = $node;
		}
		// display results
		listMSS($resultsSorted);

		// download
		echo '<div class="text-secondary small mt-5">Download <a class="text-secondary" href="/data/other/libraries.xml">XML data</a> for libraries.</div>';

	}
	else {
		// if no match, exit to general list
		require 'pages/library_list.php';
	}
}

?>