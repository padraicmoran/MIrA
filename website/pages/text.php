<?php
// text details

if (file_exists('../data/other/texts.xml')) {
	$xml_texts = simplexml_load_file('../data/other/texts.xml');
	$filter = $xml_texts->xpath('//text[@id="' . $id . '"]');

	if ($filter) {
		$text = $filter[0];
		
		// page title
		templateTop('texts');
		writeBreadcrumb('text', '');
		echo '<h1>';
		if ($text->author != '') echo $text->author . ', ';
		if ($text->title['style'] == 'roman') echo $text->title;
		else echo '<i>' . $text->title . '</i>';
		 echo '</h1>';

		// stable URL
		$link = getLink('text', $id);
		echo '<div class="mb-4 text-secondary small">Stable URL: <a class="text-secondary" href="' . $link . '">https://mira.ie' . $link . '</a></div>';

		// linked data
		$links = $text->xpath('xref');
		if (sizeof($links) > 0) {
			echo '<div class="my-5">';
			echo '<h3>Linked data</h3>';
			foreach ($links as $link) {
				if ($link['type'] == 'viaf') echo '<a href="' . $link . '">VIAF</a><br/>';
				if ($link['type'] == 'wikidata') echo '<a href="' . $link . '">Wikidata</a><br/>';
			}
			echo '</div>';
		}

		// find related manuscripts
		$results = $xml_mss->xpath('manuscript[.//text[@id="' . $id . '"]]');

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
			<a class="text-secondary" href="/data/text/' . $id . '.ttl">TTL</a> |
			<a class="text-secondary" href="/data/text/' . $id . '.jsonld">JSON-LD</a> |
			<a class="text-secondary" href="/data/text/' . $id . '.rdf">RDF</a>
			</div>';

	}
	// no match, return 404
	else {
		http_response_code(404);
		include 'pages/404.php';
	}
}
?>
