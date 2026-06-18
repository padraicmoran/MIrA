<?php

if (file_exists('../data/other/libraries.xml')) {

	// load data
	$xml_libraries = simplexml_load_file('../data/other/libraries.xml');
	$matches = count($xml_libraries);

	// write header
	writeBreadcrumb('library', null);
	echo '<h1>Libraries <span class="badge rounded-pill small text-bg-success">' . $matches . '</span></h1>';

	mapLibraries($xml_mss, false);

?>
<div class="table-responsive-sm mt-4">
<table class="table table-striped table-hover border-secondary">
<thead>
<tr>
<th>Country</th>
<th>City</th>
<th>Library name</th>
<th></th>
</tr>
</thead>

<tbody>
<?php
	// set up variables for date chart
	foreach ($xml_libraries->library as $library) {
		$link = getLink('library', $library['id']);
	
		// write table row
		echo '<tr style="cursor: pointer; " onclick="location.href=\''. $link . '\'">';
		echo '<td>' . $library->country . '</td>';
		echo '<td>' . $library->city . '</td>';
		echo '<td>' . $library->name . '</td>';
		echo '<td><a href="'. $link . '"><svg xmlns="https://www.w3.org/2000/svg" width="25" height="25" fill="#0e300e" class="" viewBox="0 0 16 16"><path d="M0 14a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V2a2 2 0 0 0-2-2H2a2 2 0 0 0-2 2v12zm4.5-6.5h5.793L8.146 5.354a.5.5 0 1 1 .708-.708l3 3a.5.5 0 0 1 0 .708l-3 3a.5.5 0 0 1-.708-.708L10.293 8.5H4.5a.5.5 0 0 1 0-1z"/></svg></a></td>';
		echo '</tr>';
	}

?>
</tbody>
</table>
</div>

<?php
}
else {
	'<div class="alert alert-danger">Data connection failed.</div>';
}

?>