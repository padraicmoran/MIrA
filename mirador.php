<?php
require 'includes/application.php';
$manifestString = getAllManifests();

?><!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Roboto:300,400,500">

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0-beta3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-eOJMYsd53ii+scO/bJGFsiCZc+5NDVN2yr8+0RDqr0Ql0h+rP48ckxlpbzKgwra6" crossorigin="anonymous">

<link rel="stylesheet" href="https://unpkg.com/leaflet@1.7.1/dist/leaflet.css" integrity="sha512-xodZBNTC5n17Xt2atTPuE1HxjVMSvLVW9ocqUKLsCC5CXdbqCmblAshOMAS6/keqq/sMZMZ19scR4PsZChSR7A==" crossorigin=""/>
<script src="https://unpkg.com/leaflet@1.7.1/dist/leaflet.js" integrity="sha512-XQoYMqMTK8LvdxXYG3nZ448hOEQiglfqkJs1NOQV44cWnUrBc8PkAOcXy20w0vlaXaVUearIOBhiXZ5V3ynxwA==" crossorigin=""></script>


<title>MIrA: Manuscripts with Irish Associations</title>

<!-- Global site tag (gtag.js) - Google Analytics -->
<script async src="https://www.googletagmanager.com/gtag/js?id=G-005FZL42EK"></script>
<script>
  window.dataLayer = window.dataLayer || [];
  function gtag(){dataLayer.push(arguments);}
  gtag('js', new Date());

  gtag('config', 'G-005FZL42EK');
</script>
</head>

<body>

<script src="https://unpkg.com/mirador@latest/dist/mirador.min.js"></script>

<!-- By default uses Roboto font. Be sure to load this or change the font -->
<link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Roboto:300,400,500">
<!-- Container element of Mirador whose id should be passed to the instantiating call as "id" -->
<div id="miradorViewer"></div>

<script type="text/javascript">
var mirador = Mirador.viewer({
	id: "miradorViewer",
	catalog: [ 
	<?php print $manifestString; ?> 
	],
	thumbnailNavigation: {
		defaultPosition: 'far-bottom'
	},
	workspace: {
		showZoomControls: true
	}
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
