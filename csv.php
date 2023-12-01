<?php
require 'includes/application.php';

// get inputs
$page = cleanInput('page') ?? '';
$id = cleanInput('id') ?? '';
$search = cleanInput('search') ?? '';

// content router
if (isset($xml_mss)) {

	if ($page == 'mss') {

		// SEARCH DATA
		// Load matches into $results object
		//
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
		// END SEARCH DATA


	}
	elseif ($page == 'texts') {
		$results = $xml_mss->xpath('//text[@id="' . $id . '"]/ancestor::manuscript');
	}
	elseif ($page == 'people') {
		$results = $xml_mss->xpath('//person[@id="' . $id . '"]/ancestor::manuscript');
	}
	elseif ($page == 'places') {
		$results = $xml_mss->xpath('//place[@id="' . $id . '"]/ancestor::manuscript');
	}
}


// OUTPUT
//
if (isset($results)) {

	// output headers so that the file is downloaded rather than displayed
	header('Content-Type: text/csv; charset=utf-8');
	header('Content-Disposition: attachment; filename=MIrA_' . date('Y-m-d_H-i') . '.csv');

	// create a file pointer connected to the output stream
	$output = fopen('php://output', 'w');

	// output the column headings
	fputcsv($output, 
		array(
			'MIrA ID',
			'Link',
			'City',
			'Library',
			'Shelfmark',
			'MS name',
			'Other units',
			'Contents',
			'Script',
			'Date desc',
			'Term post',
			'Term ante',
			'Origin',
			'Provenance',
			'Notes',
			'Categories'
		)
	);

	foreach ($results as $ms) {

		$libraryID = strval($ms->identifier['libraryID']);	
		fputcsv($output, 
			array(
				$ms['id'],
				'http://mira.ie/' . $ms['id'],
				$libraries[$libraryID]['city'],
				$libraries[$libraryID]['name'],
				$ms->identifier->shelfmark,
				$ms->identifier->ms_name,
				count($ms->identifier) - 1,
				stripTags($ms->description->contents),
				$ms->description->script,
				$ms->history->date_desc,
				$ms->history->term_post,
				$ms->history->term_ante,
				stripTags($ms->history->origin),
				stripTags($ms->history->provenance),
				stripTags($ms->notes->project_notes),
				$ms->notes->categories
			)
		);
	}
}
else {
	print 'Error: no data found.';
}

function stripTags($node) {
	$str = strval($node->asXML());
	return preg_replace('/<[^>]*>/', '', $str);
}

?>
