<?php
templateTop($nav, 'home');
?>
<!-- hero section -->

<div class="container-fluid p-0 home-hero">
    <div class="row g-0 h-100">
        <div class="col-lg-6 p-2 p-md-4 p-lg-5 align-items-center bg-mira-opaque bg-gradient text-white">
            <div class="mx-3 pt-5 pb-4 px-md-5 text-shadow">

<h1 class="display-5 mb-4" style="font-weight: 400;">
Explore Ireland’s early medieval manuscript heritage
</h1>

<p>
MIrA is a 
catalogue of manuscripts written before c. AD 1000
in Ireland, or in Irish script, or with
other Irish connections.
</p>

<form class="mt-5 d-flex" action="/manuscripts" method="get">
	<input class="form-control form-control-lg me-2" 
		name="search" type="search"
		placeholder="Search by manuscript name, keyword or MIrA number" aria-label="Search">
	<button class="btn btn-success opacity-1" type="submit">Search</button>
</form>

<p class="mt-4">
Or browse: 
<a href="/manuscripts" class="badge bg-success text-decoration-none text-reset text-shadow-none">301 manuscripts</a>
<a href="/libraries" class="badge bg-success text-decoration-none text-reset text-shadow-none">72 libraries</a>
<a href="/places" class="badge bg-success text-decoration-none text-reset text-shadow-none">51 places</a>
</p>

<p class="mt-5 small">
	Read more <a href="/about" class="link-info">about the project</a>.
</p>

            </div>
        </div>

        <div class="col-lg-6"></div>
	</div>
</div>



<main class="container mt-2 pb-5" style="min-height: 400px; ">

<!-- Cover image credit -->
<div class="row">
	<div class="col-lg-6"></div>
	<div class="col-lg-6">
		<p class="small text-secondary">Cover image: <a href="/manuscript/187/">St Gall Priscian</a>, p. 224.</p>
	</div>
</div>

<div class="row mt-5 pb-4 gx-5">
	<div class="col-lg-6">

<h2 class="mt-3 mb-3">How to view IIIF images</h2>

<p>The catalogue makes use of <a href="https://iiif.io/" target="_blank">IIIF</a> services, where available (for 181 manuscripts currently). These facilitate:
</p>

<ul>
<li>Inline display of manuscript images.</li>
<li>Inclusion of information from library websites (where provided).</li>
<li>Viewing different manuscripts side-by-side, including manuscripts now divided between different libraries, e.g. 
	the <a href="/manuscript/205">Durham Gospel fragment</a>, 
	<a href="/manuscript/030">Eutyches binding fragment</a>,
	<a href="/manuscript/173">Fleury grammatical miscellany</a>, 
	or <a href="/manuscript/219">Isidore binding fragment</a>.
</ul>

<p>You can also compare side-by-side two or more manuscripts of your own selection using the <a href="mirador.php" target="_blank">Mirador viewer</a>. Watch this short video for instructions:
<p>

	</div>
	<div class="col-lg-6 pt-5">

<iframe class="rounded shadow" width="100%" height="380" src="https://www.youtube.com/embed/bFhAdUP1clw" title="YouTube video player" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>

	</div>
</div>