<?php

/**
 * Example usage:
 * get_image.php?memberId=507&filename=Mitgliederbild.png
 */

include_once('config.php');

// check that we have a member id
if (!isset($_REQUEST['memberId']) || !intval($_REQUEST['memberId']) > 0) {
	throw new Exception('Error: Parameter memberId is missing or invalid.');
}

// check that we have a filename
if (!isset($_REQUEST['filename']) || strlen($_REQUEST['filename']) == 0) {
	throw new Exception('Error: Parameter filename is missing or invalid.');
}

/**
 * Load the image from the API.
 * @param $memberid int The id of the member
 * @param $filename string The name of the file, usually "<fieldname>.<extension>", e.g. "Mitgliederbild.png"
 * @return mixed raw image
 * @throws Exception
 */
function getImage($memberid, $filename) {
	$url = WEBLING_DOMAIN . '/api/1/member/'.intval($memberid).'/image/' . basename($filename) . '?apikey=' . WEBLING_APIKEY;
	$curl = curl_init();
	curl_setopt($curl, CURLOPT_URL, $url);
	curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
	$data = curl_exec($curl);
	$http_code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
	if($http_code < 200 || $http_code >= 300) {
		throw new Exception('cURL-Error: HTTP Code ' . $http_code);
	}
	curl_close($curl);
	return $data;
}

/**
 * @param $filename string Filename including the file extension, e.g. "Mitgliederbild.png"
 * @return string The content type according to the file extension
 * @throws Exception
 */
function getContentType($filename) {
	$filename = basename($filename);
	$file_extension = strtolower(substr(strrchr($filename,"."),1));

	switch( $file_extension ) {
		case "gif": $ctype = "image/gif"; break;
		case "png": $ctype = "image/png"; break;
		case "jpeg":
		case "jpg": $ctype = "image/jpeg"; break;
		default: throw new Exception('Unsupported file extension "'. $file_extension . '" for file "' . $filename . '"');
	}
	return $ctype;
}

// prepare parameters
$memberid = intval($_REQUEST['memberId']);
$filename = basename($_REQUEST['filename']);

// get the content type from the filename
$contentType = getContentType($filename);

// load the actual image from the api
$imageData = getImage($memberid, $filename);

// send correct image header to tell the browser to show the image instead of downloading it
header('Content-type: ' . $contentType);

// output the image
echo $imageData;
