<?php

$filter = $xml_mss->xpath('//manuscript[@id="' . $id . '"]');
if ($filter) {

	// take first MS result only
	$ms = $filter[0];
	
	//
	// HEADERS 
	//
	if ($tidyURLs) $linkBack = '/mss/';
	else $linkBack = '/index.php?page=mss';

	$heading = makeMsHeading($ms);
	print '<h1 class="h2">MIrA ' . $id . ': ' . $heading . '</h1>';

	// stable URL
	print '<div class="text-secondary small">Stable URL: <a class="text-secondary" href="/' . $id . '">http://mira.ie/' . $id . '</a></div>';

	// write categories
	if ($ms->notes['categories'] != '') {
		print '<div class="my-4">';
		$theseCats = explode(' ', $ms->notes['categories']);
		foreach ($theseCats as $cat) {
			$cat = str_replace('#', '', $cat);
			if (isset($msCategories[$cat])) writeCategoryButton($cat, true);
		} 
		print '</div>';
	}
	else {
		print '<div class="mt-3 alert alert-warning">This is a legacy record for a manuscript that has now been excluded from the database.</div>';
	}




	// Mirador viewer
	$iiifLinks = $ms->xpath('identifier/link[@type="iiif"]');
	if ($iiifLinks) mirador($iiifLinks);

	// 
	// TABLE OF DETAILS
	//
	print '<table class="table">';

	// identifiers
	print '<tr><th colspan="2"><h3 class="mt-5">Identifiers</h3></th></tr>';

	// list parts (perhaps more than one)
	$identifierCount = count($ms->identifier);
	for ($n = 0; $n < $identifierCount; $n ++) {
		$libraryID = strval($ms->identifier[$n]['libraryID']);	
		$unit = $ms->identifier[$n]["unit"];

		if ($identifierCount > 1) print '<tr><td colspan="2"><h4 class="h6 mt-3 mb-0">UNIT ' . $unit . '</h4></td></tr>';

		writeRow('Country', $libraries[$libraryID]['country'], '', '');
		writeRow('Location', $libraries[$libraryID]['city'] . ', ' . $libraries[$libraryID]['name'], '/index.php?page=mss&lib=' . $libraryID);

		$shelfmarkLink = $ms->identifier[$n]->shelfmark;
		if ($ms->identifier[$n]->xpath('link[@type="images"]')) {
			$shelfmarkLink .= ' <span class="ms-5 small">[<a target="_blank" href="' . $ms->identifier[$n]->xpath('link[@type="images"]')[0] . '">library images</a>]<span>';
		}
		writeRow('Shelfmark', $shelfmarkLink, '');
		writeRow('MS name', $ms->identifier[$n]->ms_name, '');
		
		$miraRef = $id; 
		if ($unit <> '') $miraRef .= '.' . $unit;
		writeRow('MIrA number', $miraRef, '');
	}

	// description, incl. contents
	print '<tr><th colspan="2"><h3 class="h3 mt-5">Description</h3></th></tr>';
	writeRow('MS type', $ms->description->type, '');
	writeRow('No. of folios', $ms->description->folios, '');
	writeRow('Page height (cm)', $ms->description->page_h, '');
	writeRow('Page width (cm)', $ms->description->page_w, '');
	writeRow('Columns', $ms->description->cols, '');
	writeRow('Lines', $ms->description->lines . indicativeLineHeight($ms->description->lines, $ms->description->page_h), '');
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
			print '<td>' . $item->locus . '</td>';
			print '<td>' . $item->author . ', <i>' . $item->title . '</i> ';
			if ($item->note) print '(' . $item->note . ')';
			print '</td>';
			print '<tr>';
		}
		print '</table>';
		print '</td></tr>';
	}

	print '<tr><th colspan="2"><h3 class="h3 mt-5">History</h3></th></tr>';
	writeRow('Dating', $ms->history->date_desc . ' (' . $ms->history->term_post . '–' . $ms->history->term_ante . ')', '');
	writeRow('Origin', processData($ms->history->origin->asXML()), '');
	writeRow('Provenance', processData($ms->history->provenance->asXML()), '');


	print '<tr><th colspan="2"><h3 class="mt-5">Resources</h3></th></tr>';
	writeResources('Catalogue entries/identifiers', $ms->refs->xpath('canonical'));
	writeResources('Links', $ms->refs->xpath('link'));
	writeResources('Other resources', $ms->refs->xpath('bibl'));


	if ($ms->notes != '') {
		print '<tr><th colspan="2"><h3 class="h3 mt-5">Notes</h3></th></tr>';
		print '<tr><td colspan="2">' . processData($ms->notes->asXML()) . '</td></tr>';
	}


	print '<tr><th colspan="2"><h3 class="h3 mt-5">References</h3></th></tr>';
	print '<tr><td colspan="2">';
	// make list of reference IDs for this MS entry
	$refList = array();
	$refs = $ms->refs->xpath('.//*/@corresp');
	foreach($refs as $ref) {
		array_push($refList, substr(strval($ref), 1));
	}
	//	cycle through bibliography and print entries with matching IDs
	if (file_exists('../data/other/bibliography.xml')) {
		$xml_bibl = simplexml_load_file('../data/other/bibliography.xml');
		print '<ul class="list-unstyled small">';
		foreach($xml_bibl as $bibl) {
			$biblID = strval($bibl['id']);
			if (in_array($biblID, $refList)) {
				print '<li class="mb-2">' . $bibl->asXML() . '</li>';
			}
		}
		print '</ul>';
	}
	
	
	print '</td></tr>';
	print '</table>';

	// library map
	if ($libraries[$libraryID]['coords'] != '') {
		mapLibraries($filter);
	}

	// download
	if ($tidyURLs) $xmlURL = '/' . sprintf("%03d", $id) . '/xml';
	else $xmlURL = '../data/mss/' . sprintf("%03d", $id) . '.xml';
	print '<div class="text-secondary small mt-5">Download <a class="text-secondary" href="' . $xmlURL . '">XML data</a> for this manuscript.</div>';

}
else {
	print '<!-- MS not found -->';
	require 'pages/home.php';
}


// PAGE FUNCTIONS
// write table row with left cell as header; add a link if supplied
function writeRow($header, $value, $link) {
	if ($value == '' && $link != '') $value = '[external link]';
	if ($value != '') {
		$target = '';
		if (substr($link, 0, 4) == 'http') $target = '_blank';
		
		if ($link != '') print '<tr><th width="400">' . $header . '</th><td><a href="' . $link . '" target="' . $target . '">' . $value . '</a></td></tr>';
		else print '<tr><th width="400">' . $header . '</th><td>' . $value . '</td></tr>';
	}
}

// write references
function writeResources($heading, $node) {
	$refs = $node;
	if ($refs) {
		print '<tr><th>' . $heading .'</th><td>';
		print '<ul class="list-unstyled">';
		foreach ($refs as $ref) {
			$text = $ref->asXML();
			if ($ref['href'] <> '') $text = '<a target="_blank" href="' . $ref['href'] . '">' . $text . '</a>';
			print '<li>' . $text . '</li>';
		}
		print '</ul>';
		print '</td></tr>';
	}	
}

// special handling of Thesaurus Palaeohibernicus refs 
function parseThesaurusRef($ref) {
	$urlBase = array(
		'1'=>'https://archive.org/details/thesauruspalaeo01stok/page/',
		'2'=>'https://archive.org/details/thesauruspalaeo02stok/page/'
	);

	$x = '';
	$refs = explode(', ', $ref);		// handle multiple Thes. refs
	foreach ($refs as $r) {
		if ($x != '') $x .= ', ';		// add a separator if necessary

		$parts = preg_split('/[\.\-–]/', $r);
		if (count($parts) >= 2 && ($parts[0] == '1' || $parts[0] == '2')) {			// if there appears to be a volume and page
			$x .= '<a target="_blank" href="' . $urlBase[$parts[0]] . $parts[1] . '">'  . $r . '</a>';
		}
		else $x .= $r;						// just return the plain ref if something doesn't work
	}
	return $x;
}

// replace XML cross-references (data) with HTML links (application)
function processData($str) {
	global $tidyURLs;

	$str = preg_replace('/<ms id="([0-9]*)">/', '<a href="\1">', $str);
	$str = preg_replace('/<\/ms>/', '</a>', $str);

	if ($tidyURLs) {
		$str = preg_replace('/<person id="([a-z0-9_\-]*)">/', '<a href="/people/\1">', $str);
		$str = preg_replace('/<place id="([a-z0-9_\-]*)">/', '<a href="/places/\1">', $str);
		$str = preg_replace('/<text id="([a-z0-9_\-]*)">/', '<a href="/texts/\1">', $str);
	}
	else {
		$str = preg_replace('/<person id="([a-z0-9_\-]*)">/', '<a href="index.php?page=people&id=\1">', $str);
		$str = preg_replace('/<place id="([a-z0-9_\-]*)">/', '<a href="index.php?page=places&id=\1">', $str);
		$str = preg_replace('/<text id="([a-z0-9_\-]*)">/', '<a href="index.php?page=texts&id=\1">', $str);
	}
	
	$str = preg_replace('/<\/person>/', '</a>', $str);
	$str = preg_replace('/<\/place>/', '</a>', $str);
	$str = preg_replace('/<\/text>/', '</a>', $str);

	return $str;
}

// return a string with the line height in cm
function indicativeLineHeight($lines, $pageHeight) {
	$minLines = (int)$lines['from'];
	$maxLines = (int)$lines['to'];
	$avgLines = round(($maxLines + $minLines) / 2);

	if ($avgLines > 0 && $pageHeight > 0) {
		$lineHeight = round($pageHeight / ($avgLines + 4), 2);	// assume 4 lines of margin
		if ($avgLines > $minLines) {
			$avgNote = 'average ' . $avgLines . ' lines, ';
		}
		else {
			$avgNote = '';
		}
		return ' &nbsp; (' . $avgNote . 'approx. height: ' . $lineHeight . ' cm)';
	}
	else return '';
}
?>