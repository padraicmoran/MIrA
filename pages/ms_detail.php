<?php

$filter = $xml_mss->xpath('//manuscript[@id="' . $id . '"]');
if ($filter) {

	// take first MS result only
	$ms = $filter[0];
	$identifierCount = count($ms->identifier);

	//
	// HEADERS 
	//
	if ($tidyURLs) $linkBack = '/mss/';
	else $linkBack = '/index.php?page=mss';

	$libraryID = strval($ms->identifier['libraryID']);
	$heading = $libraries[$libraryID]['city'] . ', ' . $libraries[$libraryID]['name'] . ', ' . $ms->identifier->shelfmark;
	if ($ms->identifier->ms_name !='') $heading = $ms->identifier->ms_name . ': ' . $heading;
	if ($identifierCount > 1) $heading .= ', etc.';
	print '<h2>' . $heading . '</h2>';

	// write categories
	if ($ms->notes->categories != '') {
		print '<div class="my-4">';
		$theseCats = explode(';', $ms->notes->categories);
		foreach ($theseCats as $cat) {
			if (isset($msCategories[$cat])) writeCategoryButton($cat, true);
		} 
		print '</div>';
	}

	// Mirador viewer
	$iiifLinks = $ms->xpath('identifier/link[@type="iiif"]');
	if ($iiifLinks) mirador($iiifLinks);

	// 
	// TABLE OF DETAILS
	//
	print '<table class="table">';

	// identifiers
	print '<tr><th colspan="2"><h3 class="h3 mt-2">Identifiers</h3></th></tr>';

	// list parts (perhaps more than one)
	for ($n = 0; $n < $identifierCount; $n ++) {
		$libraryID = strval($ms->identifier[$n]['libraryID']);	

		if ($identifierCount > 1) print '<tr><td colspan="2"><h4 class="h6 mt-3 mb-0">UNIT ' . $ms->identifier[$n]["unit"] . '</h4></td></tr>';

		writeRow('Country', $libraries[$libraryID]['country'], '');
		writeRow('Location', $libraries[$libraryID]['city'] . ', ' . $libraries[$libraryID]['name'], '/index.php?page=mss&lib=' . $libraryID);

		$shelfmarkLink = $ms->identifier[$n]->shelfmark;
		if ($ms->identifier[$n]->xpath('link[@type="images"]')) {
			$shelfmarkLink .= ' <span class="ms-5 small">[<a target="_blank" href="' . $ms->identifier[$n]->xpath('link[@type="images"]')[0] . '">library images</a>]<span>';
		}
		writeRow('Shelfmark', $shelfmarkLink, '');
		writeRow('MS name', $ms->identifier[$n]->ms_name, '');
	}

	// description, incl. contents
	print '<tr><th colspan="2" ><h3 class="h3 mt-5">Description</h3></th></tr>';
	writeRow('MS type', $ms->description->type, '');
	writeRow('No. of folios', $ms->description->folios, '');
	writeRow('Page height (mm)', $ms->description->page_h, '');
	writeRow('Page width (mm)', $ms->description->page_w, '');
	writeRow('Script', $ms->description->script, '');

	// handle contents
	// for summary only
	if (! $ms->description->contents->msItem) writeRow('Contents', processData($ms->description->contents->asXML()), '');
	else {
		// detailed contents
		print '<tr><th>Contents</th><td>';
		print '<table class="table table-sm">';
		foreach ($ms->description->contents->msItem as $item) {
			print '<tr>';
			print '<th style="padding-right: 20px; font-weight: normal; font-style: italic; ">' . $item->locus . '</th>';
			print '<td>' . $item->author . ', <i>' . $item->title . '</i></td>';
			print '<tr>';
		}
		print '</table>';
		print '</td></tr>';
	}

	print '<tr><th colspan="2" ><h3 class="h3 mt-5">History</h3></th></tr>';
	writeRow('Dating', $ms->history->date_desc . ' (' . $ms->history->term_post . '–' . $ms->history->term_ante . ')', '');
	writeRow('Origin', processData($ms->history->origin->asXML()), '');
	writeRow('Provenance', processData($ms->history->provenance->asXML()), '');


	print '<tr><th colspan="2" ><h3 class="h3 mt-5">References</h3></th></tr>';

	// XML data distinguished by @type is queried using XPath and results are returned in an array.
	// handeRowArray() checks array contents before sending contents to writeRow(); also allows for multiple rows.
	// Where data needs custom presentation (CLA, Tresmegistos, Thes.), there is just a check for one array item.

	handleRowArray('Alexander, <i>Insular Manuscripts</i>', $ms->xrefs->xpath('xref[@type="alexander"]'), '');
	handleRowArray('Bronner, <i>Verzeichnis</i>', $ms->xrefs->xpath('xref[@type="bronner"]'), '');
	handleRowArray('<abbr title="Descriptive Handlist of Breton Manuscripts">DHBM</abbr>', $ms->xrefs->xpath('xref[@type="dhbm"]'), $ms->xrefs->xpath('xref[@type="dhbm"]/@href'));
	handleRowArray('Bischoff, <i>SSB</i>', $ms->xrefs->xpath('xref[@type="bischoff_ssb"]'), '');
	handleRowArray('Bischoff, <i>Katalog</i>', $ms->xrefs->xpath('xref[@type="bischoff_kat"]'), '');
	handleRowArray('CLA/ELMSS', $ms->xrefs->xpath('xref[@type="cla"]'), $ms->xrefs->xpath('xref[@type="cla"]/@href'));
	handleRowArray('Foundations', $ms->xrefs->xpath('link[@type="foundations"]'), $ms->xrefs->xpath('link[@type="foundations"]/@href'));
	if ($ms->xrefs->xpath('xref[@type="thesaurus"]')) handleRowArray('<i>Thesaurus Pal.</i>', parseThesaurusRef($ms->xrefs->xpath('xref[@type="thesaurus"]')[0]), '');
	if ($ms->xrefs->xpath('xref[@type="tresmegistos"]')) writeRow('Tresmegistos', $ms->xrefs->xpath('xref[@type="tresmegistos"]')[0], str_replace('TM ', 'https://www.trismegistos.org/text/', $ms->xrefs->xpath('xref[@type="tresmegistos"]')[0]));
	
	if ($ms->notes->project_notes != '') {
		print '<tr><th colspan="2" ><h3 class="h3 mt-5">Notes</h3></th></tr>';
		print '<tr><td colspan="2">' . processData($ms->notes->project_notes->asXML()) . '</td></tr>';
	}
	print '</table>';

	// library map
	if ($libraries[$libraryID]['coords'] != '') {
		print '<h3 class="h3 mt-5">Library ' . switchSgPl($identifierCount, 'location', 'locations') . '</h3> ';
		mapLibraries($filter);
	}

		// stable URL
	print '<div class="text-secondary small mt-5">Stable URL: <a class="text-secondary" href="/' . $id . '">http://www.mira.ie/' . $id . '</a></div>';
	
}
else {
	print '<!-- MS not found -->';
	require('pages/home.php');
}

?>
