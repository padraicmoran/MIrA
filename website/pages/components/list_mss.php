<?php
/* 
Results page for MS searches
*/

function listMSS($results) {
	global $page, $id, $search, $searchCat, $searchLib;
	global $libraries, $msCategories, $tidyURLs;

	// sort results
	// cannot sort a SimpleXML object, so transfer top-level objects into an array instead
	$sort = cleanInput('sort') ?? '';
	$resultsSorted = array();
	foreach($results as $node) {
		$resultsSorted[] = $node;
	}
	
	// default sort is by city, library, shelfmark; change for other options below
	usort($resultsSorted, 'sortShelfmarkIndexer');
	usort($resultsSorted, 'sortShelfmark');
	if ($sort == '') usort($resultsSorted, 'sortLocation');
	elseif ($sort == 'script') usort($resultsSorted, 'sortScript');
	elseif ($sort == 'date') usort($resultsSorted, 'sortDate');
//		elseif ($sort == 'origin') usort($resultsSorted, 'sortOrigin');
//		elseif ($sort == 'prov') usort($resultsSorted, 'sortProv');

	// total and results and sort form
	print '<div><form class="mt-5 mb-2 px-3 py-2 rounded bg-mira shadow d-inline-flex align-items-center text-light" id="sortForm" action="/index.php">';

	// pass information about current page
	print '<input type="hidden" name="page" value="' . $page  . '" />';
	print '<input type="hidden" name="id" value="' . $id  . '" />';
	if ($search != '') print '<input type="hidden" name="search" value="' . $search . '" />';
	if ($searchCat != '') print '<input type="hidden" name="cat" value="' . $searchCat . '" />';

	// write total
	$matches = count($results);	
	print '' . $matches . switchSgPl($matches, ' manuscript', ' manuscripts') . '. &nbsp;';

	// write sort options
	print '<label for="sort">Sort by</label> &nbsp;';
	print '<select name="sort" class="" onchange="sortForm.submit(); ">';
	writeOption('', 'location', $sort);
	writeOption('script', 'script', $sort);
	writeOption('date', 'date', $sort);
//		writeOption('origin', 'origin', $sort);
//		writeOption('prov', 'provenance', $sort);
	print '</select>';

	print '</form></div>';

?>

<div class="table-responsive-sm pt-2 pb-3">
<table class="table table-striped table-hover table-sm small border-secondary">
<thead>
<tr>
<th>MIrA ID</th>
<th>City</th>
<th>Library</th>
<th>Shelfmark/section</th>
<th>Contents</th>
<th>Script</th>
<th>Dating</th>
<th>Origin</th>
<th>Categories</th>
<th>Images</th>
<th></th>
</tr>
</thead>

<tbody>
<?php

	// cycle through entries
	foreach ($resultsSorted as $ms) {
		$link = getLink('ms', $ms['id']);
	
		$libraryID = strval($ms->identifier['libraryID']);	
		print '<tr style="cursor: pointer; " onclick="location.href=\''. $link . '\'">' . "\n";
		print '<td>' . $ms['id'] . '</td>';
		print '<td>' . $libraries[$libraryID]['city'] . '</td>';
		print '<td>' . $libraries[$libraryID]['name'] . '</td>';

		print '<td>' . $ms->identifier->shelfmark;
		if ($ms->identifier->ms_name != '') print ' (' . $ms->identifier->ms_name . ')';
		$i = count($ms->identifier);
		if ($i > 1) print '<br><span class="rounded bg-warning small p-1"><b>+ ' . ($i - 1) . ' other ' . switchSgPl(($i - 1), 'unit', 'units') . '</b></span>';
		print '</td>';

		print '<td>';
		if ($ms->description->contents->summary) print stripTags($ms->description->contents->summary->asXML(), true);
		else print stripTags($ms->description->contents->asXML(), true);
		print '</td>';

		print '<td>' . $ms->description->script . '</td>';
		print '<td>' . $ms->history->date_desc . '</td>';
		print '<td>' . stripTags($ms->history->origin->asXML(), true) . '</td>';
//			print '<td>' . $ms->history->provenance . '</td>';

		// categories
		print '<td><nobr>';
		if ($ms->notes['categories'] != '') {
			$theseCats = explode(' ', $ms->notes['categories']);
			foreach ($theseCats as $thisCatID) {
				$thisCatID = str_replace('#', '', $thisCatID);
				if (isset($msCategories[$thisCatID])) writeCategoryIcon($thisCatID, true);
			} 
		}
		print '<nobr></td>';

		print '<td width="75">';
		if (count($ms->identifier->xpath('link[@type="images"]')) > 0) print '<img src="/images/photo_icon.png" width="35" alt="Link to images available" />';
		if (count($ms->identifier->xpath('link[@type="iiif"]')) > 0) print '<a href="'. $link . '"><img src="/images/iiif_logo.png" width="30" alt="Embedded IIIF images available" /></a>';
		print '</td>';
		print '<td><a href="'. $link . '"><svg xmlns="http://www.w3.org/2000/svg" width="25" height="25" fill="#0e300e" class="" viewBox="0 0 16 16"><path d="M0 14a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V2a2 2 0 0 0-2-2H2a2 2 0 0 0-2 2v12zm4.5-6.5h5.793L8.146 5.354a.5.5 0 1 1 .708-.708l3 3a.5.5 0 0 1 0 .708l-3 3a.5.5 0 0 1-.708-.708L10.293 8.5H4.5a.5.5 0 0 1 0-1z"/></svg></a></td>';
		print '</tr>' . "\n";
	}

?>
</tbody>
</table>

<?php
	// link to download CSV
	$queryString = 'page=' . $page;
	$queryString .= '&id=' . $id;
	$queryString .= '&search=' . $search;
	$queryString .= '&cat=' . $searchCat;
	$queryString .= '&lib=' . $searchLib;
	print '<p><a class="small" href="/csv.php?' . $queryString . '">Export this list</a> as a CSV file.</p>';

?>

</div>

<?php
	mapLibraries($resultsSorted);
	chartDates($resultsSorted);
	chartSizes($resultsSorted);
	chartFolios($resultsSorted);
	networkGraph($resultsSorted);
}


//
// sorting functions
function sortShelfmark($a, $b) {
	return strnatcmp($a->identifier->shelfmark, $b->identifier->shelfmark);
}
function sortShelfmarkIndexer($a, $b) {
	return strnatcmp($a->identifier->shelfmark_indexer, $b->identifier->shelfmark_indexer);
}
function sortScript($a, $b) {
	return strnatcmp($a->description->script, $b->description->script);
}
function sortDate($a, $b) {
	return strnatcmp($a->history->term_post, $b->history->term_post);
}
function sortLocation($a, $b) {
	return strnatcmp($a->identifier['libraryID'], $b->identifier['libraryID']);
}
/*
function sortOrigin($a, $b) {
	return strnatcmp($a->history->origin, $b->history->origin);
}
function sortProv($a, $b) {
	return strnatcmp($a->history->provenance, $b->history->provenance);
}
*/

?>