<?php
/* 
Bar chart for number of folios
*/

function chartFolios($data) {
	global $libraries;

	print '<h3 class="mt-5 pt-2">Folio count</h4>';

	//
	// compile chart data
	//
	$fols = array();

	// cycle through entries
	$maxF = $minF = $totalF = 0;
	foreach ($data as $ms) {

		// process num of folios
		$f = intval($ms->description->folios);
		if ($f <> 0) {
			// prepare MS identifier
			$heading = makeMsHeading($ms);

			// store value in array (for later computation)		
			$fols[] = [ 
				$f,  
				$ms['id'],
				$heading
			];

			$totalF += $f;
			if ($f > $maxF) $maxF = $f;
			if ($f < $minF || $minF == 0) $minF = $f;
		}
	}
	$cntFols = count($fols);

	// check whether data is available
	if ($cntFols == 0) {
		print '<p>No data available for this list.</p>';
	}
		else {	

		// work out average
		$avgF = floor($totalF / $cntFols);
		
		print '<p>';
		print 'Data available for ' . $cntFols . ' ' . switchSgPl($cntFols, 'manuscript', 'manuscripts') . ' (' . intval($cntFols / count($data) * 100) . '% of this list). ';
		print 'Range of folio numbers: ' . $minF . '–' . $maxF . ' (average ' . $avgF . '). ';
		print '<br>Charted below (with red line for average). Click on a bar to view manuscript.</p>';

		// sort asc
		usort($fols, 'sortF');


		// chart settings
		// % padding (space for labels)
		$xPad = 5;
		$yPad = 11;	

		// x-scale
		$xAxisMin = 0;
		$xAxisMax = $cntFols + 1;
		if ($cntFols <= 10) $xAxisStep = 1;
		elseif ($cntFols <= 30) $xAxisStep = 5;
		else $xAxisStep = 10;

		// y-scale
		$yAxisMin = 0;
		if ($maxF <= 100) {
			$yAxisMax = 100;
			$yAxisStep = 10;
		}
		else {
			$yAxisMax = 400;
			$yAxisStep = 100;
		}
				
		// chart
		print '<div id="chartHolder">';
		print '<svg id="chart" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" width="100%" height="280" style="border: none;">';

		// write axes
		writeXAxis($xAxisMin, $xAxisMax, $xAxisStep, $xPad, $yPad);
		writeYAxis($yAxisMin, $yAxisMax, $yAxisStep, $xPad, $yPad);

		// draw bars
		
		$cntFols = 0;

		foreach ($fols as $ms) {
			$cntFols ++;
			$label = $ms[2] . ': ' . $ms[0] . ' ' . switchSgPl($ms[0], 'folio', 'folios');
			drawBar($cntFols, $ms[0], $xAxisMin, $xAxisMax, $xPad, $yAxisMin, $yAxisMax, $yPad, 'darkred', '/' . $ms[1], $label);
		}
		
		// draw average line
		print '<line x1="' . $xPad . '%" y1="' . getChartYcoord($avgF, $yAxisMin, $yAxisMax, $yPad) . '%" x2="' . (100 - $xPad) . '%" y2="' . getChartYcoord($avgF, $yAxisMin, $yAxisMax, $yPad) . '%" style="stroke:#cc3333;stroke-width:0.7" />';
		print '</svg>';
		print '</div>';

	}
}

function sortF($a, $b) {
	return strnatcmp($a[0], $b[0]);
}

?>
