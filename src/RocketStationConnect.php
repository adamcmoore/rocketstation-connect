<?php
namespace RocketStationConnect;

use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\RequestOptions;
use GuzzleHttp\Exception\BadResponseException;
use Psr\Http\Message\ResponseInterface;


class RocketStationConnect
{

	protected $api_uri;
	private $client;

	public function __construct($api_uri)
	{
		$this->api_uri = trim($api_uri, '/').'/';

		$config = [
			'base_uri'   => $this->api_uri,
			'exceptions' => false,
			'timeout'    => 30,
			'headers'    => [
				'User-Agent' => gethostname() .' ' . \GuzzleHttp\default_user_agent(),
			]
		];

		$this->client = new Client($config);
	}

	

	/**
	 * GETs the given endpoint
	 * 
	 * @param string A URI such as `jobs/event_types` or a tokenized URI like `jobs/{id}/logs`
	 * @param  string[] $data An optional array of request body paramenters, or params to be used in the tokenized endpoint URI
	 * 
	 * @return GuzzleHttp\Psr7\Response
	 */
	
	public function get($endpoint, $data = [], $query = [], $headers = [])
	{
		return $this->doRequest('GET', $endpoint, $data, $query, $headers);
	}


	/**
	 * POSTs to the given endpoint
	 * 
	 * @param string A URI such as `jobs/event_types` or a tokenized URI like `jobs/{id}/logs`
	 * @param  string[] $data An optional array of request body paramenters, or params to be used in the tokenized endpoint URI
	 * 
	 * @return GuzzleHttp\Psr7\Response
	 */
	public function post($endpoint, $data = [], $query = [], $headers = [])
	{
		return $this->doRequest('POST', $endpoint, $data, $query, $headers);
	}


	/**
	 * PUTs to the given endpoint
	 * 
	 * @param string A URI such as `jobs/event_types` or a tokenized URI like `jobs/{id}/logs`
	 * @param  string[] $data An optional array of request body paramenters, or params to be used in the tokenized endpoint URI
	 * 
	 * @return GuzzleHttp\Psr7\Response
	 */
	public function put($endpoint, $data = [], $query = [], $headers = [])
	{
		return $this->doRequest('PUT', $endpoint, $data, $query, $headers);
	}


	/**
	 * DELETEs to the given endpoint
	 * 
	 * @param string A URI such as `jobs/event_types` or a tokenized URI like `jobs/{id}/logs`
	 * @param  string[] $data An optional array of request body paramenters, or params to be used in the tokenized endpoint URI
	 * 
	 * @return GuzzleHttp\Psr7\Response
	 */
	public function delete($endpoint, $data = [], $query = [], $headers = [])
	{
		return $this->doRequest('DELETE', $endpoint, $data, $query, $headers);
	}



	private function doRequest($verb, $endpoint, $body, $query, $headers)
	{
		$endpoint = trim($endpoint, '/');
		$endpoint = $this->formatUriWithParameters($endpoint, $body);
		$request  = new Request($verb, $endpoint, $headers);
		$response = $this->client->send($request, [
			RequestOptions::JSON => $body,
			RequestOptions::QUERY => $query,
		]);

		try {
			$response = $this->parseResponse($response);
		} catch (\Exception $e) {}

		if (!$response instanceof ResponseInterface || $response->getStatusCode() !== 200) {
			throw new ResponseException(sprintf(
				'A bad response was received. HTTP Status code %s - %s', 
				$response->getStatusCode(),
				$response->getBody()->getContents()
			), $request, $response);
		}

		return $response;
	}


	private function parseResponse(ResponseInterface $response)
	{
		$body = $response->getBody()->getContents();
		$body = $this->isValidJson($body) ? json_decode($body) : $body;

		$response->body = $body;

		return $response;
	}


	private function isValidJson($string) 
	{
		if (is_string($string)) {
			json_decode($string);
			return (json_last_error() === JSON_ERROR_NONE);
		} else {
			return false;
		}
	}


	/**
	 * Takes a URi like /jobs/{id}/logs and returns /jobs/123/logs
	 * 
	 * @param string $uri A URi with {token}'s to replace
	 * @param string[] $params A dictionary of the data keys & values to use for replacing the tokens
	 * 
	 * @return string
	 */
	private function formatUriWithParameters($uri, $params)
	{
		foreach ($params as $key => $value) {
			$uri = str_replace('{'.$key.'}', $value, $uri);
		}

		return $uri;
	}


	/**
	 * Builds a list of human friendly times, rounded to every 15 minutes.
	 * Used by both websites
	 * 
	 * @return string[]
	 */
	public static function getTimesForDropdown()
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
}