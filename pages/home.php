

<div class="col-lg-9">

<h2 class="mb-4 display-5">Manuscripts with Irish Associations (MIrA)</h2>

<h3 class="mb-4 display-6">Evidence for early Irish book culture, c. AD 600–1000</h3>

<p>This resource (still in development) aims to provide useful information for researchers on early Irish manuscript culture.
It currently contains a catalogue of <a href="/mss"><b><?php print $totalMSS; ?> manuscripts</b></a> from before c. AD 1000 that can be assigned to at least one of the following categories:
</p>

<p><?php listCategories(); ?>
</p>

<p>Read <a href="/about">more about the project</a>.</p>

</div>


<?php
mapLibraries($xml_mss);
?>


<div class="row mt-5">
	<div class="col-lg-6">

<h3>IIIF images</h3>

<p>The catalogue makes use of <a href="https://iiif.io/" target="_blank">IIIF</a> services, where available (for 148 manuscripts currently). These facilitate:
</p>

<ul>
<li>Inline display of manuscript images.</li>
<li>Inclusion of information from library websites (where provided).</li>
<li>Viewing different manuscripts side-by-side, including manuscripts now divided between different libraries, e.g. 
	the <a href="205">Durham Gospel fragment</a>, 
	<a href="030">Eutyches binding fragment</a>,
	<a href="173">Fleury grammatical miscellany</a>, 
	or <a href="219">Isidore binding fragment</a>.
</ul>

<p>You can also compare side-by-side two or more manuscripts of your own selection using the <a href="mirador.php" target="_blank">Mirador viewer</a>. Watch this short video for instructions:
<p>

	</div>
	<div class="col-lg-6 pt-5">

<iframe class="rounded shadow" width="100%" height="380" src="https://www.youtube.com/embed/bFhAdUP1clw" title="YouTube video player" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>

	</div>
</div>

<div class="col-lg-9">

	<h3>Linked Open Data</h3>

<p>This resource aims to conform as fully as possible to <a href="https://www.go-fair.org/fair-principles/">FAIR Principles</a>, making digital assets 
Findable, Accessible, Interoperable, and Reuseable.
</p>

<ul>
<li>Persistent URLs (URIs) are supplied to identify manuscripts, texts, people and places. 
</li>

<li>All data is available to 
   download in XML format (from this site and on <a href="https://github.com/padraicmoran/MIrA/tree/master/data">GitHub</a>).
   </li>

<li>Wherever possible, data is linked to other Linked Open Data resources.
</li>

<li>Data may be reused under the <a href="https://creativecommons.org/licenses/by-nc-sa/4.0/">Creative Commons BY-NC-SA 4.0</a> licence.: </br>
</li>

<li>Work is in progress to make data accessible in machine-readable format (probably RDF).
</li>
</ul>

<a href="https://creativecommons.org/licenses/by-nc-sa/4.0/"><img class="mb-2" style="width: 200px" src="https://mirrors.creativecommons.org/presskit/buttons/88x31/png/by-nc-sa.png" alt="CC BY-NC-SA" /></a>

</div>
