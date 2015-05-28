<?php
/*
Plugin Name: Webling
Plugin URI: http://www.webling.eu
Description: Webling Mitgliederliste Plugin
Version: 1.0
Author: uSystems GmbH
Author URI: http://www.usystems.ch
*/

/*
Webling (Wordpress Plugin)
Copyright (C) 2014 uSystems GmbH
Contact me at http://www.usystems.ch
*/

// Make sure we don't expose any info if called directly
if ( !function_exists( 'add_action' ) ) {
	echo '';
	exit;
}
require_once dirname( __FILE__ ) . "/common.php";

DEFINE("WEBLING_OPTIONS", "webling-options");
DEFINE("WEBLING_OPTIONS_GROUP", "webling-options-group");
DEFINE("WEBLING_MENU_SLUG", "webling-menu");

if ( is_admin() )
	require_once dirname(__FILE__) . '/admin.php';

//tell wordpress to register the memberlist shortcode
add_shortcode("webling_memberlist", "webling_memberlist_handler");
add_action('wp_head', 'webling_css');

function webling_memberlist_handler() {
	$output = webling_liste();

	return '<div id="webling_memberlist">'.$output.'<div>';
}

function webling_get_data($url) {
	$curl = curl_init();
	curl_setopt($curl, CURLOPT_URL, $url);
	curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
	$data = json_decode(curl_exec($curl), true);
	curl_close($curl);
	return $data;
}

function webling_liste() {

	$options = webling_get_config();

    try {

		echo '<table id="webling-memberlist">';

				echo '<tr>';
				foreach ($options['fieldarray'] as $field) {
					echo '<th>' . $field . '</th>';
				}

				echo '</tr>';

				$memberIds = webling_get_data($options["host"] . "/api/1/member?apikey=" . $options["apikey"])["objects"];

				if (is_array($memberIds) == false) {
					throw new Exception("Cannot connect to API");
				}

				foreach ($memberIds as $memberId) {
					$member = webling_get_data($options["host"] . "/api/1/member/" . $memberId . "?apikey=" . $options["apikey"]);
					echo '<tr>';
					foreach ($options['fieldarray'] as $field) {
						echo "<td>" . $member["properties"][$field] . "</td>";
					}
					echo '</tr>';
				}
		echo '</table>';

    } catch (Exception $e) {
    	echo '<p>Memberlist cannot be loaded</p>';
    }
}

function webling_get_config() {

	global $wpdb;

	$options = get_option(WEBLING_OPTIONS);

	$options['fieldarray'] = array();
	foreach (explode(',', $options['fields']) as $field) {
		if (strlen(trim($field)) > 0 ) {
			$options['fieldarray'][] = trim($field);
		}
	}

	return $options;
}

function webling_css(){
	$options = webling_get_config();
	
	$css = file_get_contents(dirname( __FILE__ ) . '/css/styles.css');
	echo '<style type="text/css"><!--' . $css;
	if (isset($options['css'])) {
		echo "\n" . $options['css'] . "\n";
	}
	echo '--></style>';
}
