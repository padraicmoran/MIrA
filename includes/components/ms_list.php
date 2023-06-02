<?php
/* 
Results page for MS searches
*/

function listMSS($resultsSorted) {
	global $page, $id, $search, $searchCat, $searchLib;
	global $libraries, $msCategories, $tidyURLs;

	$matches = count($resultsSorted);	
	print '' . $matches . switchSgPl($matches, ' manuscript', ' manuscripts') . '. ';

	// Output raw list
	// foreach ($resultsSorted as $ms) print "'" . $ms['id'] . "', ";


?>

<div class="table-responsive-sm pt-2 pb-3">
<table class="table table-striped table-hover table-sm small">
<thead>
<tr>
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
		print '<tr style="cursor: pointer; " onclick="location.href=\''. $link . '\'">';
		print '<td>' . $libraries[$libraryID]['city'] . '</td>';
		print '<td>' . $libraries[$libraryID]['name'] . '</a></td>';

		print '<td>' . $ms->identifier->shelfmark;
		if ($ms->identifier->ms_name != '') print ' (' . $ms->identifier->ms_name . ')';
		$i = count($ms->identifier);
		if ($i > 1) print '<br><span class="rounded bg-warning small p-1"><b>+ ' . ($i - 1) . ' other ' . switchSgPl(($i - 1), 'unit', 'units') . '<span></b>';
		print '</td>';

		print '<td>' . preg_replace('/\[(\/{0,1}[a-z])\]/', '<\1>', $ms->description->contents->asXML()) . '</td>';
		print '<td>' . $ms->description->script . '</td>';
		print '<td>' . $ms->history->date_desc . '</td>';
		print '<td>' . $ms->history->origin->asXML() . '</td>';
//			print '<td>' . $ms->history->provenance . '</td>';

		// categories
		print '<td><nobr>';
		if ($ms->notes->categories != '') {
			$theseCats = explode(';', $ms->notes->categories);
			foreach ($theseCats as $thisCatID) {
				if (isset($msCategories[$thisCatID])) writeCategoryIcon($thisCatID, true);
			} 
		}
		print '<nobr></td>';

		print '<td width="75">';
		if (count($ms->identifier->xpath('link[@type="images"]')) > 0) print '<img src="/images/photo_icon.png" width="35" alt="Link to images available" />';
		if (count($ms->identifier->xpath('link[@type="iiif"]')) > 0) print '<a href="'. $link . '"><img src="/images/iiif_logo.png" width="30" alt="Embedded IIIF images available" /></a>';
		print '</td>';
		print '<td><a href="'. $link . '"><svg xmlns="http://www.w3.org/2000/svg" width="25" height="25" fill="#007bff" class="" viewBox="0 0 16 16"><path d="M0 14a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V2a2 2 0 0 0-2-2H2a2 2 0 0 0-2 2v12zm4.5-6.5h5.793L8.146 5.354a.5.5 0 1 1 .708-.708l3 3a.5.5 0 0 1 0 .708l-3 3a.5.5 0 0 1-.708-.708L10.293 8.5H4.5a.5.5 0 0 1 0-1z"/></svg></a></td>';
		print '</tr>';
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
	print '<p class="mt-5"><a class="small" href="/csv.php?' . $queryString . '">Export this list</a> as a CSV file.</p>';


?>


</div>

<!-- Library map -->
<h3 class="mt-5 pt-2">Manuscript libraries</h4> 
<?php
	libraryMap($resultsSorted);
?>

<!-- Date chart -->
<h3 class="mt-5 pt-2">Manuscript dates (approx.)</h4>
<?php
	dateChart($resultsSorted);
?>

<!-- Size chart -->
<h3 class="mt-5 pt-2">Page sizes</h4> 
<?php
	sizesChart($resultsSorted);
?>

<!-- Folios chart -->
<h3 class="mt-5 pt-2">Folios</h4> 
<?php
	foliosChart($resultsSorted);

}
?>
