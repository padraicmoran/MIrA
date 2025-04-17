
<!-- Main column -->
<h2 class="mb-3 display-5">About MIrA</h2>

<div class="row gx-5">
<div class="col-lg-8">

<p>Data compiled and website developed by <a target="_blank" href="http://www.pmoran.ie/">Pádraic Moran</a>, Classics, University of Galway (Ireland).</p>

<p>Additions, corrections, suggestions welcome: <a href="mailto:padraic.moran@universityofgalway.ie">padraic.moran@universityofgalway.ie</a>.</p>

<p>The list was compiled initially from the following sources (principally <i>CLA</i> and Bischoff in the first instance):<!-- a href="index.php?page=biblio">*</a --></p>
<ul>
<li>Bernhard Bischoff, <i>Südostdeutschen Schreibschulen und Bibliotheken in der Karolingerzeit</i>, 2 vols (Wiesbaden, 1940–1980).</li>
<li>Bernhard Bischoff, <i>Katalog der festländischen Handschriften des neunten Jahrhunderts</i>, 3 vols (Wiesbaden, 1998–2014).</li>
<li>E. A. Lowe, <i>Codices Latini Antiquiores</i>, 12 vols (Oxford, 1934–1971), digitised and updated at Mark Stansbury, <a target="_blank" href="http://elmss.nuigalway.ie/">Early Latin Manuscripts</a>.</li>
</ul>



<h3 id="biblio" class="mt-5">Bibliography</h3>
<?php

// output bibliography
if (file_exists('../data/bibliography.xml')) {
   $xml_bibl = simplexml_load_file('../data/bibliography.xml');
   print '<ul class="">';
   foreach($xml_bibl as $bibl) {
      print '<li class="mb-2">' . $bibl->asXML() . '</li>';
   }
   print '</ul>';
}

?>


<h3 id="version" class="mt-5">Version history</h3>

<p>Current version:</p>

<ul>
<li>0.6.2 (beta), 13 February 2025. Measurement units converted to cm.</li>
<li>0.6.1 (beta), 12 February 2025. 7 new additions: MSS 294–300. Minor corrections.</li>
</ul>

<p>Previous versions:</p>

<ul>
<li>0.6 (beta), 29 January 2025. New "Miscellaneous" category for minor Irish associations. Minor corrections and additions.</li>
<li>0.5 (beta), 29 November 2023. Improvements to maps, network graphs, linked data.</li>
<li>0.4 (beta), 18 July 2023. Network diagrams. References for Alexander, McGurk, Trismegistos. New data for dimensions and folio counts (supplied by Lily Forrest). Developments to text, people, places indexes.</li>
<li>0.3 (beta), 1 March 2023. Export manuscript list as CSV. Permanent references for multiple units. Minor corrections. </li>
<li>0.2 (beta), 20 July 2022. Added data tables for texts, people, places (in development). New charts for page sizes, number of folios. Several minor interface improvements. Several minor corrections/improvements to data. </li>
<li>0.1 (beta): May 2021. Initial version. Manuscript list, date chart, libraries map, basic search.</li>
</ul>

<p>View source code and data at:
<a href="https://github.com/padraicmoran/MIrA">https://github.com/padraicmoran/MIrA</a>





</div>

<!-- Side column -->
<div class="col-lg-4 border-start">

<h3 class="">Acknowledgements</h3>

<p>Sincere thanks to the following for additions, corrections and suggestions: </p>
<ul class="list-unstyled">
<li>Jacopo Bisagni</li>
<li>Lily Forrest</li>
<li>Truc Ha Nguyen</li>
<li>Zdzisław Koczarski</li>
<li>David Stifter</li>
<li>Immo Warntjes</li>
<li>Brega Webb</li>
</ul>


</div>
</div>
