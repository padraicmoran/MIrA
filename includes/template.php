<?php
/*
Standard page template
*/

function templateTop($activeNav) {
	global $tidyURLs, $search;
	
	$navLabels = array(
		'Home',
		'Manuscripts',
		'Texts',
		'People',
		'Places',
		'About'	
	);
	if ($tidyURLs) {
		$navURLs = array(
			'/',
			'/mss',
			'/texts',
			'/people',
			'/places',
			'/about'	
		);
	}
	else {
		$navURLs = array(
			'/',
			'/index.php?page=mss',
			'/index.php?page=texts',
			'/index.php?page=people',
			'/index.php?page=places',
			'/index.php?page=about'	
		);
	}
	$pageTitles = array(
		'Manuscripts with Irish Associations (MIrA)',
		'MIrA • Manuscripts',
		'MIrA • Texts',
		'MIrA • People',
		'MIrA • Places',
		'MIrA • About'	
	);

?><!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.0-beta1/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-0evHe/X+R7YkIZDRvuzKMRqM+OrBnVFBL6DOitfPri4tjfHxaWutUpFmBp4vmVor" crossorigin="anonymous">
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.0-beta1/dist/js/bootstrap.bundle.min.js" integrity="sha384-pprn3073KE6tl6bjs2QrFaJGz5/SUsLqktiwsUTF55Jfv3qYSDhgCecCxMW52nD2" crossorigin="anonymous"></script>
<script src="https://unpkg.com/@popperjs/core@2"></script>


<link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Roboto:300,400,500">

<link rel="stylesheet" href="https://unpkg.com/leaflet@1.7.1/dist/leaflet.css" integrity="sha512-xodZBNTC5n17Xt2atTPuE1HxjVMSvLVW9ocqUKLsCC5CXdbqCmblAshOMAS6/keqq/sMZMZ19scR4PsZChSR7A==" crossorigin=""/>
<script src="https://unpkg.com/leaflet@1.7.1/dist/leaflet.js" integrity="sha512-XQoYMqMTK8LvdxXYG3nZ448hOEQiglfqkJs1NOQV44cWnUrBc8PkAOcXy20w0vlaXaVUearIOBhiXZ5V3ynxwA==" crossorigin=""></script>
<script src="https://api.mapbox.com/mapbox.js/plugins/leaflet-fullscreen/v1.0.1/Leaflet.fullscreen.min.js"></script>
<link href="https://api.mapbox.com/mapbox.js/plugins/leaflet-fullscreen/v1.0.1/leaflet.fullscreen.css" rel="stylesheet" />

<title><?php print $pageTitles[$activeNav]; ?></title>

<style type="text/css">

body {
	font-family: Roboto, sans-serif;
	font-size: 1.25em;
}
a, a:hover, .leaflet-container a {
	color: #8d2929;
}
a:hover {
	text-decoration: none;
}
a[href^="http://"]:after, a[href^="https://"]:after {
    content: "↗";
    margin-left: 2px;
    color: #aaaaaa;
    font-weight: bold;
}

a.btn {
	font-size: 0.9em;
}
span.btn {
	font-size: 0.6em;
}
a.navbar-brand {
	font-weight: bold;
	font-size: 1.1em;
}

.chartFullScreen {
	position: fixed;
	top: 10%;
	bottom: 10%;
	left: 0;
	width: 100%; 
	height: 90%;
	background-color: white;
}


</style>

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

<nav class="navbar navbar-expand-lg navbar-dark sticky-md-top bg-gradient shadow" style="background-color: #0e300e; z-index: 2000; ">
	<div class="container-fluid px-5 py-2">

	<a class="navbar-brand text-wrap" href="/">
	<span class="bg-white p-1 rounded fw-bold text-dark" style="color: #0e300e !important; ">MIrA</span> 
	<span class="text-nowrap">Manuscripts with Irish Associations</span>
	</a>

	<!-- span class="text-light small opacity-50"><a style="color: inherit; text-decoration: none; " href="/about">v0.2 (beta)</a></span -->

	<button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
	<span class="navbar-toggler-icon"></span>
	</button>


		<div class="collapse navbar-collapse" id="navbarSupportedContent">
			<ul class="navbar-nav ms-4 me-auto mb-2 mb-lg-0">
<?php
	for ($x = 0; $x < count($navLabels); $x ++) {
		print '<li class="nav-item"><a class="nav-link';
		if ($x == $activeNav) print ' active';
		print '" href="' . $navURLs[$x] . '">' . $navLabels[$x] . '</a></li>';
	}
?>
			</ul>

			<form class="d-flex" action="/index.php" method="get">
				<input type="hidden" name="page" value="mss">
				<input class="form-control me-2" name="search" type="search" placeholder="Search" aria-label="Search" value="<?php print $search ?>">
				<button class="btn btn-success" type="submit">Search</button>
			</form>
		</div>
	</div>
</nav>


<?php
// home page header
if ($activeNav == 0) print '<div class="container-fluid shadow p-0 overflow-auto bg-dark bg-gradient"><a href="/110"><img class="img-fluid" src="/images/header_0-3.jpg" alt="Detail from the Book of Armagh"></a></div>';
?>

<!-- main content holder -->
<div class="container mt-5 pb-5" style="min-height: 400px; ">
<?php
}


function templateBottom() {
	global $version;
?>
</div>

<div class="container-fluid bg-dark bg-gradient mt-4 p-5 text-light">
	<div class="container mb-0">

<p><b>Cite this page:</b><br> Pádraic Moran, <i>Manuscripts with Irish Associations</i> (<i>MIrA</i>)</i>, version <?php print $version; ?> 
<?php
// URL and date
print '&lt;<a class="text-light" href="' . $_SERVER['REQUEST_URI'] . '">http://www.mira.ie' . $_SERVER['REQUEST_URI'] . '</a>&gt; ';
print '[accessed ' . date("j F Y") . ']';
?>
</p>

	</div>
</div>

<!-- Script for tooltips -->
<script language="JavaScript" type="text/javascript">

var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
  return new bootstrap.Tooltip(tooltipTriggerEl);
})

</script>

<!-- Default Statcounter code for MIrA http://www.mira.ie
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
