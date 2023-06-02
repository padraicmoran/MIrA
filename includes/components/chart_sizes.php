<?php
/* 
Bar chart for folio sizes
*/

function sizesChart($data) {
	global $libraries;

	//
	// compile chart data
	//
	$sizes = array();

	// cycle through entries
	$maxH = $minH = $totalH = 0;
	$maxW = $minW = $totalW = 0;
	foreach ($data as $ms) {
		// process height and width
		$h = intval($ms->description->page_h);
		$w = intval($ms->description->page_w);
		if ($h <> 0 && $w <> 0) {
			// prepare MS identifier
			$libraryID = strval($ms->identifier['libraryID']);
			$msIdentifier = $libraries[$libraryID]['city'] . ', ' . $libraries[$libraryID]['name'] . ', ' . $ms->identifier->shelfmark;

			// store value in array (for later computation)		
			$sizes[] = [ 
				$h,  
				$w, 
				$ms['id'],
				$msIdentifier
			];

			$totalH += $h;
			$totalW += $w;
			if ($h > $maxH) $maxH = $h;
			if ($w > $maxW) $maxW = $w;
			if ($h < $minH || $minH == 0) $minH = $h;
			if ($w < $minW || $minW == 0) $minW = $w;
		}
	}
	$cntSizes = count($sizes);

	// check whether data is available
	if ($cntSizes == 0) {
		print '<p>No data available for this list.</p>';
	}
	else {	

		// work out averages
		$avgH = floor($totalH / $cntSizes);
		$avgW = floor($totalW / $cntSizes);
		
		print '<p>';
		print 'Data available for ' . $cntSizes . ' ' . switchSgPl($cntSizes, 'manuscript', 'manuscripts') . ' (' . intval($cntSizes / count($data) * 100) . '% of this list). ';
		print 'Height range: ' . $minH . '–' . $maxH . ' mm (average ' . $avgH . ' mm). ';
		print 'Width range: ' . $minW . '–' . $maxW . ' mm (average ' . $avgW . ' mm). ';
		print '<br/>Heights charted below (with red line for average). Click on a bar to view manuscript.</p>';

		// sort asc
		usort($sizes, 'sortH');

		// chart settings
		// % padding (space for labels)
		$xPad = 5;
		$yPad = 11;	

		// x-scale
		$xAxisMin = 0;
		$xAxisMax = $cntSizes + 1;
		if ($cntSizes <= 10) $xAxisStep = 1;
		elseif ($cntSizes <= 30) $xAxisStep = 5;
		else $xAxisStep = 10;

		// y-scale
		$yAxisMin = 0;
		$yAxisMax = 400;
		$yAxisStep = 100;
		
		// chart
		print '<svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" width="100%" height="280" style="border: none;">';

		// write axes
		writeXAxis($xAxisMin, $xAxisMax, $xAxisStep, $xPad, $yPad);
		writeYAxis($yAxisMin, $yAxisMax, $yAxisStep, $xPad, $yPad);

		// draw bars
		
		$cntSizes = 0;

		foreach ($sizes as $ms) {
			$cntSizes ++;
			$label = $ms[3] . ': height ' . $ms[0] . ' mm';
			drawBar($cntSizes, $ms[0], $xAxisMin, $xAxisMax, $xPad, $yAxisMin, $yAxisMax, $yPad, 'blue', '/' . $ms[2], $label);
		}
		
		// draw average line
		print '<line x1="' . $xPad . '%" y1="' . getChartYcoord($avgH, $yAxisMin, $yAxisMax, $yPad) . '%" x2="' . (100 - $xPad) . '%" y2="' . getChartYcoord($avgH, $yAxisMin, $yAxisMax, $yPad) . '%" style="stroke:#cc3333;stroke-width:0.7" />';
		print '</svg>';

	}
}


function sortH($a, $b) {
	return strnatcmp($a[0], $b[0]);
}

?>
