<?php

// process search inputs, return resutls

function searchMSS($xml_mss, $type, $target) {
	global $libraries, $msCategories;

    // keyword search
    if ($type == 'keyword') {
		// pre-search: check library information for keyword; include any matching IDs in overall search
		$xPathLibraries = '';
		foreach ($libraries as $lib) {
			if (strpos($lib['searchIndex'], $target) !== false) $xPathLibraries .= '| //identifier[@libraryID="' . $lib['id'] . '"]//ancestor::manuscript ';
		}

		//	$results = $xml_mss->xpath('//*[contains(text(),"' . $search . '")]//ancestor::manuscript');
		//	hack for case-sensitivity (needed for XPath v1): make both input & data lower case and remove diacritics
        $transFrom =    'ABCDEFGHIJKLMNOPQRSTUVWXYZäëïöüÄËÏÖÜáéíóúÁÉÍÓÚàèìòùÀÈÌÒÙ';
        $transTo =      'abcdefghijklmnopqrstuvwxyzaeiouaeiouaeiouaeiouaeiouaeiou';
        $simpleSearch = strtr(utf8_decode($target), utf8_decode($transFrom), $transTo);
        $results = $xml_mss->xpath('
			//*[contains(translate(text(), "' . $transFrom . '", "' . $transTo . '"),"' . $simpleSearch . '")][not(self::coords|self::link|self::private_notes)]
			//ancestor::manuscript
			' . $xPathLibraries);
	}

    // browse by category
    elseif ($type == 'category') {
		if (isset($msCategories[$target])) {
			$results = $xml_mss->xpath('//manuscript/notes/categories[contains(text(), \'' . $target . '\')]/ancestor::manuscript');
		}
	}

    // browse by library
	elseif ($type == 'library') {
		$results = $xml_mss->xpath('//identifier[@libraryID="' . $target . '"]/ancestor::manuscript');
	}

    // XPath search
	elseif ($type == 'xpath') {
		$results = $xml_mss->xpath($target);
	}

    // no search: show all entries
	else {
		$results = $xml_mss->manuscript;
	}
	return $results;
}


?>