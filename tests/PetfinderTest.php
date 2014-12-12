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
	}

	protected function tearDown() {
		\Mockery::close();
	}

	public function testPassingArgsToConstruct() {
		$xml = json_decode(json_encode(array('header' => array('status' => array('code' => 200)), 'auth' => array('token' => md5('string'), 'expiresString' => strtotime("+30 minutes")))));

		$xmlparser = \Mockery::mock('Pickupman\Petfinder\XMLParser');
		$xmlparser->shouldReceive('parse')
			->once()
			->andReturn($xml);

		$petfinder = new Petfinder(array('api_key' => 'key', 'api_pass' => 'pass'), $xmlparser, $this->cookie);

		$this->assertEquals('key', $petfinder->api_key);
		$this->assertEquals('pass', $petfinder->api_pass);
	}

	public function testSetString() {
		$petfinder = new Petfinder();

		$this->assertFalse($petfinder->set('string'));
	}

	public function testInitialize() {
		$petfinder = new Petfinder();
		$petfinder->initialize(array('format' => 'xml'));

		$this->assertEquals('xml', $petfinder->format);
	}

	public function testSetArray() {
		$petfinder = new Petfinder();

		$this->assertTrue($petfinder->set(array('api_key' => 'api_key')));
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

	public function testGetTokenWithNewCookie() {
		$cookie = \Mockery::mock('Pickupman\Petfinder\Cookie');
		//$cookie->shouldReceive()
	}

	public function testBreedList() {

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