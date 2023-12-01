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
?>

<div class="row">
	<div class="col-lg-4">
<?php

		print '<h2>';
		print '<a class="text-reset" href="' . $linkBack . '">Places</a> ‣ ';
		print $place->name . '</h2>';

		// stable URL
		print '<div class="text-secondary small mb-3">Stable URL: <a class="text-secondary" href="/places/' . $id . '">http://www.mira.ie/place/' . $id . '</a></div>';

		// other language versions
		print '<p>';
		writeTrans($place->xpath('name'));
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

		// linked data
		$links = $place->xpath('xref');
		if (sizeof($links) > 0) {
			print '<h3 class="mt-5">Linked data</h3>';
			foreach ($links as $link) {
				if ($link['type'] == 'pleiades') print '<a href="' . $link . '">Pleiades</a><br/>';
				if ($link['type'] == 'viaf') print '<a href="' . $link . '">VIAF</a><br/>';
			}
		}

?>
	</div>

	<div class="col-lg-8 mb-5">
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
		}

		// download
		print '<div class="text-secondary small mt-5">Download <a class="text-secondary" href="/data/places.xml">XML data</a> for places.</div>';

	}
	else {
		// if no match, exit to general list
		require 'pages/places.php';
	}
}

function writeTrans($arr) {
foreach ($arr as $name) {
	if (trim($name) <> '') {
		if ($name['lang'] == 'la') print 'Latin: <i>' . $name . '</i><br/>';
		if ($name['lang'] == 'de') print 'German: <i>' . $name . '</i><br/>';
	}
}

}
?>
