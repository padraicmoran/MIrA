
<h2>People</h2>

<p>(Indexing is still in progress.)</p>

<?php
if (file_exists('data/people.xml')) {
	$xml_people = simplexml_load_file('data/people.xml');

?>
<div class="table-responsive-sm mt-4">
<table class="table table-striped table-hover border-secondary">
<thead>
<tr>
<th>Name</th>
<th></th>
<th></th>
</tr>
</thead>

<tbody>
<?php
	// set up variables for date chart
	foreach ($xml_people->person as $person) {
		$link = getLink('people', $person['id']);
	
		// write table row
		print '<tr style="cursor: pointer; " onclick="location.href=\''. $link . '\'">';
		print '<td>' . $person->firstNames . ' ' . $person->surname . '</td>';
		print '<td>' . $person->lifetime . '</td>';
		print '<td><a href="'. $link . '"><svg xmlns="http://www.w3.org/2000/svg" width="25" height="25" fill="#0e300e" class="" viewBox="0 0 16 16"><path d="M0 14a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V2a2 2 0 0 0-2-2H2a2 2 0 0 0-2 2v12zm4.5-6.5h5.793L8.146 5.354a.5.5 0 1 1 .708-.708l3 3a.5.5 0 0 1 0 .708l-3 3a.5.5 0 0 1-.708-.708L10.293 8.5H4.5a.5.5 0 0 1 0-1z"/></svg></a></td>';
		print '</tr>';
	}

?>
</tbody>
</table>
</div>

<?php
}

?>