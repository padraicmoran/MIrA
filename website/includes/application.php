<?php
/* 
Draws together all components and loads core data (manuscripts, categories, libraries)
*/

// load config options
require 'includes/config.php';
if ($debug) {
	ini_set('display_errors', 1);
	ini_set('display_startup_errors', 1);
	error_reporting(E_ALL);
}

// load general functions and page elements
require 'includes/functions.php';
require 'includes/template.php';
require 'includes/utils.php';
require 'pages/components/category_buttons.php';
require 'pages/components/chart_shared.php';
require 'pages/components/chart_dates.php';
require 'pages/components/chart_folios.php';
require 'pages/components/chart_sizes.php';
require 'pages/components/list_mss.php';
require 'pages/components/map_libraries.php';
require 'pages/components/map_places.php';
require 'pages/components/mirador.php';
require 'pages/components/search_mss.php';

// testing new network graph
require 'pages/components/network_graph1.php';
require 'pages/components/network_graph2.php';

$romNum = array('', 'I', 'II', 'III', 'IV', 'V', 'VI', 'VII', 'VIII', 'IX', 'X');
$languages = array(
	'en'=>'English',
	'de'=>'German',
	'fr'=>'French',
	'gr'=>'Greek',
	'la'=>'Latin'
);

// 
// LOAD DATA
// manuscripts
if (file_exists('../data/mss_compiled.xml')) {
	$xml_mss = simplexml_load_file('../data/mss_compiled.xml');
	$totalMSS = count($xml_mss->xpath('manuscript[notes[not(contains(@categories, "#excl"))]]'));
}

// load MS categories
if (file_exists('../data/categories.xml')) {
	$xml_msCategories = simplexml_load_file('../data/categories.xml');

	// build category associative array; key = shorthand code, value = label
	$msCategories = array();
	foreach ($xml_msCategories as $cat) {
		$msCategories[strval($cat['id'])] = $cat;	// category name (text)
		$msCategories[strval($cat['id'])]['displayCode'] = $cat['displayCode'];
		$msCategories[strval($cat['id'])]['type'] = $cat['type'];
		$msCategories[strval($cat['id'])]['color'] = $cat['color'];
	}
}

// load libraries
if (file_exists('../data/libraries.xml')) {
	$xml_libraries = simplexml_load_file('../data/libraries.xml');

	// build associative array
	$libraries = array();
	foreach ($xml_libraries->library as $lib) {
		$id = strval($lib['id']);
		$libraries[$id] = [
			'id' => $id,
			'country' => strval($lib->country),
			'city' => strval($lib->city),
			'name' => strval($lib->name),
			'shortName' => strval($lib->shortName),
			'coords' => strval($lib->coords),
			'searchIndex' => simpleText(strval($lib->country . $lib->city . $lib->name))
		];
	}
}

// load places
if (file_exists('../data/places.xml')) {
	$xml_places = simplexml_load_file('../data/places.xml');
	$extractPlaces = $xml_places->xpath ('//place');

	// build associative array
	$placeInfo = array();
	foreach ($extractPlaces as $place) {
	  $i = strval($place['id']);
	  // check for parent
	  $getParentIDs = $xml_places->xpath ('//place[@id="' . $i . '"]/ancestor::place/@id');
	  if ($getParentIDs) $parentID = strval($getParentIDs[0]);
	  else $parentID = null;
	  $placeInfo[$i] = [
		 'id' => $i,
		 'type' => strval($place['type']),
		 'parentID' => $parentID,
		 'name' => strval($place->name[0]),
		 'coords' => strval($place->coords)
	  ];
	}
}

// load listBibl
/* IN DEVELOPMENT: HOLD FOR NOW
if (file_exists('../data/listBibl.xml')) {
	$xml_listBibl = simplexml_load_file('../data/places.xml');

	// build associative array
	$listBibl = array();
	foreach ($xml_listBibl->source as $source) {
		$id = strval($source['id']);
		$libraries[$id] = [
			'id' => $id,
			'shortRef' => strval($source->shortRef),
			'fullRef' => strval($source->fullRef)
	  ];
	}
}
*/

?>