<?php

$xpath = $_GET['xpath'];
$output = cleanInput('output') ?? '';

?>
<h2>Advanced query</h2>

<form>
<input type="hidden" name="page" value="xpath"/>

<textarea class="form-control" name="xpath" rows="5"><?php print $xpath; ?></textarea>

<div>
Results as: &nbsp; 
table <input class="form-check-input" type="radio" name="output" value="" <?php if ($output == '') print ' checked'; ?>> &nbsp; 
list of IDs <input class="form-check-input" type="radio" name="output" value="list" <?php if ($output == 'list') print ' checked'; ?>> &nbsp; 
</div>

<button type="submit" class="btn btn-primary mb-3">Submit</button>

</form>

<?php

if ($xpath != '') {
	$xpath = 'manuscript[' . $xpath . ']';
	$results = searchMSS($xml_mss, 'xpath', $xpath);

	if (isset($results)) {
		$matches = count($results);
		if ($matches == 0) {
			print '<p class="pb-5">No matches found.</p>';
		}
		// list output
		elseif ($output == 'list') {
			print '<p>Results: ' . $matches . '</p>';
			print '<textarea class="form-control" name="xpath" rows="5">';
			foreach ($results as $ms) {
				print $ms['id'] . ",";
			}
			print '</textarea>';

		}
		// default output is manuscript table
		else {
			// display results
			listMSS($results);
		}
	}
}

?>