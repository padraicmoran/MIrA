<?php
require 'pages/components/subnav.php';

templateTop($nav, 'about');
writeSubnav($nav, 'about', 'versions');
?>

<h1 class="mt-5 mb-4">Version history</h1>

<p>Current version:</p>

<ul>
<li>1.0, 18 June 2026. Linked data implemented in var</li>
</ul>

<p>Previous versions:</p>

<ul>
<li>0.7 (beta), 9 June 2026. New data for columns and lines (supplied by Chiara Corongiu).</li>
<li>0.6.2 (beta), 13 February 2025. Measurement units converted to cm.</li>
<li>0.6.1 (beta), 12 February 2025. 7 new additions (mostly old palimpsests): MSS 
   <a href="/294">294</a>, 
   <a href="/295">295</a>, 
   <a href="/296">296</a>,
   <a href="/297">297</a>,
   <a href="/298">298</a>,
   <a href="/299">299</a>,
   <a href="/300">300</a>.
   Minor corrections.</li>
<li>0.6 (beta), 29 January 2025. New "Miscellaneous" category for minor Irish associations. Minor corrections and additions.</li>
<li>0.5 (beta), 29 November 2023. Improvements to maps, network graphs, linked data.</li>
<li>0.4 (beta), 18 July 2023. Network diagrams. References for Alexander, McGurk, Trismegistos. New data for dimensions and folio counts (supplied by Lily Forrest). Developments to text, people, places indexes.</li>
<li>0.3 (beta), 1 March 2023. Export manuscript list as CSV. Permanent references for multiple units. Minor corrections. </li>
<li>0.2 (beta), 20 July 2022. Added data tables for texts, people, places (in development). New charts for page sizes, number of folios. Several minor interface improvements. Several minor corrections/improvements to data. </li>
<li>0.1 (beta): May 2021. Initial version. Manuscript list, date chart, libraries map, basic search.</li>
</ul>

<p>View source code and data at:
<a href="https://github.com/padraicmoran/MIrA">https://github.com/padraicmoran/MIrA</a>


