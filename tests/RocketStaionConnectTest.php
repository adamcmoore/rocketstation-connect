<?php
namespace RocketStationConnect;

use PHPUnit\Framework\TestCase;
use Dotenv\Dotenv;

class RocketStaionConnectTest extends TestCase {

	private $client;


	protected function setUp()
	{
		$dotenv = new Dotenv(__DIR__.'/..');
		$dotenv->load();
		$uri = getenv('ROCKETSTATION_API_URI');

		$this->client = new RocketStationConnect($uri);
	}


	public function testPasswordGrantOAuth()
	{
	}
	

	public function testClientCredentialOAuth()
	{
	}


	public function testGet()
	{
		$response = $this->client->get('jobs/event_types');
		
		$this->assertObjectHasAttribute('body', $response);
		$this->assertObjectHasAttribute('data', $response->body);
		$this->assertInternalType('object', $response->body);
		$this->assertInternalType('array', $response->body->data);

		foreach ($response->body->data as $key => $value) {
			$this->assertInternalType('integer', $key);
			$this->assertInternalType('string', $value);
		}
	}
	

	public function testParameterizedUri()
	{
		$response = $this->client->get('jobs/{endpoint}', ['endpoint' => 'event_types']);
		
		$this->assertObjectHasAttribute('body', $response);
		$this->assertObjectHasAttribute('data', $response->body);
		$this->assertInternalType('object', $response->body);
		$this->assertInternalType('array', $response->body->data);
	}
	

	public function test404ErrorResponse()
	{	
		try {
			$response = $this->client->get('non-existant-route');			
		} catch (ResponseException $e) {
			$this->assertEquals(404, $e->getResponse()->getStatusCode());
		}
	}


	public function test401ErrorResponse()
	{	
		try {
			$response = $this->client->get('jobs');			
		} catch (ResponseException $e) {
			$this->assertEquals(401, $e->getResponse()->getStatusCode());
		}
	}


	public function testPost()
	{
	}
	

	public function testPut()
	{
	}
	

	public function testDelete()
	{
	}
}