<?php
namespace Pickupman\Petfinder;

use Pickupman\Petfinder\Petfinder;
use Pickupman\Petfinder\Cookie;
use Pickupman\Petfinder\XMLParser;

class PetfinderTest extends \PHPUnit_Framework_TestCase {

	public $cookie;
	public $xmlparser;

	public function __construct() {
		$this->cookie    = new Cookie();
		$this->xmlparser = new XMLParser();
		$xml = json_decode(json_encode(array('header' => array('status' => array('code' => 200)), 'auth' => array('token' => md5('string'), 'expiresString' => strtotime("+30 minutes")))));
	}

	protected function tearDown() {
		\Mockery::close();
	}

	public function testPassingArgsToConstruct() {

		$petfinder = new Petfinder(array('api_key' => 'key', 'api_pass' => 'pass'));

		$this->assertEquals('key', $petfinder->api_key);
		$this->assertEquals('pass', $petfinder->api_pass);
	}

	public function testSetString() {
		$petfinder = new Petfinder();

		$this->assertFalse($petfinder->set('string'));
	}

	public function testSetArray() {
		$petfinder = new Petfinder();

		$this->assertTrue($petfinder->set(array('api_key' => 'api_key')));
	}

	public function testInitialize() {
		$petfinder = new Petfinder();
		$petfinder->initialize(array('format' => 'xml'));

		$this->assertEquals('xml', $petfinder->format);
	}

	public function testGetToken() {
		$cookie = \Mockery::mock('Pickupman\Petfinder\Cookie');
		$cookie->shouldReceive('get')
			->with('petToken')
			->once()
			->andReturn(true);

		$petfinder = new Petfinder();
		$this->assertEquals(true, $petfinder->getToken($cookie));
	}

	public function testBreedList() {
		$xml = new \SimpleXMLElement('<petfinder></petfinder>');
		$header = $xml->addChild('header');
		$status = $header->addChild('status');
		$code   = $status->addChild('code', 100);

		$breeds = $xml->addChild('breeds');
		$breeds->addChild('breed', 'German Shepherd');
		$breeds->addChild('breed', 'Golden Retriever');

		$xmlparser = \Mockery::mock('Pickupman\Petfinder\XMLParser');
		$xmlparser->shouldReceive('parse')
			->once()
			->andReturn(simplexml_load_string($xml->asXML()));

		$petfinder = new Petfinder(array(), $xmlparser);
		$data = $petfinder->breedList('dog');

		$this->assertEquals(true, is_array($data));
		$this->assertEquals(100, $data['code']);
	}

	public function testPetFind() {
		//$petfinder = new Petfinder();
	}

	public function testPetGet() {
		//$petfinder = new Petfinder();
	}

	public function testPetGetRandom() {
		//$petfinder = new Petfinder();
	}

	public function testShelterFind() {
		//$petfinder = new Petfinder();
	}

	public function testShelterGet() {
		//$petfinder = new Petfinder();
	}

	public function testShelterGetPets() {
		//$petfinder = new Petfinder();
	}

	public function testShelterListByBreed() {
		//$petfinder = new Petfinder();
	}

	public function testSetKey() {
		//$petfinder = new Petfinder();
	}

	public function testSetPass() {
		//$petfinder = new Petfinder();
	}

}