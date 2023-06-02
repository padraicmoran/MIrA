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
				$libraryMarker[$libID]['total'] = 1;
			}	
			else {
				$libraryMarker[$libID]['total'] += 1;
			}
		}
	}

?>

<div class="border border-secondary rounded shadow" id="mapLibrariesContainer" style="height: 480px; "></div>
<script type="text/javascript">

var mapLibraries = L.map('mapLibrariesContainer').setView([47, 6], 5);
L.tileLayer('https://api.mapbox.com/styles/v1/{id}/tiles/{z}/{x}/{y}?access_token=pk.eyJ1IjoicGFkcmFpY20iLCJhIjoiY2tuZXBrZDBqMjd1YzJ2bXFyNDc0bnAzOCJ9.-iVPCwPpOlIm3DXbXOwLYA', {
    attribution: 'Map data &copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors, Imagery © <a href="https://www.mapbox.com/">Mapbox</a>',
    maxZoom: 18,
    id: 'mapbox/streets-v11',
    tileSize: 512,
    zoomOffset: -1,
    accessToken: 'pk.eyJ1IjoicGFkcmFpY20iLCJhIjoiY2tuZXBrZDBqMjd1YzJ2bXFyNDc0bnAzOCJ9.-iVPCwPpOlIm3DXbXOwLYA'
}).addTo(mapLibraries);


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
			$content .= '<a href="' . $libraryMarker[$k]['link'] . '">' . $libraryMarker[$k]['lib_name'] . '</a><br/>';
			$content .= '' . $libraryMarker[$k]['total']  . switchSgPl($libraryMarker[$k]['total'], ' mansucript', ' manuscripts') . '<br/>';
			$content .= '<span class="small"><a href="#" onclick="mapLibraries.setView([' . $libraryMarker[$k]['coords'] . '], 13); mapLibraries.closePopup(); return false; ">Zoom in</a></span>';
			$content .= '</div>';
		
			// write marker
			print 'markers[' . $n . '] = L.circleMarker([' . $coords . '], { radius: ' . $radius . ', color: "none", fillColor: "brown", fillOpacity: 0.7 }).addTo(mapLibraries);';
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
<?php

}

?>
