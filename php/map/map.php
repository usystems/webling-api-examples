<?php

	/**
	 * Add a key for your webling REST API.
	 * https://demo.webling.ch/api/
	 */
	$apikey = "<yourapikey>";

	/**
	 * The domain of your webling account
	 */
	$domain = "https://<yourdomain>.webling.ch";

	/**
	 * These are the filed names for the Properties used to show the persons on the web
	 */
	$firstName = "Vorname";
	$lastName = "Name";
	$addressName = "Strasse";
	$cityName = "Ort";

	/**
	 * Country the address should be searched in
	 */
	$country = "Schweiz";

	function getData($url) {
		$curl = curl_init();
		curl_setopt($curl, CURLOPT_URL, $url);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
		$data = json_decode(curl_exec($curl), true);
		curl_close($curl);
		return $data;
	}

	$memberIds = getData($domain . "/api/1/member?apikey=" . $apiKey)["objects"];

	$locations = array();
	$log = "";

	if (is_array($memberIds) == false) {
		$log = "Error connectiong to API";

	} else {
		foreach ($memberIds as $memberId) {
			$member = getData($domain . "/api/1/member/" . $memberId . "?apikey=" . $apiKey);

			$address = $member["properties"][$addressName] . ", " . $member["properties"][$cityName] . ", " . $country;

			$mapData = getData("http://maps.googleapis.com/maps/api/geocode/json" .
				"?address=" . urlencode($address) . "&sensor=false");

			// If Status Code is ZERO_RESULTS, OVER_QUERY_LIMIT, REQUEST_DENIED or INVALID_REQUEST
			if ($mapData["status"] != "OK") {
				$geometry = array("location_type" => null);
			} else {
				$geometry = $mapData["results"][0]["geometry"];
				if (
					!is_numeric($geometry["location"]["lat"]) or !is_numeric($geometry["location"]["lng"])
					or $geometry["location"]["lat"] == null or $geometry["location"]["lat"] == null
				) {
					$geometry["location_type"] = null;
				}
			}

			if ($geometry["location_type"] != "ROOFTOP") {
				$log .= "<b>" . $member["properties"][$firstName] . " " . $member["properties"][$lastName] . "</b>";
				$log .= "<br/>" . $address;
				if ($geometry["location_type"] === null) {
					$log .= "<br/><i>Too many requests</i><br/><br/>";
				} else {
					$log .= "<br/><i>Address not found</i><br/><br/>";
				}
			} else {
				$locations[] = array (
					"name" => $member["properties"][$firstName] . " " . $member["properties"][$lastName],
					"address" => $address,
					"lat" => $geometry["location"]["lat"],
					"lng" => $geometry["location"]["lng"]
				);
			}
		}
	}
?>
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
	<title>Karte der Mitglieder</title>
	<meta http-equiv="content-type" content="text/html; charset=utf-8"/>
	<meta name="viewport" content="initial-scale=1.0, user-scalable=no" />

	<script src="https://code.jquery.com/jquery-2.1.4.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.4/js/bootstrap.min.js"></script>
	<script src="http://maps.googleapis.com/maps/api/js?sensor=false&libraries=visualization" type="text/javascript"></script>

	<link rel="stylesheet" type="text/css" href="//netdna.bootstrapcdn.com/bootstrap/3.1.1/css/bootstrap.min.css">

</head>

<body>
	<script type="text/javascript">
		var jsonData = <?php echo json_encode($locations); ?>;

		$(document).ready(function() {
			var markers = [];
			var bounds = new google.maps.LatLngBounds();		
			var map = new google.maps.Map(document.getElementById("map"), {
				mapTypeId: 'roadmap',
				mapTypeControlOptions: {style: google.maps.MapTypeControlStyle.DROPDOWN_MENU}
			});
			var infoWindow = new google.maps.InfoWindow();
			jsonData.forEach(function(data) {
				var latlng = new google.maps.LatLng(data['lat'], data['lng']);
				bounds.extend(latlng);

				var html = "<b>" + data['name'] + "</b> <br/>" + data['address'];
				var marker = new google.maps.Marker({
					map: map,
					position: latlng
				});
				google.maps.event.addListener(marker, 'click', function() {
					infoWindow.setContent(html);
					infoWindow.open(map, marker);
				});
				markers.push(marker);
			});
			map.fitBounds(bounds);
		});
	</script>

	<div id="map" style="width: 80%; height: 100%"></div>
	<div id="log" style="width: 19%; height: 100%; position: absolute; right: 0; top: 10px;">
		<h2>Unresolved Addresses</h2>
		<?php echo $log; ?>
	</div>

	</body>
</html>