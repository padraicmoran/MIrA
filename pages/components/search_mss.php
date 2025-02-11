<?php

// process search inputs, return resutls

function searchMSS($xml_mss, $type, $target) {
	global $libraries, $msCategories;
	$xpath = '';

    // keyword search
    if ($type == 'keyword') {
		// pre-search: check library information for keyword; include any matching IDs in overall search
		$xPathLibraries = '';
		foreach ($libraries as $lib) {
			if (strpos($lib['searchIndex'], strtolower($target)) !== false) $xPathLibraries .= 'or identifier[@libraryID="' . $lib['id'] . '"] ';
		}

		//	hack for case-sensitivity (needed for XPath v1): make both input & data lower case and remove diacritics
        $transFrom =    'ABCDEFGHIJKLMNOPQRSTUVWXYZäëïöüÄËÏÖÜáéíóúÁÉÍÓÚàèìòùÀÈÌÒÙ';
        $transTo =      'abcdefghijklmnopqrstuvwxyzaeiouaeiouaeiouaeiouaeiouaeiou';
//        $simpleSearch = strtr(utf8_decode($target), utf8_decode($transFrom), $transTo);
        $simpleSearch = strtr($target, $transFrom, $transTo);
        $xpath = '
			manuscript[
				(contains(translate(., "' . $transFrom . '", "' . $transTo . '"), "' . $simpleSearch . '") 
				' . $xPathLibraries . ') 
				and notes[not(contains(@categories, "#excl"))]
				]';
	}

    // browse by category
    elseif ($type == 'category') {
		if (isset($msCategories[$target])) {
			$xpath = 'manuscript[notes[contains(@categories, \'#' . $target . '\')]]';
		}
	}

    // browse by library
	elseif ($type == 'library') {
		$xpath = 'manuscript[identifier[@libraryID="' . $target . '"] and notes[not(contains(@categories, "#excl"))]]';
	}

   // XPath search
	elseif ($type == 'xpath') {
		$xpath = $target;
	}

    // no search: show all entries
	else {
		$xpath = 'manuscript[notes[not(contains(@categories, "#excl"))]]';
	}

	return $xml_mss->xpath($xpath);
}

?>