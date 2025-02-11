<?php
// text details

if (file_exists('data/texts.xml')) {
	$xml_texts = simplexml_load_file('data/texts.xml');
	$filter = $xml_texts->xpath('//text[@id="' . $id . '"]');

	if ($filter) {
		$text = $filter[0];
		
		if ($tidyURLs) $linkBack = '/texts/';
		else $linkBack = '/index.php?page=texts';

		// page title
		print '<h2>';
		print '<a class="text-reset" href="' . $linkBack . '">Texts</a> ‣ ';
		if ($text->author != '') print $text->author . ', ';
		if ($text->title['style'] == 'roman') print $text->title;
		else print '<i>' . $text->title . '</i>';
		 print '</h2>';

		// stable URL
		print '<div class="text-secondary small">Stable URL: <a class="text-secondary" href="/text/' . $id . '">http://mira.ie/texts/' . $id . '</a></div>';

		// linked data
		$links = $text->xpath('xref');
		if (sizeof($links) > 0) {
			print '<h3 class="mt-5">Linked data</h3>';
			foreach ($links as $link) {
				if ($link['type'] == 'viaf') print '<a href="' . $link . '">VIAF</a><br/>';
				if ($link['type'] == 'wikidata') print '<a href="' . $link . '">Wikidata</a><br/>';
			}
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
		listMSS($resultsSorted);

		// download
		print '<div class="text-secondary small mt-5">Download <a class="text-secondary" href="/data/texts.xml">XML data</a> for texts.</div>';

	}
	else {
		// if no match, exit to general list
		require 'pages/texts.php';
	}
}
?>
