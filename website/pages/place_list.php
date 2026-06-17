<?php

writeBreadcrumb('place', null);
echo '<h2>Places</h2>';

if (file_exists('../data/other/places.xml')) {
	$xml_places = simplexml_load_file('../data/other/places.xml');

	$matches = count($xml_places->xpath('//place'));
	echo '<div class="d-inline-flex mt-2 mb-4 px-3 py-2 text-light small bg-mira	rounded">' . $matches;
	echo switchSgPl($matches, ' place', ' places');
	echo '</div>';

	mapPlaces($xml_places, '');

?>

<div class="table-responsive-sm mt-4">
<table class="table table-striped table-hover border-secondary">
<thead>
<tr>
<th>Name</th>
<th></th>
</tr>
</thead>

<tbody>
<?php
	// set up variables for date chart
	foreach ($xml_places->place as $place) {
		if ($place['type'] == 'region') writeRegion($place, false);
		else writePlace($place, false);
	}

?>
</tbody>
</table>
</div>

<?php
}

function writeRegion($place, $indent) {
	$link = getLink('place', $place['id']);
	if ($indent) $indentInsert = ' class="ps-5"';
	else $indentInsert = '';
	
	// write table row
	echo '<tr style="cursor: pointer; " onclick="location.href=\''. $link . '\'">';
	echo '<td' . $indentInsert . '><b>' . $place->name . '</b> (region)</td>';
	echo '<td><a href="'. $link . '"><svg xmlns="https://www.w3.org/2000/svg" width="25" height="25" fill="#0e300e" class="" viewBox="0 0 16 16"><path d="M0 14a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V2a2 2 0 0 0-2-2H2a2 2 0 0 0-2 2v12zm4.5-6.5h5.793L8.146 5.354a.5.5 0 1 1 .708-.708l3 3a.5.5 0 0 1 0 .708l-3 3a.5.5 0 0 1-.708-.708L10.293 8.5H4.5a.5.5 0 0 1 0-1z"/></svg></a></td>';
	echo '</tr>';

	// check for sub-places
	foreach ($place->place as $subPlace) {
		if ($subPlace['type'] == 'region') writeRegion($subPlace, true);
		else writePlace($subPlace, true);
	}
}

function writePlace($place, $indent) {
	$link = getLink('place', $place['id']);
	if ($indent) $indentInsert = ' class="ps-5"';
	else $indentInsert = '';

	// write table row
	echo '<tr style="cursor: pointer; " onclick="location.href=\''. $link . '\'">';
	echo '<td' . $indentInsert . '>' . $place->name . '</td>';
	echo '<td><a href="'. $link . '"><svg xmlns="https://www.w3.org/2000/svg" width="25" height="25" fill="#0e300e" class="" viewBox="0 0 16 16"><path d="M0 14a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V2a2 2 0 0 0-2-2H2a2 2 0 0 0-2 2v12zm4.5-6.5h5.793L8.146 5.354a.5.5 0 1 1 .708-.708l3 3a.5.5 0 0 1 0 .708l-3 3a.5.5 0 0 1-.708-.708L10.293 8.5H4.5a.5.5 0 0 1 0-1z"/></svg></a></td>';
	echo '</tr>';
}

?>