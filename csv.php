<?php
require 'includes/application.php';

// get inputs
$page = cleanInput('page') ?? '';
$id = cleanInput('id') ?? '';
$search = cleanInput('search') ?? '';

// content router
if (isset($xml_mss)) {

	if ($page == 'mss') {

		$searchCat = cleanInput('cat') ?? '';
		$searchLib = cleanInput('lib') ?? '';

		if ($search != '') {
			// keyword search
			$results = searchMSS($xml_mss, 'keyword', $search);
		}
		elseif ($searchCat != '') {
			// browse by category 
			if (isset($msCategories[$searchCat])) {
				$results = searchMSS($xml_mss, 'category', $searchCat);
			}
		}
		elseif ($searchLib != '') {
			// browse by library
			$results = searchMSS($xml_mss, 'library', $searchLib);
		}
		else {
			// show all entries
			$results = searchMSS($xml_mss, null, null);
		}
	}
	elseif ($page == 'texts') {
		$results = $xml_mss->xpath('manuscript[//text[@id="' . $id . '"]]');
	}
	elseif ($page == 'people') {
		$results = $xml_mss->xpath('manuscript[//person[@id="' . $id . '"]]');
	}
	elseif ($page == 'places') {
		$results = $xml_mss->xpath('manuscript[//place[@id="' . $id . '"]]');
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
		// find how many MS units
		$identifierCount = count($ms->identifier);
		for ($n = 0; $n < $identifierCount; $n ++) {

			// get ID; add unit number if more than one unit
			$miraRef = $ms['id']; 
			$unit = $ms->identifier[$n]['unit'];
			if ($unit <> '') $miraRef .= '.' . $unit;

			// get library ID
			$libraryID = strval($ms->identifier[$n]['libraryID']);	

			// prepare contents
			if ($ms->description->contents->summary) $contents = $ms->description->contents->summary;
			else $contents = $ms->description->contents;

			fputcsv($output, 
				array(
					$miraRef,
					'http://mira.ie/' . $ms['id'],
					$libraries[$libraryID]['city'],
					$libraries[$libraryID]['name'],
					$ms->identifier[$n]->shelfmark,
					$ms->identifier[$n]->ms_name,
					stripTagsInNode($contents),
					$ms->description->script,
					$ms->history->date_desc,
					$ms->history->term_post,
					$ms->history->term_ante,
					stripTagsInNode($ms->history->origin),
					stripTagsInNode($ms->history->provenance),
					stripTagsInNode($ms->notes),
					$ms->notes['categories']
				)
			);
		}
	}
}
else {
	print 'Error: no data found.';
}

function stripTagsInNode($node) {
	$str = strval($node->asXML());
	return preg_replace('/<[^>]*>/', '', $str);
}

?>