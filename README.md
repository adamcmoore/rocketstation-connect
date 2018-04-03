# Rocketstation Connect

A package to access data on the RocketStation API. 

More or less just a wrapper around GuzzleHTTP.


### Usage

```
$client = new RocketStationConnect($api_uri);

// Simple GET request
$guzzle_response = $client->get('jobs');

// The response body is decoded from JSON and attached to the $guzzle_response
dump($guzzle_response->data);

// Get a resource with a parameterized URL
$guzzle_response = $client->get('jobs/{id}', ['id' => 123]);

// POST, PUT & DELETE verbs also supported, passing the request body as an array
$guzzle_response = $client->put('jobs/{id}', ['id' => 123, 'is_reds' => true]);

// Error Handling
try {
	$guzzle_response = $client->get('non-existant-route');
} catch (ResponseException $response_exception) {
	$response_exception->getRequest();
	$response_exception->getResponse();
}
```


### Testing
A running RocketStation instance is required for testing. The URL of which needs to be set in the ROCKETSTATION_API_URI value of the .env file