<?php

	/**
	 * Add a key for your webling REST API.
	 * https://demo.webling.ch/api/
	 */
	$apiKey = "<yourapikey>";

	/**
	 * The domain of your webling account
	 */
	$domain = "https://<yourdomain>.webling.ch";

	/**
	 * Fields you want to display
	 */
	$fields = array("Strasse", "PLZ", "Ort");

	function getData($url) {
		$curl = curl_init();
		curl_setopt($curl, CURLOPT_URL, $url);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
		$data = json_decode(curl_exec($curl), true);
		curl_close($curl);
		return $data;
	}

	// add the "format=full" parameter to load all member details
	$memberData = getData($domain . "/api/1/member?format=full&apikey=" . $apiKey);
	if (is_array($memberData) == false) {
		die("Error connectiong to API");
	}
?>
<!DOCTYPE html>
<html>
<head>
	<title>Memberlist</title>
	<meta http-equiv="content-type" content="text/html; charset=utf-8"/>
	<meta name="viewport" content="initial-scale=1.0, user-scalable=no" />
	<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.4/css/bootstrap.min.css">
	<script src="https://code.jquery.com/jquery-2.1.4.min.js"></script>
	<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.4/js/bootstrap.min.js"></script>
</head>

<body>
	<table class="table">
		<tr>
			<th>
				<?php echo implode("</th><th>", $fields); ?>
			</th>
		</tr>
		<?php
			foreach ($memberData as $member) {
				echo "<tr>";
				foreach ($fields as $field) {
					echo "<td>" . $member["properties"][$field] . "</td>";
				}
				echo "</tr>\n";
			}
		?>
	</table>
</body>
</html>