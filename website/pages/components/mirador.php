<?php
/* 
Display Mirador viewer
*/

function mirador($iiifLinks) {

	echo '<div id="images" class="mt-4 mb-5">';
	$manifestString = getAllManifests();

	// Mirador viewer code
	?>

<script src="https://unpkg.com/mirador@4.0.0/dist/mirador.min.js"></script>
<link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Roboto:300,400,500">
<div id="miradorViewer" class="rounded shadow" style="position: relative; height: 700px; margin-bottom: 15px;">
	<!-- spinner -->
	<button class="btn btn-primary" type="button" disabled>
	  <span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
	  Loading image viewer…
	</button>
</div>

<script type="text/javascript">
const viewer = Mirador.viewer({
	id: "miradorViewer",
	catalog: [ 
	<?php echo $manifestString; ?> 
	],
	windows: [
<?php

	foreach ($iiifLinks as $i) {
		echo '{ ';
		echo 'manifestId: "' . $i . '", ';		
		if ($i->xpath('@index')) echo 'canvasIndex: ' . $i->xpath('@index')[0] .  ', ';
		echo 'thumbnailNavigationPosition: "far-right" ';
		echo '}';
		if (next($iiifLinks)) echo ', ';
	}
	
?>	

	],
	thumbnailNavigation: {
		defaultPosition: 'far-right'
	},
  	workspace: {
		showZoomControls: true
	}
});
</script>

	</div>

	<?php
}

function getAllManifests() {
	global $xml_mss;

	// prepare list of all IIIF manifests
	$results = $xml_mss->xpath('//link[@type="iiif"]');

	// sorting
	// cannot sort a SimpleXML object, so transfer top-level objects into an array instead
	$resultsSorted = array();
	foreach($results as $node) {
		$resultsSorted[] = $node;
	}
	// sort manifests strings by MS shelfmark
	usort($resultsSorted, 'sortURL');

	// compile manifest list as string
	$manifestString = '';
	$prevManifest = '';
	foreach ($resultsSorted as $manifest) {
		if (strval($prevManifest) != strval($manifest)) {
			$manifestString .=  '{ manifestId: "' . $manifest . '" } ';
			if (next($resultsSorted)) $manifestString .= ', ';
		}
		$prevManifest = $manifest;
	}	

	return $manifestString;
}

function sortURL($a, $b) {
	return strnatcmp($a, $b);
}


?>