<?php
// place details

if (file_exists('data/places.xml')) {
	$xml_places = simplexml_load_file('data/places.xml');

	// get place info
	$filter = $xml_places->xpath('//place[@id="' . $id . '"]');

	if ($filter) {
		$place = $filter[0];
		
		if ($tidyURLs) $linkBack = '/places/';
		else $linkBack = '/index.php?page=places';
		print '<div class="h5 text-secondary"><a href="' . $linkBack . '">Places</a></div>';

?>

<div class="row">
	<div class="col-lg">
<?php

		print '<h2>' . $place->name . '</h2>';
		if ($place->xpath('name[@lang="lat"]')) print '<p><i>' . $place->xpath('name[@lang="lat"]')[0] . '</i></p>';

		// if region: show sub-locations
		if ($place['type'] == 'region') {
			$filterContains = $xml_places->xpath('//place[@partof="' . $id . '"]');
			if ($filterContains) {
				print '<p>Region, including the following places:</p> ';
				print '<ul>';
				foreach ($filterContains as $p) {
					print '<li><a href="' . getLink('places', $p['id']) . '">' . $p->name . '</a></li>';
				}				
				print '</ul>';
			}
			else {
				// no sub-locations
				print '<p>Region.</p>';
			}
		}
		// if not region: check if part of a region
		else {
			if ($place['partof']) print '<p>Part of region: <a href="' . getLink('places', $place['partof']) . '">' . $place['partof'] . '</a></p>';
		}





?>
	</div>

	<div class="col-lg mb-5">
<?php
		mapPlaces($xml_places, $id);
?>
	</div>
</div>


<?php
		// find related manuscripts
		$results = $xml_mss->xpath('//place[@id="' . $id . '"]/ancestor::manuscript');
		if ($results) {

			// sorting
			// cannot sort a SimpleXML object, so transfer top-level objects into an array instead
			$resultsSorted = array();
			foreach($results as $node) {
				$resultsSorted[] = $node;
			}

			// display results
			listMSS($resultsSorted);

			// stable URL
			print '<div class="text-secondary small mt-5">Stable URL: <a class="text-secondary" href="/places/' . $id . '">http://www.mira.ie/place/' . $id . '</a></div>';

		}
	}
	else {
		// if no match, exit to general list
		require('pages/places.php');
	}
}
?>
