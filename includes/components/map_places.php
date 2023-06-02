<?php
/* 
Map for place entries
*/

function mapPlaces($results, $selectedID) {
	global $tidyURLs;
?>

<div class="border border-secondary rounded shadow mt-5" id="mapPlacesContainer" style="height: 400px; "></div>
<script type="text/javascript">

var mapPlaces = L.map('mapPlacesContainer').setView([47, 6], 5);
L.tileLayer('https://api.mapbox.com/styles/v1/{id}/tiles/{z}/{x}/{y}?access_token=pk.eyJ1IjoicGFkcmFpY20iLCJhIjoiY2tuZXBrZDBqMjd1YzJ2bXFyNDc0bnAzOCJ9.-iVPCwPpOlIm3DXbXOwLYA', {
    attribution: 'Map data &copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors, Imagery © <a href="https://www.mapbox.com/">Mapbox</a>',
    maxZoom: 18,
    id: 'mapbox/streets-v11',
    tileSize: 512,
    zoomOffset: -1,
    accessToken: 'pk.eyJ1IjoicGFkcmFpY20iLCJhIjoiY2tuZXBrZDBqMjd1YzJ2bXFyNDc0bnAzOCJ9.-iVPCwPpOlIm3DXbXOwLYA'
}).addTo(mapPlaces);

var markers = new Array();
var bounds = new L.LatLngBounds();

<?php

	// write data from array
   $n = 0;
	$radius = 8;
	$selectedCoords = '';
	foreach ($results as $place) {
		$n ++;

		// prepare content
		$coords = $place->coords;
		if ($coords <> '') {
		
			// prepare popup text 
			$content = '<div class="fs-6">';
			if ($place['id'] != $selectedID) {
				$link = getLink('places', $place['id']);
				$content .= '<a class="" href="' . $link . '">' . $place->name . '</a><br/>';
			}
			else {
				$content .= '<b>' . $place->name . '</b><br/>';
			}
//			$content .= $place->description;
			$content .= '<span class="small"><a href="#" onclick="mapPlaces.setView([' . $coords . '], 13); mapPlaces.closePopup(); return false; ">Zoom in</a></span>';
			$content .= '</div>';
									
			// write marker
			if ($place['type'] == 'region') $appearance = 'radius: ' . ($radius * 3) . ', color: "none", fillColor: "darkblue", fillOpacity: 0.4';  
			else $appearance = 'radius: ' . $radius . ', color: "none", fillColor: "darkblue", fillOpacity: 0.7';
			
			print 'markers[' . $n . '] = L.circleMarker([' . $coords . '], { ' . $appearance . ' }).addTo(mapPlaces); ';
			print 'markers[' . $n . '].bindPopup(\'' . addSlashes($content) . '\'); ';
			print 'markers[' . $n . '].on("click", function(e) { this.openPopup; }); ';
			print "\n";
			
			// add to bounds object (to zoom to show all markers)
			print 'bounds.extend(markers[' . $n . '].getLatLng());';

			// check whether selected
			if ($place['id'] == $selectedID) { 
				$selectedCoords = $coords;
				$selectedNum = $n;
			}
		}
	}

	// if showing one result, centre map
	if ($selectedCoords != '') {
		print 'mapPlaces.setView([' . $selectedCoords . '], 8); ';
		print 'markers[' . $selectedNum . '].openPopup(); ';	
	}
	else print 'mapPlaces.fitBounds(bounds); ';

?>

mapPlaces.addControl(new L.Control.Fullscreen());
	
</script>
<?php
}

?>