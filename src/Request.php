<?php
namespace Pickupman\Petfinder;

class Request {
	
	public function __construct() {}
	
	public function get($url) {

		// create a new cURL resource
		$ch = curl_init();

		// set URL and other appropriate options
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);


		// grab URL and pass it to the browser
		$response = curl_exec($ch);

		// close cURL resource, and free up system resources
		curl_close($ch);
		
		return $response;
	}
}