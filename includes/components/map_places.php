<?php
/* 
Map for place entries
*/

function mapPlaces($results, $selectedID) {
	global $tidyURLs, $placeInfo;

	// set up map
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
	$selectedCoords = '';
	foreach ($results as $place) {
		
		// prepare content
		if ($place->coords <> '') {
			if ($place['type'] == 'region') writeRegionMarker($place, $selectedID);
			else writePlaceMarker($place, $selectedID);
		}
	}

	// if showing one result, centre map
	if ($selectedID != '') {
		print 'mapPlaces.setView([' . $placeInfo[$selectedID]['coords'] . '], 8); ';
		print 'markers["' . $selectedID . '"].openPopup(); ';	
	}
	else print 'mapPlaces.fitBounds(bounds); ';

?>

mapPlaces.addControl(new L.Control.Fullscreen());
	
</script>
<?php
}

function writeRegionMarker($place, $selectedID) {
	writePlaceMarker($place, $selectedID);

	// check for sub-places
	foreach ($place->place as $subPlace) writePlaceMarker($subPlace, $selectedID);
}

function writePlaceMarker($place, $selectedID) {
	$id = $place['id'];

	// prepare popup text 
	$content = '<div class="fs-6">';
	if ($place['id'] != $selectedID) {
		$link = getLink('places', $place['id']);
		$content .= '<a class="" href="' . $link . '">' . $place->name . '</a><br/>';
	}
	else {
		$content .= '<b>' . $place->name . '</b><br/>';
	}
	//	$content .= $place->description;
	$content .= '<span class="small"><a href="#" onclick="mapPlaces.setView([' . $place->coords . '], 13); mapPlaces.closePopup(); return false; ">Zoom in</a></span>';
	$content .= '</div>';
							
	if ($place['type'] == 'region') $appearance = 'radius: 18, color: "none", fillColor: "darkblue", fillOpacity: 0.4';  
	else $appearance = 'radius: 8, color: "none", fillColor: "darkblue", fillOpacity: 0.7';

	// write marker
	print 'markers["' . $id . '"] = L.circleMarker([' . $place->coords . '], { ' . $appearance . ' }).addTo(mapPlaces); ';
	print 'markers["' . $id . '"].bindPopup(\'' . addSlashes($content) . '\'); ';
	print 'markers["' . $id . '"].on("click", function(e) { this.openPopup; }); ';
		
	// add to bounds object (to zoom to show all markers)
	print 'bounds.extend(markers["' . $id . '"].getLatLng());' . "\n";
}
?>