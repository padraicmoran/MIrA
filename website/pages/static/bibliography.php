
<h1 class="mt-5 mb-4">Bibliography</h2>

<?php

// output bibliography
if (file_exists('../data/other/bibliography.xml')) {
   $xml_bibl = simplexml_load_file('../data/other/bibliography.xml');
   echo '<ul class="list-unstyled">';
   foreach($xml_bibl as $bibl) {
      echo '<li class="mb-4">' . $bibl->asXML() . '</li>';
   }
   echo '</ul>';
}

?>
