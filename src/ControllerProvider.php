<?php

namespace ApiExploder;

use Silex\Api\ControllerProviderInterface;
use Silex\Application;
use Exception;

class ControllerProvider implements ControllerProviderInterface
{
    /**
     * {@inheritdoc}
     */
    public function connect(Application $app)
    {
        $services = $app['service_manager.ctrl']->getResourceDefinitions();
        $controllers = $app['controllers_factory'];

        foreach ($services as $serviceName => $service) {
            foreach ($service as $resourceName => $resource) {
                foreach (array_keys($resource['endpoints']) as $endpoint) {
                    list($path, $method) = explode(':', $endpoint);
                    $method = strtolower($method);
                    $methods = ['get', 'post', 'put', 'patch', 'options', 'delete'];
                    if (in_array($method, $methods)) {
                        $controllers->$method($resourceName . '/' . $path, 'forward.ctrl:forward')
                            ->bind(sprintf('%s->%s->%s', $serviceName, $resourceName, $path));
                    } else {
                        throw new Exception(sprintf('The method %s of endpoint %s is not supported.', $method, $path));
                    }
                }
            }
        }

        return $controllers;
    }
}
