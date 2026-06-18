<?php
// Results page for MS searches

function listMSS($results, $primaryHeading) {
	global $page, $id, $search, $searchCat, $searchLib;
	global $libraries, $msCategories;

	// sort results
	// cannot sort a SimpleXML object, so transfer top-level objects into an array instead
	$sort = cleanInput('sort') ?? '';
	$filter = cleanInput('filter') ?? '';

	$resultsSorted = array();
	foreach($results as $node) {
		// if filter is set, only include matching manuscripts
		if ($filter != '' && isset($node->notes['categories']) && strpos($node->notes['categories'], '#' . $filter) === false) continue;
		$resultsSorted[] = $node;
	}
	$matches = count($resultsSorted);	
	
	//	header
	if ($primaryHeading) echo '<h1>Manuscripts <span class="badge rounded-pill text-bg-success">' . $matches . '</span></h1>';
	else echo '<h2>Manuscripts <span class="badge rounded-pill text-bg-success">' . $matches . '</span></h2>';

	// default sort is by city, library, shelfmark; change for other options below
	usort($resultsSorted, 'sortShelfmarkIndexer');
	usort($resultsSorted, 'sortShelfmark');
	if ($sort == '') usort($resultsSorted, 'sortLocation');
	elseif ($sort == 'script') usort($resultsSorted, 'sortScript');
	elseif ($sort == 'date') usort($resultsSorted, 'sortDate');
//		elseif ($sort == 'origin') usort($resultsSorted, 'sortOrigin');
//		elseif ($sort == 'prov') usort($resultsSorted, 'sortProv');


	// sort form
	echo '<div><form class="mt-3 my-2 px-3 py-2 rounded-pill text-bg-secondary d-inline-flex align-items-center text-light small" id="sortForm" action="/index.php">';

	// pass information about current page
	echo '<input type="hidden" name="page" value="' . $page  . '" />';
	echo '<input type="hidden" name="id" value="' . $id  . '" />';
	if ($search != '') echo '<input type="hidden" name="search" value="' . $search . '" />';
	if ($searchCat != '') echo '<input type="hidden" name="cat" value="' . $searchCat . '" />';

	// write sort options
	echo '<label class="ms-0 me-2" for="sort">Sort by</label>';
	echo '<select name="sort" class="" onchange="sortForm.submit(); ">';
	writeOption('', 'location', $sort);
	writeOption('script', 'script', $sort);
	writeOption('date', 'date', $sort);
//		writeOption('origin', 'origin', $sort);
//		writeOption('prov', 'provenance', $sort);
	echo '</select>';

	// write filter options
	echo '<label class="ms-4 me-2" for="filter">Filter by</label> &nbsp;';
	echo '<select name="filter" class="" onchange="sortForm.submit(); ">';
	writeOption('', '(none)', $filter);
	writeOption('or-ire', 'Origin: Ireland', $filter);
	writeOption('sc-ire', 'Script: Irish', $filter);
	echo '</select>';


	echo '</form></div>';

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
		$link = getLink('manuscript', $ms['id']);
	
		$libraryID = strval($ms->identifier['libraryID']);	
		echo '<tr style="cursor: pointer; " onclick="location.href=\''. $link . '\'">' . "\n";
		echo '<td>' . $ms['id'] . '</td>';
		echo '<td>' . $libraries[$libraryID]['city'] . '</td>';
		echo '<td>' . $libraries[$libraryID]['name'] . '</td>';

		echo '<td>' . $ms->identifier->shelfmark;
		if ($ms->identifier->ms_name != '') echo ' (' . $ms->identifier->ms_name . ')';
		$i = count($ms->identifier);
		if ($i > 1) echo '<br><span class="rounded bg-warning small p-1"><b>+ ' . ($i - 1) . ' other ' . switchSgPl(($i - 1), 'unit', 'units') . '</b></span>';
		echo '</td>';

		echo '<td>';
		if ($ms->description->contents->summary) echo stripTags($ms->description->contents->summary->asXML(), true);
		else echo stripTags($ms->description->contents->asXML(), true);
		echo '</td>';

		echo '<td>' . $ms->description->script . '</td>';
		echo '<td>' . $ms->history->date_desc . '</td>';
		echo '<td>' . stripTags($ms->history->origin->asXML(), true) . '</td>';
//			echo '<td>' . $ms->history->provenance . '</td>';

		// categories
		echo '<td><nobr>';
		if ($ms->notes['categories'] != '') {
			$theseCats = explode(' ', $ms->notes['categories']);
			foreach ($theseCats as $thisCatID) {
				$thisCatID = str_replace('#', '', $thisCatID);
				if (isset($msCategories[$thisCatID])) writeCategoryIcon($thisCatID, true);
			} 
		}
		echo '<nobr></td>';

		echo '<td width="75">';
		if (count($ms->identifier->xpath('link[@type="images"]')) > 0) echo '<img src="/images/photo_icon.png" width="35" alt="Link to images available" />';
		if (count($ms->identifier->xpath('link[@type="iiif"]')) > 0) echo '<a href="'. $link . '"><img src="/images/iiif_logo.png" width="30" alt="Embedded IIIF images available" /></a>';
		echo '</td>';
		echo '<td><a href="'. $link . '"><svg xmlns="https://www.w3.org/2000/svg" width="25" height="25" fill="#0e300e" class="" viewBox="0 0 16 16"><path d="M0 14a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V2a2 2 0 0 0-2-2H2a2 2 0 0 0-2 2v12zm4.5-6.5h5.793L8.146 5.354a.5.5 0 1 1 .708-.708l3 3a.5.5 0 0 1 0 .708l-3 3a.5.5 0 0 1-.708-.708L10.293 8.5H4.5a.5.5 0 0 1 0-1z"/></svg></a></td>';
		echo '</tr>' . "\n";
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
	echo '<p><a class="small" href="/csv.php?' . $queryString . '">Export this list</a> as a CSV file.</p>';

?>

</div>

<h2 class="mt-5">Further information for this manuscript list</h2>
<?php
	mapLibraries($resultsSorted, true);
	chartDates($resultsSorted);
	chartSizes($resultsSorted);
	chartFolios($resultsSorted);
	if (cleanInput('model') == '1') networkGraph1($resultsSorted);
	networkGraph2($resultsSorted);
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