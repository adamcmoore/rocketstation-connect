<?php
namespace RocketStation;

use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Exception\BadResponseException;
use Psr\Http\Message\ResponseInterface;


class RocketStation
{

	protected $api_url;
	private $client;

	public function __construct($api_url = null)
	{

		$this->setApiUrl($api_url);

		$config = [
			'base_uri'   => $this->api_url,
			'exceptions' => false,
			'timeout'    => 30,
			'headers'    => [
				'User-Agent' => gethostname() .' ' . \GuzzleHttp\default_user_agent(),
			]
		];

		$this->client = new Client($config);
	}


	public function setApiUrl($url)
	{
		$this->api_url = $url;
	}


	public function getTimesForDropdown()
	{
		$twelve_hour_format = false;
		$times = [];

		for ($time = 0; $time < 24; $time += 0.25) {
			$hours   = intval($time);
			$minutes = 60 * ($time - $hours);
			$value = sprintf('%02d:%02d', $hours, $minutes);

			if ($twelve_hour_format) {
				if ($hours < 12) {
					$twelve_hours = $hours;
					$period = 'AM';
				} else {
					$twelve_hours = $hours - 12;	
					$period = 'PM';		
				}
				if ($twelve_hours === 0) {
					$twelve_hours = 12;
				}

				$label = sprintf('%1d:%02d %s', $twelve_hours, $minutes, $period);

			} else {
				$label = sprintf('%1d:%02d', $hours, $minutes);
			}

			if ($minutes == 0) {				
				if ($hours == 0) {
					$label = 'Midnight';
				}
				if ($hours == 12) {
					$label = 'Midday';
				}
			}

			$times[$value] = $label;
		}

			// Move midnight to end and change to a minute before to save confusion
		$midnight = array_shift($times);
		$times['23:59'] = 'Midnight';

		return $times;
	}


	public function getEventTypes()
	{
		$list = $this->_doRequest('jobs/event_types');        
		return array_get($list->body, 'data', false);
	}

	public function getLuggageOptions()
	{
		$list = $this->_doRequest('jobs/luggage_options');
		return array_get($list->body, 'data', false);
	}

	public function getReferrers()
	{
		$list = $this->_doRequest('jobs/referrers');
		return array_get($list->body, 'data', false);
	}

	public function getSourceTypes()
	{
		$list = $this->_doRequest('jobs/source_types');
		return $list->body;
	}

	public function getSalesTeams()
	{
		$list = $this->_doRequest('teams/sales_teams');
		return $list->body;
	}

	public function getAreas()
	{
		$list = $this->_doRequest('areas/areas');    
		return $list->body;
	}


	private function _doRequest($endpoint, $verb = 'GET', $headers = [], $body = '')
	{
		$request  = new Request($verb, $endpoint, $headers, $body);        
		$response = $this->client->send($request, []);

		if ($response instanceof ResponseInterface && $response->getStatusCode() === 200) {
			$response = $this->_parseResponse($response);
		} else {
			throw new BadResponseException(sprintf(
				'A bad response was received. HTTP Status code %s - %s', 
				$response->getStatusCode(),
				$response->getBody()->getContents()
			), $request);
		}

		return $response;
	}


	private function _parseResponse(ResponseInterface $response)
	{
		$body = $response->getBody()->getContents();
		$body = $this->_isValidJson($body) ? json_decode($body, true) : $body;

		return (object) [
			'code'      => $response->getStatusCode(),
			'headers'   => $response->getHeaders(),
			'body'      => $body,
		];
	}


	private function _isValidJson($string) 
	{
		if (is_string($string)) {
			json_decode($string);
			return (json_last_error() === JSON_ERROR_NONE);
		} else {
			return false;
		}
	}
}