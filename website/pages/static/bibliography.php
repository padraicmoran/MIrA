
<!-- Main column -->
<h2 class="mt-5 mb-4 display-5">Bibliography</h2>

<?php

// output bibliography
if (file_exists('../data/other/bibliography.xml')) {
   $xml_bibl = simplexml_load_file('../data/other/bibliography.xml');
   echo '<ul class="">';
   foreach($xml_bibl as $bibl) {
      echo '<li class="mb-3">' . $bibl->asXML() . '</li>';
   }
   echo '</ul>';
}

?>
