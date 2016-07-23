<?php

namespace ApiExploder;

use Exception;

class ServiceManagerController
{
    /** @var  App */
    private $app;

    public function __construct(App $app)
    {
        $this->app = $app;
    }

    public function getResourceDefinitions()
    {
        $cache = $this->app['app.cache']->getItem('app.resources.definition');
        if (!$resources = $cache->get()) {
            foreach ($this->app['services'] as $service_name => $options) {
                if (empty($options['resource_definition']) || empty($options['service_address'])) {
                    throw new Exception(sprintf('Service %s setting is invalid.', $service_name));
                }
                $resources[$service_name] = json_decode($this->app['guzzle']->get($options['resource_definition'])->getBody()->getContents(), true);
                foreach ($resources[$service_name] as $resource_name => &$structure) {
                    if (empty($structure['dataStructure']['fields']) || empty($structure['endpoints'])) {
                        throw new Exception(sprintf('The resource %s definition is invalid.', $resource_name));
                    }
                    $structure['name'] = $resource_name;
                }
            }
            $this->app['app.cache']->save($cache->set($resources)->expiresAfter(60 * 60 * 24));
        }

        return !empty($resources) ? $resources : [];
    }

    public function getActiveResource()
    {
        $request = $this->app['request_stack']->getCurrentRequest();

        list($service_name, $resource_name, $active_endpoint) = explode('->', $request->get('_route'));

        return [
            'resource_name' => $resource_name,
            'service_name' => $service_name,
            'endpoint' => $active_endpoint,
            'parameters' => $request->attributes->get('_route_params')
        ];
    }

    public function getResource($service_name, $resource_name)
    {
        $resources = $this->getResourceDefinitions();

        if (empty($resources[$service_name][$resource_name])) {
            throw new Exception(sprintf('Not found resource %s on service %s', $resource_name, $service_name));
        }

        return $resources[$service_name][$resource_name];
    }

    public function getServiceEndpoint($service_name, $endpoint, $parameters)
    {
        $serviceAddress = trim($this->app['services'][$service_name]['service_address'], '/');

        if (!empty($parameters) && is_array($parameters)) {
            foreach ($parameters as $name => $parameter) {
                $args['{' . $name . '}'] = $parameter;
            }
        }

        return $serviceAddress . '/' . strtr($endpoint, isset($args) ? $args : []);
    }
}
