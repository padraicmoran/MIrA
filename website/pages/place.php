<?php
// place details

if (file_exists('../data/other/places.xml')) {
	$xml_places = simplexml_load_file('../data/other/places.xml');

	// get place info
	$filter = $xml_places->xpath('//place[@id="' . $id . '"]');
	if ($filter) {
		$place = $filter[0];
?>

<div class="row">
	<div class="col-lg-4">
<?php

		writeBreadcrumb('place', '');
		echo '<h2>' . $place->name . '</h2>';

		// stable URL
		$link = getLink('place', $id);
		echo '<div class="text-secondary small">Stable URL: <a class="text-secondary" href="' . $link . '">https://mira.ie' . $link . '</a></div>';

		// other language versions
		echo '<p class="mt-3">';
		writeTrans($place->xpath('name'));
		echo '</p>';

		// if region: show sub-locations
		if ($place['type'] == 'region') {
			$subPlaces = $xml_places->xpath('//place[@id="' . $id . '"]/place');
			if ($subPlaces) {
				echo '<p>Region, including the following places:</p> ';
				echo '<ul>';
				foreach ($subPlaces as $p) {
					echo '<li><a href="' . getLink('place', $p['id']) . '">' . $p->name . '</a></li>';
				}				
				echo '</ul>';
			}
			else {
				// no sub-locations
				echo '<p>Region.</p>';
			}
		}
		// if not region: check if part of a region
		else {
			$priorPlace = $xml_places->xpath('//place[@id="' . $id . '"]/ancestor::place');
			if ($priorPlace) echo '<p>Part of region: <a href="' . getLink('places', $priorPlace[0]['id']) . '">' . $priorPlace[0]->name . '</a></p>';
		}

		// linked data
		$links = $place->xpath('xref');
		if (sizeof($links) > 0) {
			echo '<h3 class="mt-5">Linked data</h3>';
			foreach ($links as $link) {
				if ($link['type'] == 'pleiades') echo '<a href="' . $link . '">Pleiades</a><br/>';
				if ($link['type'] == 'viaf') echo '<a href="' . $link . '">VIAF</a><br/>';
				if ($link['type'] == 'wikidata') echo '<a href="' . $link . '">Wikidata</a><br/>';
				if ($link['type'] == 'biblissima') echo '<a href="' . $link . '">Biblissima</a><br/>';
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
		$xpath = 'manuscript[.//place[@id="' . $id . '"]]';
		// if this place is a region, also search all sub-places
		if (isset($subPlaces)) {
			foreach ($subPlaces as $p) $xpath .= ' | manuscript[.//place[@id="' . $p['id'] . '"]]';
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
#		echo '<div class="text-secondary small mt-5">Download <a class="text-secondary" href="/data/other/places.xml">XML data</a> for places.</div>';

	}
	else {
		// if no match, exit to general list
		require 'pages/places.php';
	}
}

function writeTrans($arr) {
foreach ($arr as $name) {
	if (trim($name) <> '') {
		if ($name['lang'] == 'la') echo 'Latin: <i>' . $name . '</i><br/>';
		if ($name['lang'] == 'de') echo 'German: <i>' . $name . '</i><br/>';
	}
}

}
?>
