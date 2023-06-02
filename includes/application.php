<?php
/* 
Draws in all the available avilable and loads in data
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
require('includes/components/map_libraries.php');
require('includes/components/map_places.php');
require('includes/components/mirador.php');
require('includes/components/ms_list.php');
$romNum = array('', 'I', 'II', 'III', 'IV', 'V', 'VI', 'VII', 'VIII', 'IX', 'X');

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

	// build category associative array; key = shorthand code, value = label
	$libraries = array();
	foreach ($xml_libraries->library as $lib) {
		$libraries[strval($lib['id'])]['id'] = strval($lib['id']);
		$libraries[strval($lib['id'])]['country'] = strval($lib->country);
		$libraries[strval($lib['id'])]['city'] = strval($lib->city);
		$libraries[strval($lib['id'])]['name'] = strval($lib->name);
		$libraries[strval($lib['id'])]['coords'] = strval($lib->coords);
		$libraries[strval($lib['id'])]['searchIndex'] = simpleText(strval($lib->country . $lib->city . $lib->name));
	}
}

?>
