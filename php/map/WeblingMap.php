<?php

class WeblingMap {
	private $api;
	private $config;
	private $mapsApiKey;

	private $locations = array();
	private $locationErrors = array();

	public function __construct(String $domain, String $weblingApiKey, String $mapsApiKey, Array $config) {
		$this->api = new Webling\API\Client($domain, $weblingApiKey);
		$this->config = $config;
		$this->mapsApiKey = $mapsApiKey;
	}

	public function getLocations() {
		return $this->locations;
	}

	public function getLocationErrors() {
		return $this->locationErrors;
	}

	public function loadLocations() {
		//reset data
		$this->locations = [];
		$this->locationErrors = [];

		$members = $this->getMembers();

		foreach ($members as $member) {
			$this->loadMemberLocation($member);
		}
	}

	private function loadMemberLocation($member) {
		$mapData = $this->getData("https://maps.googleapis.com/maps/api/geocode/json" .
				"?address=" . urlencode($this->getAddress($member)) . "&key=" . $this->mapsApiKey);

		$geometry = $this->geometryFromMapData($mapData);

		if ($geometry["location_type"] == "ROOFTOP") {
			$this->locations[] = array (
				"name" => $this->getName($member),
				"address" => $this->getAddress($member),
				"lat" => $geometry["location"]["lat"],
				"lng" => $geometry["location"]["lng"]
			);
		} else {
			$this->logLocationError($member, $mapData['status']);
		}
	}

	private function geometryFromMapData(Array $mapData) {
		// If Status Code is ZERO_RESULTS, OVER_QUERY_LIMIT, REQUEST_DENIED or INVALID_REQUEST
		if ($mapData["status"] == "OK") {
			return $mapData["results"][0]["geometry"];
		}
		return array("location_type" => null);
	}

	private function logLocationError($member, String $status) {
		if ($status == 'OVER_QUERY_LIMIT') {
			$error = "Too many requests";
		} else {
			$error = "Address not found";
		}

		$this->locationErrors[] = array (
			"name" =>$this->getName($member),
			"address" => $this->getAddress($member),
			"error" => $error
		);
	}
	
	private function getMembers() {
		$members = $this->api->get('member?format=full');
		return $members->getData();
	}

	private function getAddress($member) {
		$address  = $member["properties"][$this->config['weblingProperty']['addressName']];
		$address .= ', ';
		$address .= $member["properties"][$this->config['weblingProperty']['cityName']];
		$address .= ', ';
		$address .= $this->config['defaultCountry'];
		return $address;
	}

	private function getName($member) {
		$name  = $member["properties"][$this->config['weblingProperty']['firstName']];
		$name .= ' ';
		$name .= $member["properties"][$this->config['weblingProperty']['lastName']];
		return $name;
	}

	private function getData(String $url) {
		$curl = curl_init();
		curl_setopt($curl, CURLOPT_URL, $url);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
		$data = json_decode(curl_exec($curl), true);
		curl_close($curl);
		return $data;
	}
}
