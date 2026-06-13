<?php
require 'includes/application.php';

// get inputs
$page = cleanInput('page') ?? '';
$id = cleanInput('id') ?? '';
$search = cleanInput('search') ?? '';

// if search is a number, shortcut to manuscript page
if ((int) $search > 0 && (int) $search <= $totalMSS) {
	$page = 'manuscript';
	$id = $search;
}

// route to entity details
if ($id) {
	if ($page == 'manuscript') {
		templateTop(1);
		require 'pages/manuscript.php';
	}
	elseif ($page == 'library') {
		templateTop(2);
		require 'pages/library.php';
	}
	elseif ($page == 'person') {
		templateTop(3);
		require 'pages/person.php';
	}
	elseif ($page == 'place') {
		templateTop(4);
	require 'pages/place.php';
	}
	elseif ($page == 'text') {
		templateTop(5);
		require 'pages/text.php';
	}
	// fall back: route to home page
	else {	
		templateTop(0);
		require 'pages/home.php';
	}
}
else {
	// else, route to list or content pages
	if ($page == 'manuscripts') {
		templateTop(1);
		require 'pages/manuscript_list.php';
	}
	elseif ($page == 'libraries') {
		templateTop(2);
		require 'pages/library_list.php';
	}
	elseif ($page == 'people') {
		templateTop(3);
		require 'pages/person_list.php';
	}
	elseif ($page == 'places') {
		templateTop(4);
	require 'pages/place_list.php';
	}
	elseif ($page == 'texts') {
		templateTop(5);
		require 'pages/text_list.php';
	}

	// content pages
	elseif ($page == 'about') {
		templateTop(6);
		require 'pages/about.php';
	}
	// in development
	elseif ($page == 'xpath') {
		templateTop(1);
		require 'pages/xpath.php';
	}
	// fall back: route to home page
	else {	
		templateTop(0);
		require 'pages/home.php';
	}
}

// template
templateBottom();	

?>
