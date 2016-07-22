<?php

namespace ApiExploder\Forward;

use ApiExploder\App;
use Symfony\Component\HttpFoundation\Request;

class Controller
{
    /** @var  App */
    private $app;

    public function __construct(App $app)
    {
        $this->app = $app;
    }

    public function forward(Request $request)
    {
        return $this->app['service_manager.ctrl']->getActiveResource();
        list($serviceName) = explode('->', $request->get('_route'));
        $routeParams = $request->get('_route_params');
        /** @var \GuzzleHttp\Client $client */
        $client = $this->app['guzzle'];
    }
}
