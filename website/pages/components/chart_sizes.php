<?php
/* 
Bar chart for folio sizes
*/

function chartSizes($data) {
	global $libraries;

	echo '<h3 class="mt-5 pt-2">Page sizes</h4>';

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
			$heading = makeMsHeading($ms);

			// store value in array (for later computation)		
			$sizes[] = [ 
				$h,  
				$w, 
				$ms['id'],
				$heading
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
		echo '<p>No data available for this list.</p>';
	}
	else {	

		// work out averages
		$avgH = floor($totalH / $cntSizes);
		$avgW = floor($totalW / $cntSizes);
		
		echo '<p>';
		echo 'Data available for ' . $cntSizes . ' ' . switchSgPl($cntSizes, 'manuscript', 'manuscripts') . ' (' . intval($cntSizes / count($data) * 100) . '% of this list). <br/>';
		echo 'Height range: ' . $minH . '–' . $maxH . ' cm (average ' . $avgH . ' cm). ';
		echo 'Width range: ' . $minW . '–' . $maxW . ' cm (average ' . $avgW . ' cm). <br/>';
		echo 'Heights charted below (with red line for average). Click on a bar to view manuscript.</p>';

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
		$yAxisMax = 40;
		$yAxisStep = 10;
		
		// chart
		echo '<svg xmlns="https://www.w3.org/2000/svg" xmlns:xlink="https://www.w3.org/1999/xlink" width="100%" height="280" style="border: none;">';

		// write axes
		writeXAxis($xAxisMin, $xAxisMax, $xAxisStep, $xPad, $yPad);
		writeYAxis($yAxisMin, $yAxisMax, $yAxisStep, $xPad, $yPad);

		// draw bars
		
		$cntSizes = 0;

		foreach ($sizes as $ms) {
			$cntSizes ++;
			$url = getLink('manuscript', $ms[1]);
			$label = $ms[3] . ': height ' . $ms[0] . ' cm';
			drawBar($cntSizes, $ms[0], $xAxisMin, $xAxisMax, $xPad, $yAxisMin, $yAxisMax, $yPad, 'blue', $url, $label);
		}
		
		// draw average line
		echo '<line x1="' . $xPad . '%" y1="' . getChartYcoord($avgH, $yAxisMin, $yAxisMax, $yPad) . '%" x2="' . (100 - $xPad) . '%" y2="' . getChartYcoord($avgH, $yAxisMin, $yAxisMax, $yPad) . '%" style="stroke:#cc3333;stroke-width:0.7" />';
		echo '</svg>';

	}
}


function sortH($a, $b) {
	return strnatcmp($b[0], $a[0]);
}

?>
