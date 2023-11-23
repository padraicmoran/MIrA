<?php
/* 
Bar chart for manuscript dates
*/

function chartDates($data) {
	//
	// compile chart data
	//
	$years = array();
	
	// cycle through entries
	$maxTotal = 0;
	foreach ($data as $ms) {
		// get one mid-point year
		$thisYear = round(($ms->history->term_ante + $ms->history->term_post) / 2);

		// move back to nearest third of a century;
		$thisYear = floor(floor($thisYear / (100/3)) * (100/3));

		// convert to string for processing in array
		$thisYear = strval($thisYear);
		
		if (isset($years[$thisYear])) $years[$thisYear] += 1;
		else $years[$thisYear] = 1;
		if ($years[$thisYear] > $maxTotal) $maxTotal = $years[$thisYear];
	}

	//
	// chart settings
	// % padding (space for labels)
	$xPad = 5;
	$yPad = 11;	

	// x-scale
	$xAxisMin = 500;
	$xAxisMax = 1000;
	$xAxisStep = 100;

	// y-scale
	$yAxisMin = 0;
	if ($maxTotal <= 5) {
		$yAxisMax = 5;
		$yAxisStep = 1;
	}
	elseif ($maxTotal <= 10) {
		$yAxisMax = 10;
		$yAxisStep = 2;
	}
	elseif ($maxTotal <= 20) {
		$yAxisMax = 20;
		$yAxisStep = 5;
	}
	else {
		$yAxisMax = (floor($maxTotal / 10) * 10) + 10;
		$yAxisStep = 10;
	}
	

	// chart
	print '<svg width="100%" height="280" style="border: none;">';

	// write axes
	writeXAxis($xAxisMin, $xAxisMax, $xAxisStep, $xPad, $yPad);
	writeYAxis($yAxisMin, $yAxisMax, $yAxisStep, $xPad, $yPad);

	// draw bars
	$colour = 'green';
	foreach ($years as $xVal => $yVal) {
		// not a standard bar chart, so needs custom calculation
		$x = getChartXcoord($xVal, $xAxisMin, $xAxisMax, $xPad);
		$w = getChartXcoord($xVal + (100/3), $xAxisMin, $xAxisMax, $xPad) - $x;
		$y = getChartYcoord($yVal, $yAxisMin, $yAxisMax, $yPad);
		if ($xVal >= $xAxisMin && $xVal <= $xAxisMax) print '<rect x="' . ($x + 1) . '%" y="' . $y . '%" width="' . ($w - 1) . '%" height="' . (100 - $yPad - $y) . '%" style="fill:' . $colour . ';stroke:none;fill-opacity:0.5"><title>' . $yVal . ' MSS</title></rect>';	
	}
	
	print '</svg>';

}

?>
