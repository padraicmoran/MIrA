<?php
require 'includes/application.php';

// get inputs
$page = cleanInput('page') ?? '';
$id = cleanInput('id') ?? '';
$search = cleanInput('search') ?? '';

// if search is a number, redirect to ms page
if ((int) $search > 0 && (int) $search <= $totalMSS) {
	$page = 'mss';
	$id = $search;
}


// content router
if (isset($xml_mss)) {
	if ($page == 'mss') {
		templateTop(1);
		if ($id != '') require 'pages/ms_detail.php';
		else require 'pages/mss.php';
	}
	elseif ($page == 'texts') {
		templateTop(2);
		if ($id != '') require 'pages/texts_detail.php';
		else require 'pages/texts.php';
	}
	elseif ($page == 'people') {
		templateTop(3);
		if ($id != '') require 'pages/people_detail.php';
		else require 'pages/people.php';
	}
	elseif ($page == 'places') {
		templateTop(4);
		if ($id != '') require 'pages/places_detail.php';
		else require 'pages/places.php';
	}
	elseif ($page == 'about') {
		templateTop(5);
		require 'pages/about.php';
	}
	// in development
	elseif ($page == 'xpath') {
		templateTop(1);
		require 'pages/xpath.php';
	}
	// home page (in the last resort)
	else {	
		templateTop(0);
		require 'pages/home.php';
	}
	// template
	templateBottom();	
}
else {
	// some problem accessing XML
	print 'This resource is currently unavailable. Please try again later.';
}


?>
