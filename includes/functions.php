<?php
/* 
Some general functions customised for this website
*/

// handle tidy URL links
function getLink($type, $id) {
	global $tidyURLs;
	$link = '#';
	if ($tidyURLs) {
		if ($type == 'ms') $link = '/' . $id;
		else if ($type == 'texts' || $type == 'people' || $type == 'places') $link = '/' . $type . '/' . $id;
	}
	else {
		if ($type == 'mss' || $type == 'texts' || $type == 'people' || $type == 'places') $link = '/index.php?page=' . $type . '&id=' . $id;
	}
	return $link;
}


// if row data is potentially in the form of an array, write a row for each array item
function handleRowArray($header, $value, $link) {
	if(is_array($value)) {
		for ($x = 0; $x < count($value); $x++) {
			if (is_array($link)) {
				if (! empty($link)) writeRow($header, $value[$x], $link[$x]);
				else writeRow($header, $value[$x], '');
			}
			else writeRow($header, $value[$x], $link);
		}	
	}
	else writeRow($header, $value, $link);
}

// add a link to Thesaurus Palaeohibernicus refs if possible
function parseThesaurusRef($ref) {
	$urlBase = array(
		'1'=>'https://archive.org/details/thesauruspalaeo01stok/page/',
		'2'=>'https://archive.org/details/thesauruspalaeo02stok/page/'
	);

	$x = '';
	$refs = explode(', ', $ref);		// handle multiple Thes. refs
	foreach ($refs as $r) {
		if ($x != '') $x .= ', ';		// add a separator if necessary

		$parts = preg_split('/[\.\-–]/', $r);
		if (count($parts) >= 2 && ($parts[0] == '1' || $parts[0] == '2')) {			// if there appears to be a volume and page
			$x .= '<a target="_blank" href="' . $urlBase[$parts[0]] . $parts[1] . '">'  . $r . '</a>';
		}
		else $x .= $r;						// just return the plain ref if something doesn't work
	}
	return $x;
}

// replace XML cross-references (data) with HTML links (application)
function processData($str) {
	global $tidyURLs;

	$str = preg_replace('/<ms id="([0-9]*)">/', '<a href="\1">', $str);
	$str = preg_replace('/<\/ms>/', '</a>', $str);

	if ($tidyURLs) {
		$str = preg_replace('/<person id="([a-z0-9_\-]*)">/', '<a href="/people/\1">', $str);
		$str = preg_replace('/<place id="([a-z0-9_\-]*)">/', '<a href="/places/\1">', $str);
		$str = preg_replace('/<text id="([a-z0-9_\-]*)">/', '<a href="/texts/\1">', $str);
	}
	else {
		$str = preg_replace('/<person id="([a-z0-9_\-]*)">/', '<a href="index.php?page=people&id=\1">', $str);
		$str = preg_replace('/<place id="([a-z0-9_\-]*)">/', '<a href="index.php?page=places&id=\1">', $str);
		$str = preg_replace('/<text id="([a-z0-9_\-]*)">/', '<a href="index.php?page=texts&id=\1">', $str);
	}
	
	$str = preg_replace('/<\/person>/', '</a>', $str);
	$str = preg_replace('/<\/place>/', '</a>', $str);
	$str = preg_replace('/<\/text>/', '</a>', $str);

	return $str;
}

// write table row with left cell as header; add a link if supplied
function writeRow($header, $value, $link) {
	if ($value == '' && $link != '') $value = '[external link]';
	if ($value != '') {
		$target = '';
		if (substr($link, 0, 4) == 'http') $target = '_blank';
		
		if ($link != '') print '<tr><th width="400">' . $header . '</th><td><a href="' . $link . '" target="' . $target . '">' . $value . '</a></td></tr>';
		else print '<tr><th width="400">' . $header . '</th><td>' . $value . '</td></tr>';
	}
}


?>
