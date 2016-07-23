<?php

namespace ApiExploder\Connector;

use ApiExploder\App;
use ApiExploder\ServiceManagerController;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;

class Controller
{
    /** @var  App */
    private $app;

    /** @var  ServiceManagerController */
    private $serviceManager;

    /** @var  Client */
    private $client;

    public function __construct(App $app)
    {
        $this->app = $app;
        $this->serviceManager = $app['service_manager.ctrl'];
        $this->client = $app['guzzle'];
    }

    public function forward(Request $request)
    {
        $activeResource = $this->serviceManager->getActiveResource();
        $serviceUrl = $this->serviceManager->getServiceEndpoint(
            $activeResource['service_name'],
            $activeResource['endpoint'],
            $activeResource['parameters']
        );
        $method = sprintf('do%s', ucfirst(strtolower($request->getMethod())));
        if (method_exists($this, $method)) {
            return $this->{$method}($serviceUrl, $request);
        }

        throw new MethodNotAllowedHttpException([], sprintf('Your request method %s is not allowed', $request->getMethod()));
    }

    protected function doGet($url, Request $request)
    {
        $headers = $request->headers->all();
        unset($headers['cookie'], $headers['host']);

        try {
            $response = $this->client->get($url, [
                'headers' => $headers,
                'query' => $request->query->all(),
                'allow_redirects' => false
            ]);
        } catch (RequestException $exception) {
            $response = $exception->getResponse();
        }

        return new Response($response->getBody()->getContents(), $response->getStatusCode());
    }

    protected function doPost($url, $request)
    {
    }
}
