<?php
namespace Pickupman\Petfinder;

class Cookie {

	public function __construct() {}

	/**
	* Retrieves the value of a cookie
	* @param string key to fetch
	* @return mixed
	*/
	public function get($key) {
		return ( isset($_COOKIE[$key]) ) ? $_COOKIE[$key] : false;
	}

	/**
	* Set a value in a COOKIE
	* @param string key to set
	* @param string data to set
	* @param int    expiration value
	*/
	public function set($key, $data, $expires) {
		return (bool) setcookie($key, $data, $expires);
	}
}