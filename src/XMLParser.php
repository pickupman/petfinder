<?php
namespace Pickupman\Petfinder;

class XMLParser {

	public function __construct() {}

	/**
	* Parse XML data using SimpleXMLElement
	* @param string
	* @return string
	*/
	public function parse($xml) {
		return simplexml_load_string($xml);
	}
}