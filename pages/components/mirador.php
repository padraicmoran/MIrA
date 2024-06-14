<?php
/* 
Display Mirador viewer
*/

function mirador($iiifLinks) {

	print '<div class="mt-4">';
	$manifestString = getAllManifests();

	// Mirador viewer code
	?>

<a name="images"></a>	
<script src="https://unpkg.com/mirador@latest/dist/mirador.min.js"></script>
<link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Roboto:300,400,500">
<div id="miradorViewer" class="rounded shadow" style="position: relative; height: 600px; margin-bottom: 15px;">
	<!-- spinner -->
	<button class="btn btn-primary" type="button" disabled>
	  <span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
	  Loading image viewer…
	</button>
</div>

<script type="text/javascript">
var mirador = Mirador.viewer({
	id: "miradorViewer",
	catalog: [ 
	<?php print $manifestString; ?> 
	],
	windows: [
<?php

	foreach ($iiifLinks as $i) {
		print '{ ';
		print 'manifestId: "' . $i . '", ';		
		if ($i->xpath('@index')) print 'canvasIndex: ' . $i->xpath('@index')[0] .  ', ';
		print 'thumbnailNavigationPosition: "far-bottom" ';
		print '}';
		if (next($iiifLinks)) print ', ';
	}
	
?>	

	],
	thumbnailNavigation: {
		defaultPosition: 'far-bottom'
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
