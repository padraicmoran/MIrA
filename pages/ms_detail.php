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
	print '<div class="text-secondary small">Stable URL: <a class="text-secondary" href="/' . $id . '">http://www.mira.ie/' . $id . '</a></div>';

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
	print '<tr><th colspan="2"><h3 class="mt-2">Identifiers</h3></th></tr>';

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
			print '<td>' . $item->locus_from . '–' . $item->locus_to . '</td>';
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


	print '<tr><th colspan="2" ><h3 class="mt-5">References</h3></th></tr>';

	// XML data distinguished by @type is queried using XPath and results are returned in an array.
	// handeRowArray() checks array contents before sending contents to writeRow(); also allows for multiple rows.
	// Where data needs custom presentation (CLA, Tresmegistos, Thes.), there is just a check for one array item.

	handleRowArray('CLA/ELMSS', $ms->xrefs->xpath('xref[@type="cla"]'), $ms->xrefs->xpath('xref[@type="cla"]/@href'), '', '');
	handleRowArray('Bischoff, <i>SSB</i>', $ms->xrefs->xpath('xref[@type="bischoff_ssb"]'), '', '');
	handleRowArray('Bischoff, <i>Katalog</i>', $ms->xrefs->xpath('xref[@type="bischoff_kat"]'), '', '');
	handleRowArray('Alexander, <i>Insular Manuscripts</i>', $ms->xrefs->xpath('xref[@type="alexander"]'), '', '');
	handleRowArray('Foundations', $ms->xrefs->xpath('link[@type="foundations"]'), $ms->xrefs->xpath('link[@type="foundations"]/@href'), '', '');
	handleRowArray('<abbr title="Descriptive Handlist of Breton Manuscripts">DHBM</abbr>', $ms->xrefs->xpath('xref[@type="dhbm"]'), $ms->xrefs->xpath('xref[@type="dhbm"]/@href'), '');
	handleRowArray('McGurk, <i>Gospel Books</i>', $ms->xrefs->xpath('xref[@type="mcgurk"]'), '', '');
	handleRowArray('Bronner, <i>Verzeichnis</i>', $ms->xrefs->xpath('xref[@type="bronner"]'), '', '');
	if ($ms->xrefs->xpath('xref[@type="thesaurus"]'))  handleRowArray('<i>Thesaurus Palaeohibernicus</i>', parseThesaurusRef($ms->xrefs->xpath('xref[@type="thesaurus"]')[0]), '', '');
	handleRowArray('Tresmegistos', $ms->xrefs->xpath('xref[@type="tresmegistos"]'), $ms->xrefs->xpath('xref[@type="tresmegistos"]'), 'https://www.trismegistos.org/text/');
	
	if ($ms->notes->project_notes != '') {
		print '<tr><th colspan="2" ><h3 class="h3 mt-5">Notes</h3></th></tr>';
		print '<tr><td colspan="2">' . processData($ms->notes->project_notes->asXML()) . '</td></tr>';
	}
	print '</table>';

	print '<div class="mt-5">Full details for the references above can be found on the <a href="/about">About</a> page.</div>';

	// library map
	if ($libraries[$libraryID]['coords'] != '') {
		mapLibraries($filter);
	}

	// download
	print '<div class="text-secondary small mt-5">Download <a class="text-secondary" href="/data/mss/' . sprintf("%03d", $id) . '.xml">XML data</a> for this manuscript.</div>';

}
else {
	print '<!-- MS not found -->';
	require('pages/home.php');
}


// PAGE FUNCTIONS
// if row data is potentially in the form of an array, write a row for each array item
function handleRowArray($header, $value, $link, $linkPrefix) {
	if(is_array($value)) {
		for ($x = 0; $x < count($value); $x++) {
			if (is_array($link)) {
				if (! empty($link)) writeRow($header, $value[$x], $linkPrefix . $link[$x]); 
				else writeRow($header, $value[$x], '');
			}
			else writeRow($header, $value[$x], $linkPrefix . $link);
		}	
	}
	else {
		if ($link != '') writeRow($header, $value, $link, $linkPrefix);
		else writeRow($header, $value, '', '');
	}
}

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

?>