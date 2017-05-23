<?php

require_once("config.php");
require_once("vendor/autoload.php");

require_once("WeblingMap.php");

$map = new WeblingMap($domain, $weblingApiKey, $mapsApiKey, $propertiesInWebling);
$map->loadLocations();
	
?>
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
	<title>Display members on map</title>
	<meta http-equiv="content-type" content="text/html; charset=utf-8"/>
	<meta name="viewport" content="initial-scale=1.0, user-scalable=no" />
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.4/css/bootstrap.min.css">
	<script src="https://code.jquery.com/jquery-2.1.4.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.4/js/bootstrap.min.js"></script>
	<script src="http://maps.googleapis.com/maps/api/js?sensor=false&libraries=visualization&key=<?php echo $mapsApiKey ?>" type="text/javascript"></script>

</head>

<body>
	<script type="text/javascript">
		var jsonData = <?php echo json_encode($map->getLocations()); ?>;

		$(document).ready(function() {
			var markers = [];
			var bounds = new google.maps.LatLngBounds();		
			var map = new google.maps.Map(document.getElementById("map"), {
				mapTypeId: "roadmap",
				mapTypeControlOptions: {style: google.maps.MapTypeControlStyle.DROPDOWN_MENU}
			});
			var infoWindow = new google.maps.InfoWindow();
			jsonData.forEach(function(data) {
				var latlng = new google.maps.LatLng(data["lat"], data["lng"]);
				bounds.extend(latlng);

				var html = "<b>" + data["name"] + "</b> <br/>" + data["address"];
				var marker = new google.maps.Marker({
					map: map,
					position: latlng
				});
				google.maps.event.addListener(marker, "click", function() {
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
		<?php
			foreach ($map->getLocationErrors() as $locationError) {
				echo "<b>" . $locationError["name"] . "</b>";
				echo "<br/>" . $locationError["address"];
				echo "<br/><i>" . $locationError["error"] . "</i><br/><br/>";
			}
		?>
	</div>

	</body>
</html>
