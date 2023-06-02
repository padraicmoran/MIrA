<?php
/*
General functions not specific to this website
*/

function cleanInput($key) {
	if (isset($_GET[$key])) return htmlspecialchars($_GET[$key], ENT_QUOTES, 'UTF-8');
}


// return appropriate number of noun (e.g. "1 child", "3 children")
function switchSgPl($val, $sg, $pl) {
	if ($val == 1) return $sg;
	else return $pl;
}


// write a form select opion
function writeOption($val, $label, $currentVal) {
		print '<option value="'. $val . '"';
		if ($val == $currentVal) print ' selected';
		print '>' . $label . '</option>';
}

// replace letters with diactrics, etc. with plain text letters
function simpleText($str) {
	$str = mb_strtolower($str);
	$subs = array(
		'á' => 'a',
		'é' => 'e',
		'í' => 'i',
		'ó' => 'o',
		'ú' => 'u',
		'à' => 'a',
		'è' => 'e',
		'ì' => 'i',
		'ò' => 'o',
		'ù' => 'u',
		'ä' => 'a',
		'ë' => 'e',
		'ï' => 'i',
		'ö' => 'o',
		'ü' => 'u',
		'ł' => 'l'
		
	);
	return strtr($str, $subs);
}

?>
