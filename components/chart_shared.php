<?php
/* 
Generic chart functions:
Convert X and Y values to image co-ordinates
Draw axis lines
Draw a bar
*/

// calculate co-ordinate values
// as % of chart area, taking into account padding for axis labels
// y-values work upwards from the bottom of the chart
function getChartXcoord($val, $xAxisMin, $xAxisMax, $xPad) {
	return (($val - $xAxisMin) / ($xAxisMax - $xAxisMin) * (100 - $xPad - $xPad)) + $xPad;
}
function getChartYcoord($val, $yAxisMin, $yAxisMax, $yPad) {
	return (100 - $yPad - $yPad) - (($val - $yAxisMin) / ($yAxisMax - $yAxisMin) * (100 - $yPad - $yPad)) + $yPad;
}

// generic x-axis
function writeXAxis($xAxisMin, $xAxisMax, $xAxisStep, $xPad, $yPad) {
	for ($n = $xAxisMin; $n < $xAxisMax; $n += $xAxisStep) {			// omit final number (last point is just for padding)
		$x = getChartXcoord($n, $xAxisMin, $xAxisMax, $xPad);
		// labels
		print '<text x="' . ($x - 1)  . '%" y="' . (100 - $yPad + 10) . '%" fill="#666666">' . $n . '</text>';
		// markers
		print '<line x1="' . $x . '%" y1="' . (100 - $yPad) . '%" x2="' . $x . '%" y2="' . (100 - $yPad + 2) . '%" style="stroke:#666666;stroke-width:0.5" />';
	}
	// baseline
	print '<line x1="' . $xPad . '%" y1="' . (100 - $yPad) . '%" x2="' . (100 - $xPad) . '%" y2="' . (100 - $yPad) . '%" style="stroke:#666666;stroke-width:0.5" />';
}

// generic y-axis
function writeYAxis($yAxisMin, $yAxisMax, $yAxisStep, $xPad, $yPad) {
	for ($n = $yAxisMin; $n <= $yAxisMax; $n += $yAxisStep) {
		$y = getChartYcoord($n, $yAxisMin, $yAxisMax, $yPad);
		// labels
		print '<text x="' . ($xPad - 1)  . '%" y="' . ($y + 1) . '%" fill="#666666" text-anchor="end">' . $n . '</text>';
		// markers & background lines
		if ($n > $yAxisMin) {
			print '<line x1="' . ($xPad - 0.6) . '%" y1="' . $y . '%" x2="' . ($xPad) . '%" y2="' . $y . '%" style="stroke:#666666;stroke-width:0.5" />';
			print '<line x1="' . ($xPad) . '%" y1="' . $y . '%" x2="' . (100 - $xPad) . '%" y2="' . $y . '%" style="stroke:#cccccc;stroke-width:0.5" />';
		}
	}
	// baseline
	print '<line x1="' . $xPad . '%" y1="' . $yPad . '%" x2="' . $xPad . '%" y2="' . (100 - $yPad) . '%" style="stroke:#666666;stroke-width:0.5" />';
}

// draw generic bar
function drawBar($xVal, $yVal, $xAxisMin, $xAxisMax, $xPad, $yAxisMin, $yAxisMax, $yPad, $colour, $link, $label) {
	$w = ((100 - $xPad) / ($xAxisMax + 1 - $xAxisMin)) * 0.85;	
	$x = getChartXcoord($xVal, $xAxisMin, $xAxisMax, $xPad) - ($w / 2);

	$y = getChartYcoord($yVal, $yAxisMin, $yAxisMax, $yPad);
	if ($xVal >= $xAxisMin && $xVal <= $xAxisMax) {
		if ($link <> '') print '<a xlink:href="' . $link . '" title="' . $label . '" data-bs-toggle="tooltip">';
		print '<rect x="' . $x . '%" y="' . $y . '%" width="' . $w . '%" height="' . (100 - $yPad - $y) . '%" style="fill:' . $colour . ';stroke:none;fill-opacity:0.5"><title></title></rect>';
		if ($link <> '') print '</a>';
	}
}

?>
