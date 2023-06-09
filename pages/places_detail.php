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
		print '<p>';
		if ($place->xpath('name[@lang="de"]')) print 'German: ' . $place->xpath('name[@lang="de"]')[0] . '<br/>';
		if ($place->xpath('name[@lang="la"]')) print 'Latin: ' . $place->xpath('name[@lang="la"]')[0] . '<br/>';
		print '</p>';

		// if region: show sub-locations
		if ($place['type'] == 'region') {
			$subPlaces = $xml_places->xpath('//place[@id="' . $id . '"]/place');
			if ($subPlaces) {
				print '<p>Region, including the following places:</p> ';
				print '<ul>';
				foreach ($subPlaces as $p) {
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
			$priorPlace = $xml_places->xpath('//place[@id="' . $id . '"]/ancestor::place');
			if ($priorPlace) print '<p>Part of region: <a href="' . getLink('places', $priorPlace[0]['id']) . '">' . $priorPlace[0]->name . '</a></p>';
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
		$xpath = '//place[@id="' . $id . '"]/ancestor::manuscript';
		// if this place is a region, also search all sub-places
		if (isset($subPlaces)) {
			foreach ($subPlaces as $p) $xpath .= ' | //place[@id="' . $p['id'] . '"]/ancestor::manuscript';
		}
		$results = $xml_mss->xpath($xpath);
		
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
