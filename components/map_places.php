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

var mapPlaces = L.map('mapPlacesContainer', {
		zoom: 4,
		layers: [pelagios],
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
L.control.layers(baseLayers, overlays, { position: 'topright' }).addTo(mapPlaces);

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
	$content .= 'Place: <b>' . $place->name . '</b><br/>';
	if ($place['id'] != $selectedID) {
		$link = getLink('places', $place['id']);
		$content .= '<a class="" href="' . $link . '">View manuscripts</a><br/>';
	}
	//	$content .= $place->description;
	$content .= '<a href="#" onclick="mapPlaces.setView([' . $place->coords . '], 13); mapPlaces.closePopup(); return false; ">Zoom in</a>';
	$content .= '</div>';
							
	if ($place['type'] == 'region') $appearance = 'radius: 18, stroke: false, fillColor: "darkblue", fillOpacity: 0.5';
	else $appearance = 'radius: 8,  stroke: false, fillColor: "darkblue", fillOpacity: 0.5';

	// write marker
	print 'markers["' . $id . '"] = L.circleMarker([' . $place->coords . '], { ' . $appearance . ' }).addTo(mapPlaces); ';
	print 'markers["' . $id . '"].bindPopup(\'' . addSlashes($content) . '\'); ';
	print 'markers["' . $id . '"].on("click", function(e) { this.openPopup; }); ';
		
	// add to bounds object (to zoom to show all markers)
	print 'bounds.extend(markers["' . $id . '"].getLatLng());' . "\n";
}
?>