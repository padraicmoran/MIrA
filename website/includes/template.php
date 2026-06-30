<?php
/*
Standard page template
*/
$nav = [
	'home' => 			['label' => 'Home',        'url' => '/',            'title' => 'MIrA • Manuscripts with Irish Associations • Information about early Irish book culture'],
	'manuscripts' =>	['label' => 'Manuscripts', 'url' => '/manuscripts', 'title' => 'MIrA • Manuscripts with Irish Associations • Manuscripts'],
	'libraries' => 		['label' => 'Libraries',   'url' => '/libraries',   'title' => 'MIrA • Manuscripts with Irish Associations • Libraries'],
	'people' => 		['label' => 'People',      'url' => '/people',      'title' => 'MIrA • Manuscripts with Irish Associations • People'],
	'places' =>			['label' => 'Places',      'url' => '/places',      'title' => 'MIrA • Manuscripts with Irish Associations • Places'],
	'texts' => 			['label' => 'Texts',       'url' => '/texts',       'title' => 'MIrA • Manuscripts with Irish Associations • Texts'],
	'about' => 			['label' => 'About',       'url' => '/about',       'title' => 'MIrA • Manuscripts with Irish Associations • About',
	'children' => [
		'bibliography' => 	['label' => 'Bibliography',     'url' => '/about/bibliography',    'title' => 'MIrA • Bibliography'],
		'versions' => 		['label' => 'Version history',  'url' => '/about/versions',		  'title' => 'MIrA • Version history'],
		'data' => 			['label' => 'Data management',  'url' => '/about/data',			  'title' => 'MIrA • Data management'],
	]],
];


function templateTop($nav, $activeNavID) {
	global $search;
	
	if (isset($nav[$activeNavID])) $title = $nav[$activeNavID]['title'];
	else $title = $nav['home']['title'];

?><!doctype html>
<html lang="en">
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">

	<title><?php echo $title; ?></title>

	<!-- metadata -->
	<meta name="description" lang="en" content="Manuscripts with Irish Associations (MIrA): Evidence for early Irish book culture, c. AD 600–1000."/>

	<meta name="DC.title" lang="en" content="<?php echo $title; ?>"/>
	<meta name="DC.description" lang="en" content="Manuscripts with Irish Associations (MIrA): Evidence for early Irish book culture, c. AD 600–1000."/>
	<meta name="DC.creator" content="Pádraic Moran" />
	<meta name="DC.publisher" content="Pádraic Moran, University of Galway" />
	<meta name="DC.type" content="Text" />
	<meta name="DC.format" content="text/html" />
	<meta name="DC.coverage" content="Global" />
	<meta name="DC.source" content="University of Galway" />
	<meta name="DC.language" content="en_IE" />

	<meta property="og:title" content="<?php echo $title; ?>" />
	<meta property="og:site_name" content="Manuscripts with Irish Associations (MIrA)" />    
	<meta property="og:description" content="Manuscripts with Irish Associations (MIrA): Evidence for early Irish book culture, c. AD 600–1000." />
	<meta property="og:url" content="https://<?php echo $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'] ?>" />
	<meta property="og:locale" content="en_US" />
	<meta property="og:type" content="website" />

	<!-- Bootstrap -->
	<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
	<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>

	<!-- Leaflet -->
	<link rel="stylesheet" href="https://unpkg.com/leaflet@1.7.1/dist/leaflet.css" integrity="sha512-xodZBNTC5n17Xt2atTPuE1HxjVMSvLVW9ocqUKLsCC5CXdbqCmblAshOMAS6/keqq/sMZMZ19scR4PsZChSR7A==" crossorigin=""/>
	<script src="https://unpkg.com/leaflet@1.7.1/dist/leaflet.js" integrity="sha512-XQoYMqMTK8LvdxXYG3nZ448hOEQiglfqkJs1NOQV44cWnUrBc8PkAOcXy20w0vlaXaVUearIOBhiXZ5V3ynxwA==" crossorigin=""></script>
	<script src="https://api.mapbox.com/mapbox.js/plugins/leaflet-fullscreen/v1.0.1/Leaflet.fullscreen.min.js"></script>
	<link href="https://api.mapbox.com/mapbox.js/plugins/leaflet-fullscreen/v1.0.1/leaflet.fullscreen.css" rel="stylesheet" />

	<!-- site styles -->
	<link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Roboto:300,400,500" />
	<link href="https://fonts.googleapis.com/css2?family=Source+Serif+4:opsz,wght@8..60,200..900&display=swap" rel="stylesheet">
	<link rel="stylesheet" href="/includes/mira.css" />

	<script src="/includes/mira.js" />

	<!-- favicons -->
	<link rel="apple-touch-icon" href="/images/favicons/apple-touch-icon.png" sizes="180x180">
	<link rel="icon" type="image/x-icon" href="/images/favicons/favicon-32x32.ico" sizes="32x32" type="image/png">
	<link rel="icon" type="image/x-icon" href="/images/favicons/favicon-16x16.ico" sizes="16x16" type="image/png">
	<link rel="icon" type="image/x-icon" href="/images/favicons/favicon.ico">

	<!-- Global site tag (gtag.js) - Google Analytics -->
	<script async src="https://www.googletagmanager.com/gtag/js?id=G-005FZL42EK"></script>
	<script>
	window.dataLayer = window.dataLayer || [];
	function gtag(){dataLayer.push(arguments);}
	gtag('js', new Date());

	gtag('config', 'G-005FZL42EK');
	</script>
</head>
<body class="bg-light">

<nav class="navbar navbar-expand-lg navbar-dark sticky-md-top" style="z-index: 2000">
	<div class="container-fluid px-5 py-2">

	<!-- site branding and mobile nav toggler -->
	 <div class="d-flex align-items-center flex-nowrap w-100 justify-content-between">
		<a class="navbar-brand d-flex align-items-center gap-2 flex-shrink-1" href="/">
			<span class="bg-white p-1 rounded fw-bold text-dark flex-shrink-0 me-2" style="color: #0e300e !important;">MIrA</span>
			<span class="site-title" style="white-space: normal; line-height: 1.2;">Manuscripts with Irish Associations</span>
		</a>
		<button class="navbar-toggler fs-5 p-2 flex-shrink-0" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
			<span class="navbar-toggler-icon"></span>
		</button>
	</div>


	<div class="collapse navbar-collapse" id="navbarSupportedContent">
		<ul class="navbar-nav ms-4 ms-auto me-auto mb-2 mb-lg-0">
	<?php
	foreach ($nav as $navID => $item):
		$isActive    = ($navID === $activeNavID);
		$hasChildren = !empty($item['children']);
	?>
	<?php if ($hasChildren): ?>
    <li class="nav-item dropdown d-flex align-items-center">
        <a class="nav-link<?= $isActive ? ' active' : '' ?>"
           href="<?= $item['url'] ?>"><?= $item['label'] ?></a>
        <a class="nav-link dropdown-toggle dropdown-toggle-split p-0"
           role="button" data-bs-toggle="dropdown"
           aria-expanded="false"><span class="visually-hidden">Toggle dropdown</span></a>
        <ul class="dropdown-menu">
		<?php foreach ($item['children'] as $childID => $child): ?>
            <li><a class="dropdown-item<?= ($childID === $activeNavID) ? ' active' : '' ?>"
                   href="<?= $child['url'] ?>"><?= $child['label'] ?></a></li>
		<?php endforeach ?>
        </ul>
    </li>
		<?php else: ?>
		<li class="nav-item">
			<a class="nav-link<?= $isActive ? ' active' : '' ?>"
			href="<?= $item['url'] ?>"><?= $item['label'] ?></a>
		</li>
	<?php endif ?>
	<?php endforeach ?>
		</ul>
		<form class="d-flex" action="/manuscripts" method="get">
			<input class="form-control ms-4 me-2" style="width: 220px; "
				name="search" type="search"
				placeholder="Keyword or MIrA number" aria-label="Search"
				value="<?= $search ?>">
			<button class="btn btn-success" type="submit">Search</button>
		</form>
	</div>

</nav>


<?php
	// main content wrapper
	// skip on home page
	if ($activeNavID != 'home') {
?>
<!-- main content holder -->
<main class="container mt-5 pb-5" style="min-height: 400px; ">
<?php
	}
}


function templateBottom() {
	global $version, $versionDate;
?>
</main>

<footer class="container-fluid mt-4 py-5 px-2 px-lg-5 border-top">
	<div class="container mb-0">
		<div class="row">
			<div class="col-lg-6 pb-2">

<h3 class="h4">Manuscripts with Irish Associations</h3>
<p>Irish manuscript culture before c. AD 1000</p>

<p class="mt-4">
	Access data via 
	<a target="_blank" href="https://github.com/padraicmoran/MIrA/tree/master/data">GitHub</a> |
	<a target="_blank" href="https://mira-sparql.universityofgalway.ie">SPARQL endpoint</a>
</p>

			</div>
			<div class="col-lg-6">

<h3 class="h5">How to cite:</h3>

<p>Pádraic Moran, <i>Manuscripts with Irish Associations</i> (<i>MIrA</i>)</i>, 
	version <?php echo $version; ?> (<?php echo $versionDate; ?>)
<?php
// URL and date
echo '&lt;<a class="text-reset" href="' . $_SERVER['REQUEST_URI'] . '">https://mira.ie' . $_SERVER['REQUEST_URI'] . '</a>&gt; ';
echo '(accessed ' . date("j F Y") . ')';
?>
</p>
			</div>
		</div>
	</div>
</footer>

<!-- Script for tooltips -->
<script language="JavaScript" type="text/javascript">

var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
  return new bootstrap.Tooltip(tooltipTriggerEl);
})

</script>

<!-- Default Statcounter code for MIrA https://www.mira.ie
-->
<script type="text/javascript">
var sc_project=12541456; 
var sc_invisible=1; 
var sc_security="0e71a91e"; 
</script>
<script type="text/javascript"
src="https://www.statcounter.com/counter/counter.js"
async></script>
<noscript><div class="statcounter"><a title="Web Analytics"
href="https://statcounter.com/" target="_blank"><img
class="statcounter"
src="https://c.statcounter.com/12541456/0/0e71a91e/1/"
alt="Web Analytics"></a></div></noscript>
<!-- End of Statcounter Code -->

</body>
</html>
<?php
}
?>