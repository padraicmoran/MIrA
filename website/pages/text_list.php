<?php

if (file_exists('../data/other/texts.xml')) {

	// load data
	$xml_texts = simplexml_load_file('../data/other/texts.xml');
	$matches = count($xml_texts);

	// write header
	templateTop('texts');
	writeBreadcrumb('text', null);
	echo '<h1>Texts <span class="badge rounded-pill small text-bg-success">' . $matches . '</span></h1>';
?>

<p>(Indexing is still in progress.)</p>

<div class="table-responsive-sm mt-4">
<table class="table table-striped table-hover border-secondary">
<thead>
<tr>
<th>Author</th>
<th>Title</th>
<th></th>
</tr>
</thead>

<tbody>
<?php
	// set up variables for date chart
	foreach ($xml_texts->text as $text) {
		$link = getLink('text', $text['id']);
	
		// write table row
		echo '<tr style="cursor: pointer; " onclick="location.href=\''. $link . '\'">';
		echo '<td>' . $text->author . '</td>';

		$title = $text->title;
		if ($text->title['style'] != 'roman') $title = '<i>' . $title . '</i>';
		echo '<td>' . $title . '</td>';
		
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