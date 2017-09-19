<?php

include_once('config.php');

/**
 * Member name field name you want to display
 */
$nameField = "Vorname";

/**
 * Member image field name with the image you want to display
 */
$imageField = "Mitgliederbild";

function getData($url) {
	$curl = curl_init();
	curl_setopt($curl, CURLOPT_URL, $url);
	curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
	$data = json_decode(curl_exec($curl), true);
	curl_close($curl);
	return $data;
}

$memberIds = getData(WEBLING_DOMAIN . "/api/1/member?apikey=" . WEBLING_APIKEY)["objects"];
if (is_array($memberIds) == false) {
	die("Error connectiong to API");
}
?>
<!DOCTYPE html>
<html>
<head>
	<title>Memberlist with images</title>
	<meta http-equiv="content-type" content="text/html; charset=utf-8"/>
	<meta name="viewport" content="initial-scale=1.0, user-scalable=no" />
	<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.4/css/bootstrap.min.css">
	<script src="https://code.jquery.com/jquery-2.1.4.min.js"></script>
	<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.4/js/bootstrap.min.js"></script>
</head>

<body>
	<div class="row">
		<?php
			foreach ($memberIds as $memberId) {
				$member = getData(WEBLING_DOMAIN . "/api/1/member/" . $memberId . "?apikey=" . WEBLING_APIKEY);
				echo '<div class="col-sm-4 col-md-3">';
				echo '<div class="thumbnail">';
				if (isset($member["properties"][$imageField]['name'])) {
					echo '<img src="get_image.php?memberId='.$memberId.'&filename='.$member["properties"][$imageField]['name'].'" />';
				}
				echo '<div class="caption">';
				echo '<h3>'.$member["properties"][$nameField].'</h3>';
				echo '</div>';
				echo "</div>";
				echo "</div>\n";
			}
		?>
	</div>
</body>
</html>
