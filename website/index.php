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

// routing based on receiving an entity ID
$entityRoutes = [	
	// pageID =>  [navID, PHP route]
	'manuscript' => ['manuscripts', 'pages/manuscript.php'],
    'library'    => ['libraries', 'pages/library.php'],
    'person'     => ['people', 'pages/person.php'],
    'place'      => ['places', 'pages/place.php'],
    'text'       => ['texts', 'pages/text.php'],
];
// other routes
$otherRoutes = [
    'home' 		  => ['home', 'pages/home.php'],
    'manuscripts' => ['manuscripts', 'pages/manuscript_list.php'],
    'libraries'   => ['libraries', 'pages/library_list.php'],
    'people'      => ['people', 'pages/person_list.php'],
    'places'      => ['places', 'pages/place_list.php'],
    'texts'       => ['texts', 'pages/text_list.php'],
    'about'       => ['about', 'pages/static/about.php'],
    'about__bibliography' => ['about', 'pages/static/bibliography.php'],
    'about__versions'    => ['about', 'pages/static/versions.php'],
    'about__data'        => ['about', 'pages/static/data.php'],
    'xpath'       => ['', 'pages/xpath.php'],   // in development
];

// route to entity details
$fallback = false;
if ($id) {
	// if page is recognised
	if (isset($entityRoutes[$page])) {
		templateTop($entityRoutes[$page][0]);
		require $entityRoutes[$page][1];
	}
	else $fallback = true;
}
// otherwise, route to a standard page
else {
	// if page is recognised
	if (isset($otherRoutes[$page])) {
		templateTop($otherRoutes[$page][0]);
		require $otherRoutes[$page][1];
	}
	else $fallback = true;
}
if ($fallback) {
	templateTop($otherRoutes['home'][0]);
	require $otherRoutes['home'][1];
}


// template
templateBottom();	

?>
