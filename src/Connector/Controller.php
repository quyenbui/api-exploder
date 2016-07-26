<?php

namespace ApiExploder\Connector;

use ApiExploder\App;
use ApiExploder\ServiceManagerController;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use Symfony\Component\HttpFoundation\JsonResponse;
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
        $this->client = new Client([
            'cookies' => false,
            'allow_redirects' => false,
            'headers' => [
                'Request-From' => 'ApiExploder'
            ]
        ]);
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
            $response = $this->{$method}($serviceUrl, $request);

            return 'aa';
        }

        throw new MethodNotAllowedHttpException([], sprintf('Your request method %s is not allowed', $request->getMethod()));
    }

    public function ValidateRequest(Request $request)
    {
        // A Request must has Content-Type: application/vnd.api+json for POST|PATCH
        $errors = [];
        if (in_array($request->getMethod(), ['POST', 'PATCH'])) {
            if ($request->getContentType() !== 'application/vnd.api+json') {
                $errors['errors'][]['detail'] = 'Content must be application/vnd.api+json';
            }

            // Ensure json data always valid
            if (json_decode($request->getContent()) === null) {
                $errors['errors'][]['detail'] = 'Request body is not valid an json data';
            }
        }

        $acceptableTypes = $request->getAcceptableContentTypes();
        if (count($acceptableTypes) > 1 || reset($acceptableTypes) !== 'application/vnd.api+json') {
            $errors['errors'][]['detail'] = 'Accept header must be only application/vnd.api+json';
        }

        return !empty($errors) ? new JsonResponse($errors, 400) : null;
    }

    protected function doGet($url, Request $request)
    {
        try {
            $response = $this->client->get($url, [
                'query' => $request->query->all(),
                'allow_redirects' => false
            ]);
        } catch (RequestException $exception) {
            $response = $exception->getResponse();
        }

        return [
            'content' => $response->getBody()->getContents(),
            'status' => $response->getStatusCode()
        ];
    }

    protected function doPost($url, $request)
    {
        try {
            $response = $this->client->post($url, [
                'query' => $request->query->all(),
                'json' => json_decode($request->getContent(), true)
            ]);
        } catch (RequestException $exception) {
            $response = $exception->getResponse();
        }

        return [
            'content' => $response->getBody()->getContents(),
            'status' => $response->getStatusCode()
        ];
    }

    protected function doPatch($url, $request)
    {
        try {
            $response = $this->client->patch($url, [
                'query' => $request->query->all(),
                'json' => json_decode($request->getContent(), true)
            ]);
        } catch (RequestException $exception) {
            $response = $exception->getResponse();
        }

        return [
            'content' => $response->getBody()->getContents(),
            'status' => $response->getStatusCode()
        ];
    }

    protected function doDelete($url, $request)
    {
        try {
            $response = $this->client->delete($url, [
                'query' => $request->query->all()
            ]);
        } catch (RequestException $exception) {
            $response = $exception->getResponse();
        }

        return [
            'content' => $response->getBody()->getContents(),
            'status' => $response->getStatusCode()
        ];
    }

    protected function doOptions($url, $request)
    {
        // @TODO
        // Render endpoint document
        // Return method allowed
    }
}
