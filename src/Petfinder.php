<?php
namespace Pickupman\Petfinder;

use Pickupman\Petfinder\Cookie;
use Pickupman\Petfinder\XMLParser;

/**
 * PHP class for communicating with Petfinder.com API
 * requires signing up for a free API key from Petfinder website
 *
 * YOU ARE FREE TO USE THIS ANY WAY YOU WISH.
 *
 * @author Joe McFrederick
 * @date   2010-06-29
 * @license MIT
 */
class Petfinder
{
	var $api_key;
	var $api_pass;
	var $api_url = 'http://api.petfinder.com/';
	var $token;
	var $format    = FALSE;
	var $animal    = FALSE;
	var $id        = FALSE;
	var $breed     = FALSE;
	var $size      = FALSE;
	var $sex       = FALSE;
	var $location  = FALSE;
	var $shelterid = FALSE;
	var $output    = FALSE;
	var $offset    = FALSE;
	var $count     = FALSE;
	var $name      = FALSE;
	var $status    = FALSE;
    var $cache_enable = TRUE;
    var $cache_expire = 360; //3 mins
    var $cache_contents;
    var $cache_path;
    var $cache_url;

    var $XMLParser;
    var $cookie;


	public function __construct($options = array(), XMLParser $XMLParser = null, Cookie $cookie = null)
	{
        $this->cache_path = dirname(__FILE__) . '/cache_files/';

        $this->XMLParser = is_null($XMLParser) ? new XMLParser() : $XMLParser;
		$this->cookie    = is_null($cookie) ? new Cookie() : $cookie;

        if ( ! empty($options) ) $this->set($options);

		if ( ! empty($this->api_key) AND ! empty($this->api_key)) $this->getToken($this->cookie);

	}

	/**
	* Set a property of class
	* @param array $key => $value to set
	* @return void
	*/
	function set( $setter = array() ){
		if(is_array($setter))
		{
			foreach($setter as $key => $value){
				$this->$key = $value;
			}
			return TRUE;
		}
		return FALSE;
	}

	/**
	*	Get Security Token
	*/
	function getToken(Cookie $cookie = null)
	{

		$this->cookie = is_null($cookie) ? new Cookie() : $cookie;

		$sig = md5($this->api_pass . 'key=' . $this->api_key);
		$url = 'auth.getToken?key=' . $this->api_key . '&sig=' . $sig;

		// Check for existing cookie
		if ( $this->cookie->get('petToken') ) {
			return true;
		}

		$xmlResponse = $this->_curl($url);

		//Create SimpleXML
		$xml = $this->XMLParser->parse($xmlResponse);

		// Need a well formed xml response before we continue
		if ( ! $xml) return false;

		//Set cookie with successful token
		if($xml->header->status->code = '100')
		{
			$this->cookie->set('petToken',$xml->auth->token,strtotime($xml->auth->expiresString));
			return true;
		}

		return false;
	}

	/**
	*	Pass options array to build url string
	*	@params array $config associative array of key=>values to pass to xml request
	*	@return void
	*/
	function initialize( $config = array() )
	{
		//Set default values
		$defaults = array(
			  'format'    => 'xml',
			  'animal'    => 'dog',
			  'id'        => FALSE,
			  'breed'     => FALSE,
			  'size'      => FALSE,
			  'sex'       => FALSE,
			  'location'  => FALSE,
			  'shelterid' => FALSE,
			  'output'    => FALSE,
			  'offset'    => 0,
			  'count'     => 25,
			  'name'      => FALSE,
			  'status'    => FALSE
		);

		//Overwrite defaults with values passed to function
		foreach( $defaults as $key => $value )
		{
			if (isset($config[$key]))
			{
				$this->$key = $config[$key];
			}
			else
			{
				$this->$key = $value;
			}
		}
	}

	/**
	*	Retrieve breeds for a animal
	*	@param string (barnyard, bird, cat, dog, horse, pig, reptile, smallfurry)
	*	@returns array
	*/
	function breedList($animal = FALSE)
	{
		// Make sure we have a animal as a property or passed as a parameter
		if ( ! $this->animal AND ! $animal) {
			return false;
		}

		// Not set as a property but passed as a parameter
		if ( ! $this->animal AND $animal) {
			$this->animal = $animal;
		}

		$urlString = $this->_urlString();
		$url = 'breed.list?'.$urlString;

		$xmlResponse = $this->_curl($url);

		$xml = new \SimpleXMLElement($xmlResponse);

		$data['code'] = (string)$xml->header->status->code;

		$i = 0;
		if ( isset($xml->breeds) ) {
			foreach ($xml->breeds->breed as $breed)
			{
				$data[$i] = (string)$breed;
				$i++;
			}
		}

		return $data;
	}

	/**
	*	Search for pet records
	*	@param none
	*	@returns array $data associative array of $key=>$values
	*/
	function petFind()
	{
		$urlString = $this->_urlString(array('animal' => $animal, 'location' => $zip));
		$url = 'pet.find?'.$urlString;

		$xmlResponse = $this->_curl($url);

		$xml = new \SimpleXMLElement($xmlResponse);

		$data['code'] = (string)$xml->header->status->code;
		$i=0;
		if ( isset($xml->pets) ) {
			foreach ($xml->pets->pet as $pet) {

			$data[$i] = array(
				'id'          => (string)$pet->id,
				'animal'      => (string)$pet->animal,
				'breeds'      => (string)$pet->breeds->breed,
				'mix'         => (string)$pet->mix,
				'age'         => (string)$pet->age,
				'name'        => (string)$pet->name,
				'shelterId'   => (string)$pet->shelterId,
				'size'        => (string)$pet->size,
				'sex'         => (string)$pet->sex,
				'description' => (string)$pet->description,
				'lastUpdate'  => (string)$pet->lastUpdate,
				'status'      => (string)$pet->status
			);

				//Iterate through images
				foreach($pet->media->photos->photo as $photo)
				{
					$j = (string)$photo['id'];
					switch($photo['size'])
					{
						case('x'):
							$data[$i]['photo'][$j]['x'] = (string)$photo;
						break;

						case 't':
							$data[$i]['photo'][$j]['t'] = (string)$photo;
						break;

						case 'pn':
							$data[$i]['photo'][$j]['pn'] = (string)$photo;
						break;

						case 'pnt':
							$data[$i]['photo'][$j]['pnt'] = (string)$photo;
						break;

						case 'fpm':
							$data[$i]['photo'][$j]['fpm'] = (string)$photo;
						break;
					}
				}

				$i++;
			}
		}
		$data['xml'] = $xmlResponse;
		return $data;
	}

	/**
	*	Retrieve information for a pet id
	*	@param $id (int)
	*	@returns (array)
	*/
	function petGet()
	{
		//Create URL string
		//$urlString = $this->_urlString(array('id'=>$id));
		$urlString = $this->_urlString();

		$url = 'pet.get?'.$urlString;

		$xmlResponse = $this->_curl($url);

		//Create SimpleXML
		$xml = new \SimpleXMLElement($xmlResponse);


		//Assign element to array
		$data = array(
			'code'        => (string)$xml->header->status->code,
			'id'          => (string)$xml->pet->id,
			'animal'      => (string)$xml->pet->animal,
			'breeds'      => (string)$xml->pet->breeds->breed,
			'mix'         => (string)$xml->pet->mix,
			'age'         => (string)$xml->pet->age,
			'name'        => (string)$xml->pet->name,
			'shelterId'   => (string)$xml->pet->shelterId,
			'size'        => (string)$xml->pet->size,
			'sex'         => (string)$xml->pet->sex,
			'description' => (string)$xml->pet->description,
			'lastUpdate'  => (string)$xml->pet->lastUpdate,
			'status'      => (string)$xml->pet->status,
		);

		$i=0;
		if ( isset($xml->pet) ) {
			foreach ( $xml->pet->media->photos->photo as $photo) {

				$i = (string)$photo['id'];
				switch((string)$photo['size'])
				{
					case 'x':
						$data['photo'][$i]["x"] = (string)$photo;
					break;

					case 't':
						$data['photo'][$i]['t'] = (string)$photo;
					break;

					case 'pn':
						$data['photo'][$i]['pn'] = (string)$photo;
					break;

					case 'pnt':
						$data['photo'][$i]['pnt'] = (string)$photo;
					break;

					case 'fpm':
						$data['photo'][$i]['fpm'] = (string)$photo;
					break;
				}
				$i++;
			}
		}

		$data['xml'] = $xmlResponse;
		return $data;
	}


	/**
	*	Retrieve random pet record for information
	*	@param none
	*	@returns array
	*/
	function petGetRandom()
	{
		$urlString = $this->_urlString();

		$url = 'pet.getRandom?' . $urlString;

		$xmlResponse = $this->_curl($url);

		$xml = new \SimpleXMLElement($xmlResponse);

		$data['code'] = $xml->header->status->code;

		//Assign element to array
		$data = array(
			'code'        => (string)$xml->header->status->code,
			'id'          => (string)$xml->pet->id,
			'animal'      => (string)$xml->pet->animal,
			'breeds'      => (string)$xml->pet->breeds->breed,
			'mix'         => (string)$xml->pet->mix,
			'age'         => (string)$xml->pet->age,
			'name'        => (string)$xml->pet->name,
			'shelterId'   => (string)$xml->pet->shelterId,
			'size'        => (string)$xml->pet->size,
			'sex'         => (string)$xml->pet->sex,
			'description' => (string)$xml->pet->description,
			'lastUpdate'  => (string)$xml->pet->lastUpdate,
			'status'      => (string)$xml->pet->status,
		);

		if ( isset($xml->pet) ) {
			foreach( $xml->pet->media->photos->photo as $photo)
			{
				$image = '';
				$image = $photo;

				switch((string)$photo['size'])
				{
					case 'x':
						$data['photo']["x"] = (string)$photo;
					break;

					case 't':
						$data['photo']['t'] = (string)$photo;
					break;

					case 'pn':
						$data['photo']['pn'] = (string)$photo;
					break;

					case 'pnt':
						$data['photo']['pnt'] = (string)$photo;
					break;

					case 'fpm':
						$data['photo']['fpm'] = (string)$photo;
					break;
				}

			}
		}

		$data['xml'] = $xmlResponse;

		return $data;
	}

	/**
	*	Retrieve group of shelter records
	*	$param none
	*	@returns array
	*/
	function shelterFind()
	{
		$urlString = $this->_urlString();
		$url = 'shelter.find?' . $urlString;

		$xmlResponse = $this->_curl($url);

		$xml = new \SimpleXMLElement($xmlResponse);

		$data['code'] = (string)$xml->header->status->code;

		$i = 0;
		if ( isset($xml->shelters) ) {
			foreach($xml->shelters->shelter as $shelter)
			{

				$data = array(
					'id'        => (string) $shelter->id,
					'name'      => (string) $shelter->name,
					'address1'  => (string) $shelter->address1,
					'address2'  => (string) $shelter->address2,
					'city'      => (string) $shelter->city,
					'state'     => (string) $shelter->state,
					'zip'       => (string) $shelter->zip,
					'country'   => (string) $shelter->country,
					'longitude' => (string) $shelter->longitude,
					'latitude'  => (string) $shelter->latitude,
					'phone'     => (string) $shelter->phone,
					'fax'       => (string) $shelter->fax,
					'email'     => (string) $shelter->email
				);

				$i++;
			}
		}

		return $data;

	}

	/**
	*	Retrieve Shelter record given a shelterID
	*	@param none
	*	@returns array
	*/
	function shelterGet()
	{
		$urlString = $this->_urlString();
		$url = 'shelter.get?'.$urlString;

		$xmlResponse = $this->_curl($url);

		$xml = new \SimpleXMLElement($xmlResponse);

		return array(
			'code'      => (string) $xml->header->status->code,
			'id'        => (string) $xml->shelter->id,
			'name'      => (string) $xml->shelter->name,
			'address1'  => (string) $xml->shelter->address1,
			'address2'  => (string) $xml->shelter->address2,
			'city'      => (string) $xml->shelter->city,
			'state'     => (string) $xml->shelter->state,
			'zip'       => (string) $xml->shelter->zip,
			'country'   => (string) $xml->shelter->country,
			'longitude' => (string) $xml->shelter->longitude,
			'latitude'  => (string) $xml->shelter->latitude,
			'phone'     => (string) $xml->shelter->phone,
			'fax'       => (string) $xml->shelter->fax,
			'email'     => (string) $xml->shelter->email
		);
	}

	/**
	*	Retrieve pet records for a Shelter given a shelterID
	*	@param none
	*	@returns array
	*/
	function shelterGetPets()
	{
		//Set up default signature & url
		$url = $this->_urlString();

		$url = 'shelter.getPets?'.$url;

		$xmlResponse = $this->_curl($url);

		$xml = new \SimpleXMLElement($xmlResponse);

		$data['code'] = (string)$xml->header->status->code;

		$i=0;
		if ( isset($xml->pets) ) {
			foreach($xml->pets->pet as $pet)
			{

				$data[$i] = array(
					'id'          => (string)$pet->id,
					'animal'      => (string)$pet->animal,
					'breeds'      => (string)$pet->breeds->breed,
					'mix'         => (string)$pet->mix,
					'age'         => (string)$pet->age,
					'name'        => (string)$pet->name,
					'shelterId'   => (string)$pet->shelterId,
					'size'        => (string)$pet->size,
					'sex'         => (string)$pet->sex,
					'description' => (string)$pet->description,
					'lastUpdate'  => (string)$pet->lastUpdate,
					'status'      => (string)$pet->status
				);

				//Iterate through images
				foreach($pet->media->photos->photo as $photo)
				{
					switch($photo['size'])
					{
						case('x'):
							$data[$i]['photo']['x'] = (string)$photo;
						break;

						case 't':
							$data[$i]['photo']['t'] = (string)$photo;
						break;

						case 'pn':
							$data[$i]['photo']['pn'] = (string)$photo;
						break;

						case 'pnt':
							$data[$i]['photo']['pnt'] = (string)$photo;
						break;

						case 'fpm':
							$data[$i]['photo']['fpm'] = (string)$photo;
						break;
					}

				}

				$i++;
			}
		}
		$data['xml'] = $xmlResponse;
		return $data;

	}


	/**
	*	Get a list of shelters for a particular animal breed
	*	@param none
	*	@returns array $data
	*/
	function shelterListByBreed()
	{
		$urlString = $this->_urlString();

		$url = 'shelter.listByBreed?'.$urlString;

		$xmlResponse = $this->_curl($url);

		$xml = new \SimpleXMLElement($xmlResponse);

		$data['code'] = (string)$xml->header->status->code;

		$data['xml'] = $xmlResponse;
		return $data;
	}


	/**
	*	Set API Key provided by petfinder.com
	*	@param string $key
	*/
	function setKey( $key )
	{
		$this->api_key = $key;
	}

	/**
	*	Set API Password Secret provided by petfinder.com
	*	@param string $pass
	*/
	function setPass( $pass )
	{
		$this->api_pass = $pass;
	}


	/**
	*	Create a signature for a request
	*	@param string $url string to be signed
	*	@returns string a signature md5 has for authentication
    *   @access private
	*/
	function _signature($data)
	{
		$str = $this->api_pass . $data;

		return md5($str);
	}

	/**
	*	Create a url string for a request
	*	@param none
	*	@returns string a url string
	*/
	function _urlString()
	{

		if ( ! $this->cookie->get('petToken') ) {
			throw new \Exception('Invalid token');
		}

		$str = 'key=' . $this->api_key;

		//Add key=>values pass to class to url string
		if( $this->format )
			$str .= '&format=' . $this->format;

		if( $this->animal )
			$str .= '&animal=' . $this->animal;

		if( $this->id )
			$str .= '&id=' . $this->id;

		if( $this->breed )
			$str .= '&breed=' . $this->breed;

		if( $this->size )
			$str .= '&size=' . $this->size;

		if( $this->sex )
			$str .= '&sex=' . $this->sex;

		if( $this->location )
			$str .= '&location=' . $this->location;

		if( $this->shelterid )
			$str .= '&shelterid=' . $this->shelterid;

		if( $this->output )
			$str .= '&output=' . $this->output;

		if( $this->offset)
			$str .= '&offset=' . $this->offset;

		if( $this->count )
			$str .= '&count=' . $this->count;

		if( $this->name )
			$str .= '&name=' . $this->name;

		if( $this->status )
			$str .= '&status=' . $this->status;

        $this->cache_url = $str;

		$str .= '&token=' . $_COOKIE['petToken'];

		//Generate signature
		$sig = $this->_signature($str);

		//Append signature to url
		$str .= '&sig=' . $sig;

		return $str;
	}

	/**
	*	Retrieve a URL
	*	@param string $url
	*	@returns string xml
	*	@access private
	*/
	function _curl($url)
	{
		$xml = '';

        //Check for cache
        if($this->cache_enable){
            $cache_response = $this->_cache($this->cache_url);

            if($cache_response != FALSE)
               return $cache_response;
        }


		// create a new cURL resource
		$ch = curl_init();

		// set URL and other appropriate options
		curl_setopt($ch, CURLOPT_URL, $this->api_url.$url);
		curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);


		// grab URL and pass it to the browser
		$xml = curl_exec($ch);

		// close cURL resource, and free up system resources
		curl_close($ch);

        //Make sure this data to write
        if( strlen($xml) > 0)
            $this->_cache_write($this->cache_url, $xml);

		return $xml;
	}

    /**
     * Check for cache file
     * @param string url to lookup in cache
     * @return mixed
     * @access private
     */
    function _cache($url){

        $cache_file = md5($url) . '.cache';

        if(is_dir($this->cache_path) && file_exists($this->cache_path . $cache_file)){

            //Check for old cache
            if(time() - filemtime($this->cache_path . $cache_file) > $this->cache_expire){
                @unlink($this->cache_path . $cache_file); //Delete old fileS
                return FALSE;
            }


            //Looks legit, get contents of cache
            return $this->_cache_read($cache_file);
        }

        return FALSE;
    }

    /**
     * Read cache file
     * @param string cache filename
     * @return sting
     * @access private
     */
    function _cache_read($cache_file){
	    //Let open a file
	    $fp = file_get_contents($this->cache_path . $cache_file);

	    $this->cache_contents = unserialize($fp);

	    return $this->cache_contents['__cache_contents'];
    }

    /**
     * Write cache file
     * @param string url identifiter
     * @param string xml response to save
     * @return bool
     */
    function _cache_write($url, $xml){

	    $cache_file = md5($url) . '.cache';

	    //Check if folder is writable
	    if( !is_dir($this->cache_path) || !is_writable($this->cache_path) )
	        return FALSE;

	    $fp = fopen($this->cache_path . $cache_file, 'wb');

	   	// Put the contents in an array so additional meta variables
		// can be easily removed from the output
	    $this->cache_contents = array('__cache_url' => md5($url));
	    $this->cache_contents['__cache_created'] = time();
	    $this->cache_contents['__cache_expires'] = time() + $this->cache_expire;
	    $this->cache_contents['__cache_contents'] = $xml;

	    if( !fwrite($fp, serialize($this->cache_contents)) )
	        return FALSE;

	    fclose($fp);
	    @chmod($this->cache_path . $cache_file, 0755);


	    return TRUE;
    }

}