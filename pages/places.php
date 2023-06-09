
<h2>Places</h2>

<div class="bg-warning p-3 rounded border">Under development. Sample data for testing purposes.</div>	

<?php
if (file_exists('data/places.xml')) {
	$xml_places = simplexml_load_file('data/places.xml');

	mapPlaces($xml_places, '');
?>

<div class="table-responsive-sm mt-4">
<table class="table table-striped table-hover">
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
		if ($place['type'] == 'region') writeRegion($place);
		else writePlace($place, false);
	}

?>
</tbody>
</table>
</div>

<?php
}

function writeRegion($place) {
	$link = getLink('places', $place['id']);
	
	// write table row
	print '<tr style="cursor: pointer; " onclick="location.href=\''. $link . '\'">';
	print '<td><b>' . $place->name . '</b> (region)</td>';
	print '<td><a href="'. $link . '"><svg xmlns="http://www.w3.org/2000/svg" width="25" height="25" fill="#007bff" class="" viewBox="0 0 16 16"><path d="M0 14a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V2a2 2 0 0 0-2-2H2a2 2 0 0 0-2 2v12zm4.5-6.5h5.793L8.146 5.354a.5.5 0 1 1 .708-.708l3 3a.5.5 0 0 1 0 .708l-3 3a.5.5 0 0 1-.708-.708L10.293 8.5H4.5a.5.5 0 0 1 0-1z"/></svg></a></td>';
	print '</tr>';

	// check for sub-places
	foreach ($place->place as $subPlace) writePlace($subPlace, true);
}

function writePlace($place, $indent) {
	$link = getLink('places', $place['id']);
	if ($indent) $indentInsert = ' class="ps-5"';
	else $indentInsert = '';

	// write table row
	print '<tr style="cursor: pointer; " onclick="location.href=\''. $link . '\'">';
	print '<td' . $indentInsert . '>' . $place->name . '</td>';
	print '<td><a href="'. $link . '"><svg xmlns="http://www.w3.org/2000/svg" width="25" height="25" fill="#007bff" class="" viewBox="0 0 16 16"><path d="M0 14a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V2a2 2 0 0 0-2-2H2a2 2 0 0 0-2 2v12zm4.5-6.5h5.793L8.146 5.354a.5.5 0 1 1 .708-.708l3 3a.5.5 0 0 1 0 .708l-3 3a.5.5 0 0 1-.708-.708L10.293 8.5H4.5a.5.5 0 0 1 0-1z"/></svg></a></td>';
	print '</tr>';
}

?>
