<?php
// person details

if (file_exists('../data/other/people.xml')) {
	$xml_people = simplexml_load_file('../data/other/people.xml');
	$filter = $xml_people->xpath('//person[@id="' . $id . '"]');

	if ($filter) {
		$person = $filter[0];

		// header
		templateTop($nav, 'people');
		writeBreadcrumb('person', '');
		echo '<h1>' . $person->firstNames . ' ' . $person->surname . '</h1>';
		if ($person->lifetime) echo '<p>' . $person->lifetime . '</p>';

		// stable URL
		$link = getLink('person', $id);
		echo '<div class="mb-4 text-secondary small mb-5">Stable URL: <a class="text-secondary" href="' . $link . '">https://mira.ie' . $link . '</a></div>';

		// linked data
		$links = $person->xpath('xref');
		if (sizeof($links) > 0) {
			echo '<div class="my-5">';
			echo '<h3>Linked data</h3>';
			foreach ($links as $link) {
				if ($link['type'] == 'biblissima') echo '<a href="' . $link . '">Biblissima</a><br/>';
				if ($link['type'] == 'dib') echo '<a href="https://www.dib.ie/biography/' . $link . '">Dictionary of Irish Biography</a><br/>';
				if ($link['type'] == 'viaf') echo '<a href="' . $link . '">VIAF</a><br/>';
				if ($link['type'] == 'wikidata') echo '<a href="' . $link . '">Wikidata</a><br/>';
			}
			echo '</div>';
		}

		// find related manuscripts
		$results = $xml_mss->xpath('manuscript[.//person[@id="' . $id . '"]]');

		// sorting
		// cannot sort a SimpleXML object, so transfer top-level objects into an array instead
		$resultsSorted = array();
		foreach($results as $node) {
			$resultsSorted[] = $node;
		}
		// display results
		listMSS($resultsSorted, false);

		// download
		echo '<div class="text-secondary small mt-5">Download:
			<a class="text-secondary" href="/data/person/' . $id . '.ttl">TTL</a> |
			<a class="text-secondary" href="/data/person/' . $id . '.jsonld">JSON-LD</a> |
			<a class="text-secondary" href="/data/person/' . $id . '.rdf">RDF</a>
			</div>';

	}
	// no match, return 404
	else {
		http_response_code(404);
		include 'pages/static/404.php';
	}
}

?>
