<?php
require __DIR__.'/../vendor/autoload.php';

use Exbil\ResellingAPI\Client;
use Exbil\ResellingAPI\Exceptions\ApiException;
use GuzzleHttp\Client as HttpClient;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use GuzzleHttp\Psr7\Response;

$history = [];
$handler = HandlerStack::create(new MockHandler([
    new Response(200, [], '{"success":true,"data":null,"message":"Renamed."}'),
    new Response(409, [], '{"message":"A file or folder with that name already exists."}'),
]));
$handler->push(Middleware::history($history));
$client = new Client('test-only', 'https://example.test/api/', new HttpClient([
    'handler'=>$handler, 'base_uri'=>'https://example.test/api/', 'http_errors'=>false,
]));
$client->cloudServices()->files()->rename('test-uuid', '/public/Über uns.html', '/public/index.html');
$request = $history[0]['request'];
if ($request->getMethod() !== 'POST' || $request->getUri()->getPath() !== '/api/v1/products/cloudservices/test-uuid/files/rename'
    || json_decode((string)$request->getBody(),true) !== ['from'=>'/public/Über uns.html','to'=>'/public/index.html']) {
    throw new RuntimeException('Unexpected rename request');
}
try {
    $client->cloudServices()->files()->rename('test-uuid', '/public/a', '/public/b');
    throw new RuntimeException('Expected API conflict');
} catch (ApiException $e) {
    if ($e->getCode() !== 409) throw $e;
}
echo "Rename request and conflict handling passed.\n";
