<?php
require 'includes/application.php';

// get inputs
$page = cleanInput('page') ?? '';
$id = cleanInput('id') ?? '';
$search = cleanInput('search') ?? '';

// if 404 called by server (see .htaccess)
if ($page == '404') {
	require 'pages/static/404.php';
	templateBottom();	
	exit;
}

// trim any zero padding on IDs
$id = ltrim($id, '0');

// if search is a number, shortcut to manuscript page
if ((int) $search > 0 && (int) $search <= $totalMSS) {
	$page = 'manuscript';
	$id = $search;
}

// routing based on receiving an entity ID
$entityRoutes = [	
	// pageID =>  [navID, PHP route]
	'manuscript' => 'pages/manuscript.php',
    'library'    => 'pages/library.php',
    'person'     => 'pages/person.php',
    'place'      => 'pages/place.php',
    'text'       => 'pages/text.php',
];
// other routes
$otherRoutes = [
    'home' 		  			=> 'pages/home.php',
    'manuscripts' 			=> 'pages/manuscript_list.php',
    'libraries'   			=> 'pages/library_list.php',
    'people'      			=> 'pages/person_list.php',
    'places'      			=> 'pages/place_list.php',
    'texts'       			=> 'pages/text_list.php',
    'about'       			=> 'pages/static/about.php',
    'about__bibliography' 	=> 'pages/static/bibliography.php',
    'about__versions'    	=> 'pages/static/versions.php',
    'about__data'  	      	=> 'pages/static/data.php',
    'xpath'       			=> 'pages/xpath.php',   // in development
];

// route to entity details
$fallback = false;
if ($id) {
	// if page is recognised
	if (isset($entityRoutes[$page])) {
		require $entityRoutes[$page];
	}
	else $fallback = true;
}
// otherwise, route to a standard page
else {
	// if page is recognised
	if (isset($otherRoutes[$page])) {
		require $otherRoutes[$page];
	}
	else $fallback = true;
}
if ($fallback) {
	require $otherRoutes['home'];
}


// lower template, standard on all pages
templateBottom();	

?>