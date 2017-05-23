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
		$this->faultyLocations = [];

		$memberIds = $this->getMembers();

		foreach ($memberIds as $memberId) {
			$this->loadMemberLocation($memberId);
		}
	}

	private function loadMemberLocation(Int $memberId) {
		$mapData = $this->getData("https://maps.googleapis.com/maps/api/geocode/json" .
				"?address=" . urlencode($this->getAddress($memberId)) . "&sensor=false&key=" . $this->mapsApiKey);

		$geometry = $this->geometryFromMapData($mapData);

		if ($geometry["location_type"] == "ROOFTOP") {
			$this->locations[] = array (
				"name" => $this->getName($memberId),
				"address" => $this->getAddress($memberId),
				"lat" => $geometry["location"]["lat"],
				"lng" => $geometry["location"]["lng"]
			);
		} else {
			$this->logLocationError($memberId, $mapData['status']);		
		}
	}

	private function geometryFromMapData(Array $mapData) {
		// If Status Code is ZERO_RESULTS, OVER_QUERY_LIMIT, REQUEST_DENIED or INVALID_REQUEST
		if ($mapData["status"] == "OK") {
			return $mapData["results"][0]["geometry"];
		}
		return array("location_type" => null);
	}

    private function logLocationError(Int $memberId, String $status) {
		if ($status == 'OVER_QUERY_LIMIT') {
			$error = "Too many requests";
		} else {
			$error = "Address not found";
		}

		$this->locationErrors[] = array (
			"name" =>$this->getName($memberId),
			"address" => $this->getAddress($memberId),
			"error" => $error
		);
	}
	
	private function getMembers() {
		$members = $this->api->get('member');
		return $members->getData()['objects'];
	}

	private function getMember(Int $memberId) {
		$member = $this->api->get('member/' . $memberId);
		return $member->getData();
	}

	private function getAddress(Int $memberId) {
		$member = $this->getMember($memberId);

		return $member["properties"][$this->config['addressName']] . ", " . $member["properties"][$this->config['cityName']] . ", " . $this->config['country'];
	}

	private function getName(Int $memberId) {
		$member = $this->getMember($memberId);
		return $member["properties"][$this->config['firstName']] . " " . $member["properties"][$this->config['lastName']];
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