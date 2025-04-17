<?php
/* 
Map of manuscript libraries
*/

function mapLibraries($results) {
	global $libraries;

	// collect data
	$libraryMarker = array();

	// cycle through MSS
	foreach ($results as $ms) {
		// cycle through each MS identifier
		// load details into array, in order to count totals
		foreach ($ms->identifier as $msid) {
			$libID = strval($msid['libraryID']);
			if (! isset($libraryMarker[$libID])) {
				$libraryMarker[$libID] = array();
				$libraryMarker[$libID]['lib_name'] = $libraries[$libID]['city'] . ', ' . $libraries[$libID]['name'] ;
				$libraryMarker[$libID]['coords'] = $libraries[$libID]['coords'];
				$libraryMarker[$libID]['link'] = '/index.php?page=mss&lib=' . $libID;
				$libraryMarker[$libID]['total'] = 0;
			}
			$libraryMarker[$libID]['total'] += 1;
		}
	}

	?>

<h3 class="mt-5">Manuscript libraries</h3>

<div class="border border-secondary rounded shadow" id="mapLibrariesContainer" style="height: 480px; "></div>
<script type="text/javascript">

var streetmap = L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
	maxZoom: 19,
	attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
});
var topography = L.tileLayer('https://{s}.tile.opentopomap.org/{z}/{x}/{y}.png', {
	maxZoom: 17,
	attribution: 'Map data: &copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors, <a href="http://viewfinderpanoramas.org">SRTM</a> | Map style: &copy; <a href="https://opentopomap.org">OpenTopoMap</a> (<a href="https://creativecommons.org/licenses/by-sa/3.0/">CC-BY-SA</a>)'
});
var satellite = L.tileLayer('https://server.arcgisonline.com/ArcGIS/rest/services/World_Imagery/MapServer/tile/{z}/{y}/{x}', {
	attribution: 'Tiles &copy; Esri &mdash; Source: Esri, i-cubed, USDA, USGS, AEX, GeoEye, Getmapping, Aerogrid, IGN, IGP, UPR-EGP, and the GIS User Community'
});
var pelagios = L.tileLayer('https://dh.gu.se/tiles/imperium/{z}/{x}/{y}.png', {
	attribution: 'Map Data: <a href="https://dh.gu.se/dare/">DARE</a>',
	id: 'pelagios', 
	maxZoom: 11, 
	minZoom: 5, 
});

var mapLibraries = L.map('mapLibrariesContainer', {
		zoom: 4,
		layers: [streetmap],
		zoomControl: true,
		preferCanvas: true,
		scrollWheelZoom: false
	});
var baseLayers = {
		"Digital Atlas of the Roman Empire": pelagios,
		"Street map": streetmap,
		"Topography": topography,
		"Satellite": satellite,
    };
var overlays = {};
L.control.layers(baseLayers, overlays, { position: 'topright' }).addTo(mapLibraries);

var markers = new Array();
var bounds = new L.LatLngBounds();

<?php

	// write data from array
   $n = 0;
	foreach ($libraryMarker as $k => $v) {
		$n ++;
		if ($libraryMarker[$k]['coords'] != '') {
			// prepare content
			$coords = $libraryMarker[$k]['coords'];
			$radius = (5 + (0.8 * $libraryMarker[$k]['total']));

			$content = '<div class="fs-6">';
			$content .= 'Library: <b>' . $libraryMarker[$k]['lib_name'] . '</b><br/>';
			$content .= '<a href="' . $libraryMarker[$k]['link'] . '">' . $libraryMarker[$k]['total']  . switchSgPl($libraryMarker[$k]['total'], ' manuscript', ' manuscripts') . '</a><br/>';
			$content .= '<a href="#" onclick="mapLibraries.setView([' . $libraryMarker[$k]['coords'] . '], 13); mapLibraries.closePopup(); return false; ">Zoom in</a>';
			$content .= '</div>';
		
			// write marker
			print 'markers[' . $n . '] = L.circleMarker([' . $coords . '], { radius: ' . $radius . ', stroke: false, fillColor: "#0e0e30", fillOpacity: 0.7 }).addTo(mapLibraries);';
			print 'markers[' . $n . '].bindPopup(\'' . addslashes($content) . '\');';
			print 'markers[' . $n . '].on("click", function(e) { this.openPopup; });';
			print "\n";

			// add to bounds object (to zoom to show all markers)
			print 'bounds.extend(markers[' . $n . '].getLatLng());';
			}
	}
	
?>

mapLibraries.fitBounds(bounds);
mapLibraries.addControl(new L.Control.Fullscreen());
	
</script>

<p class="small mt-2 pb-4">The size of each data point is proportional to the number of manuscripts represented.
</p>

<?php

}

?>
