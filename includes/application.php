<?php
/* 
Draws in all the available avilable and loads in core data (manuscripts, categories, libraries)
*/

// load config variables, general functions and page elements
require('includes/config.php');
require('includes/functions.php');
require('includes/template.php');
require('includes/utils.php');
require('includes/components/category_buttons.php');
require('includes/components/chart_shared.php');
require('includes/components/chart_dates.php');
require('includes/components/chart_folios.php');
require('includes/components/chart_sizes.php');
require('includes/components/list_mss.php');
require('includes/components/map_libraries.php');
require('includes/components/map_places.php');
require('includes/components/mirador.php');
require('includes/components/network_graph.php');
require('includes/components/search_mss.php');
$romNum = array('', 'I', 'II', 'III', 'IV', 'V', 'VI', 'VII', 'VIII', 'IX', 'X');
$languages = array(
	'en'=>'English',
	'de'=>'German',
	'fr'=>'French',
	'gr'=>'Greek',
	'la'=>'Latin'
);


if ($debug) {
	ini_set('display_errors', 1);
	ini_set('display_startup_errors', 1);
	error_reporting(E_ALL);
}

// 
// LOAD DATA
// manuscripts
if (file_exists('data/mss.xml')) {
	$xml_mss = simplexml_load_file('data/mss.xml');
	$totalMSS = count($xml_mss->xpath('manuscript'));
}

// load MS categories
if (file_exists('data/ms_categories.xml')) {
	$xml_msCategories = simplexml_load_file('data/ms_categories.xml');

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
if (file_exists('data/libraries.xml')) {
	$xml_libraries = simplexml_load_file('data/libraries.xml');

	// build associative array
	$libraries = array();
	foreach ($xml_libraries->library as $lib) {
		$id = strval($lib['id']);
		$libraries[$id] = [
			'id' => $id,
			'country' => strval($lib->country),
			'city' => strval($lib->city),
			'name' => strval($lib->name),
			'coords' => strval($lib->coords),
			'searchIndex' => simpleText(strval($lib->country . $lib->city . $lib->name))
		];
	}
}

// load places
if (file_exists('data/places.xml')) {
	$xml_places = simplexml_load_file('data/places.xml');
	$extractPlaces = $xml_places->xpath ('//place');

	// build associative array
	$placeInfo = array();
	foreach ($extractPlaces as $place) {
	  $i = strval($place['id']);
	  $placeInfo[$i] = [
		 'id' => $i,
		 'name' => strval($place->name[0]),
		 'coords' => strval($place->coords)
	  ];
	}
}

?>