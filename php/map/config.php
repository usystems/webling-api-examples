<?php
	/**
	 * Add a key for your webling REST API.
	 * https://demo.webling.ch/api/
	 */
	$weblingApiKey = "yourWeblingApiKey";

	/**
	 * The domain of your webling account
	 */
	$domain = "https://<yourdomain>.webling.ch";

	$config = array(
		/**
		 * These are the filed names for the Properties used to show the persons on the web
		 */
		'weblingProperty' => array(
			'firstName' => 'Vorname',
			'lastName' => 'Name',
			'addressName' => 'Strasse',
			'cityName' => 'Ort'
		),
		/**
		 * Country the address should be searched in
		 */
		'defaultCountry' => 'Schweiz'
	);

	/**
	 * Add a key for the Google Maps API
	 */
	$mapsApiKey = "<yourGoogleMapsApiKey>";
