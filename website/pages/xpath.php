<?php

if (isset($_GET['xpath'])) $xpath = $_GET['xpath'];
else $xpath = null;
$output = cleanInput('output') ?? '';

?>
<h2>Advanced query</h2>

<form>
<input type="hidden" name="page" value="xpath"/>

<textarea class="form-control" name="xpath" rows="5"><?php echo $xpath; ?></textarea>

<div>
Results as: &nbsp; 
table <input class="form-check-input" type="radio" name="output" value="" <?php if ($output == '') echo ' checked'; ?>> &nbsp; 
list of IDs <input class="form-check-input" type="radio" name="output" value="list" <?php if ($output == 'list') echo ' checked'; ?>> &nbsp; 
raw output <input class="form-check-input" type="radio" name="output" value="raw" <?php if ($output == 'raw') echo ' checked'; ?>> &nbsp; 
</div>

<button type="submit" class="btn btn-primary mb-3">Submit</button>

</form>

<?php

if ($xpath != '') {

	// process raw XSLT
	if ($output == 'raw') {
		$results = $xml_mss->xpath($xpath);

		echo '<p>Results: ' . count($results) . '</p>';
		echo '<textarea class="form-control" name="xpath" rows="5">';
		$text = '';
		foreach ($results as $node) {
			$text .= (string)$node . "\n";
		}

		echo  htmlspecialchars($text);
		echo '</textarea>';
	}
	// filter full MS XML
	else {
		$xpath = 'manuscript[' . $xpath . ']';
		$results = searchMSS($xml_mss, 'xpath', $xpath);

		if (isset($results)) {
			$matches = count($results);
			if ($matches == 0) {
				echo '<p class="pb-5">No matches found.</p>';
			}
			// list IDs
			elseif ($output == 'list') {
				echo '<p>Results: ' . $matches . '</p>';
				echo '<textarea class="form-control" name="xpath" rows="5">';
				foreach ($results as $index => $ms) {
					echo $ms['id'];
					if ($index < count($results) - 1) echo ', ';
				}
				echo '</textarea>';

			}
			// default output is manuscript table
			else {
				// display results
				listMSS($results);
			}
		}
	}
}

?>