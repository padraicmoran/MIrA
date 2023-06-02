<?php
// text details

if (file_exists('data/texts.xml')) {
	$xml_texts = simplexml_load_file('data/texts.xml');
	$filter = $xml_texts->xpath('//text[@id="' . $id . '"]');

	if ($filter) {
		$text = $filter[0];
		
		if ($tidyURLs) $linkBack = '/texts/';
		else $linkBack = '/index.php?page=texts';
		print '<div class="h5 text-secondary"><a href="' . $linkBack . '">Texts</a></div>';

		print '<h2 class="mb-4">' . $text->author . ', <i>' . $text->title . '</i></h2>';

		// find related manuscripts
		$results = $xml_mss->xpath('//text[@id="' . $id . '"]/ancestor::manuscript');

		// sorting
		// cannot sort a SimpleXML object, so transfer top-level objects into an array instead
		$resultsSorted = array();
		foreach($results as $node) {
			$resultsSorted[] = $node;
		}

		// display results
		listMSS($resultsSorted);

		// stable URL
		print '<div class="text-secondary small mt-5">Stable URL: <a class="text-secondary" href="/text/' . $id . '">http://www.mira.ie/texts/' . $id . '</a></div>';
	}
	else {
		// if no match, exit to general list
		require('pages/texts.php');
	}
}
?>
